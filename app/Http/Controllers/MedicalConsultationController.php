<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CareEncounter;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MedicalConsultationController extends Controller
{
    public function create(Request $request, Appointment $appointment): View
    {
        $this->ensureDoctorOwnsAppointment($request, $appointment);
        abort_unless($this->appointmentCanBeAttended($appointment), 404);
        abort_if($appointment->medicalConsultation()->exists(), 404);

        $appointment->load(['patient', 'nursingIntake.amendments', 'careEncounter.patientProfile']);
        $latestAmendment = $appointment->nursingIntake?->amendments->last();
        $suggestedReason = $latestAmendment?->data['reason']
            ?? $appointment->nursingIntake?->reason
            ?? $appointment->reason;

        $encounter = $appointment->careEncounter;

        return view('medical-records.consultations.create', compact('appointment', 'encounter', 'suggestedReason'));
    }

    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:5000'],
            'observations' => ['required', 'string', 'max:10000'],
            'diagnosis' => ['required', 'string', 'max:5000'],
            'treatment' => ['required', 'string', 'max:10000'],
            'recommendations' => ['required', 'string', 'max:10000'],
        ]);

        DB::transaction(function () use ($request, $appointment, $data): void {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureDoctorOwnsAppointment($request, $lockedAppointment);

            if ($lockedAppointment->medicalConsultation()->exists()) {
                throw ValidationException::withMessages([
                    'appointment' => 'Esta cita ya tiene una consulta médica registrada.',
                ]);
            }

            if (! $this->appointmentCanBeAttended($lockedAppointment)) {
                throw ValidationException::withMessages([
                    'appointment' => 'Solo se pueden registrar consultas para citas confirmadas que ya iniciaron.',
                ]);
            }

            $encounter = CareEncounter::query()
                ->where('appointment_id', $lockedAppointment->id)
                ->lockForUpdate()
                ->firstOrFail();
            if (! in_array($encounter->status, ['waiting', 'pending_consultation'], true)) {
                throw ValidationException::withMessages([
                    'appointment' => 'La atención no está lista para registrar una consulta médica.',
                ]);
            }

            $lockedAppointment->medicalConsultation()->create([
                ...$data,
                'patient_id' => $lockedAppointment->patient_id,
                'patient_profile_id' => $encounter->patient_profile_id,
                'care_encounter_id' => $encounter->id,
                'doctor_user_id' => $request->user()->id,
            ]);
            $lockedAppointment->update(['status' => 'completed']);
            if ($encounter->status === 'waiting' || $encounter->status === 'pending_consultation') {
                $encounter->transitionTo('in_consultation', $request->user());
            }
            $encounter->transitionTo('attended', $request->user());
        });

        return redirect()
            ->route('medical-records.history', $appointment->patient_id)
            ->with('status', 'La consulta médica quedó registrada en el historial clínico.');
    }

    public function encounters(Request $request): View
    {
        $doctor = $request->user();
        $encounters = CareEncounter::query()
            ->where('doctor_user_id', $doctor->id)
            ->where('source', 'walk_in')
            ->whereDate('received_at', today())
            ->whereIn('status', ['waiting', 'pending_consultation'])
            ->with(['patientProfile', 'nursingIntake', 'medicalConsultation'])
            ->orderBy('received_at')
            ->get();

        return view('medical-records.encounters.index', compact('encounters'));
    }

    public function createEncounter(Request $request, CareEncounter $encounter): View
    {
        abort_unless((int) $encounter->doctor_user_id === (int) $request->user()->id, 404);
        abort_unless($encounter->source === 'walk_in', 404);
        abort_unless(in_array($encounter->status, ['waiting', 'pending_consultation'], true), 404);
        abort_if($encounter->medicalConsultation()->exists(), 404);

        $encounter->load(['patientProfile', 'nursingIntake.amendments']);
        $latestAmendment = $encounter->nursingIntake?->amendments->last();
        $suggestedReason = $latestAmendment?->data['reason'] ?? $encounter->nursingIntake?->reason;
        $appointment = null;

        return view('medical-records.consultations.create', compact('appointment', 'encounter', 'suggestedReason'));
    }

    public function storeEncounter(Request $request, CareEncounter $encounter): RedirectResponse
    {
        $data = $request->validate($this->consultationRules());

        DB::transaction(function () use ($request, $encounter, $data): void {
            $lockedEncounter = CareEncounter::query()
                ->whereKey($encounter->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                (int) $lockedEncounter->doctor_user_id === (int) $request->user()->id
                && $lockedEncounter->source === 'walk_in',
                404,
            );

            if (
                ! in_array($lockedEncounter->status, ['waiting', 'pending_consultation'], true)
                || $lockedEncounter->medicalConsultation()->exists()
            ) {
                throw ValidationException::withMessages([
                    'encounter' => 'Esta atención ya avanzó o cuenta con una consulta médica.',
                ]);
            }

            $lockedEncounter->medicalConsultation()->create([
                ...$data,
                'appointment_id' => null,
                'patient_id' => null,
                'patient_profile_id' => $lockedEncounter->patient_profile_id,
                'doctor_user_id' => $request->user()->id,
            ]);
            $lockedEncounter->transitionTo('in_consultation', $request->user());
            $lockedEncounter->transitionTo('attended', $request->user());
        });

        return redirect()
            ->route('medical-records.encounters.history', $encounter)
            ->with('status', 'La consulta médica quedó registrada en la atención presencial.');
    }

    /**
     * @return array<string, mixed>
     */
    private function consultationRules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:5000'],
            'observations' => ['required', 'string', 'max:10000'],
            'diagnosis' => ['required', 'string', 'max:5000'],
            'treatment' => ['required', 'string', 'max:10000'],
            'recommendations' => ['required', 'string', 'max:10000'],
        ];
    }

    private function ensureDoctorOwnsAppointment(Request $request, Appointment $appointment): void
    {
        abort_unless(
            (int) $appointment->doctor()->value('user_id') === (int) $request->user()->id,
            404,
        );
    }

    private function appointmentCanBeAttended(Appointment $appointment): bool
    {
        return $appointment->status === 'confirmed'
            && $appointment->careEncounter()->whereIn('status', ['waiting', 'pending_consultation'])->exists()
            && Carbon::parse($appointment->appointment_date->toDateString().' '.$appointment->start_time)->isPast();
    }
}
