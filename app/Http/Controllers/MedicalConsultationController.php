<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
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

        $appointment->load(['patient', 'nursingIntake.amendments']);
        $latestAmendment = $appointment->nursingIntake?->amendments->last();
        $suggestedReason = $latestAmendment?->data['reason']
            ?? $appointment->nursingIntake?->reason
            ?? $appointment->reason;

        return view('medical-records.consultations.create', compact('appointment', 'suggestedReason'));
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

            $lockedAppointment->medicalConsultation()->create([
                ...$data,
                'patient_id' => $lockedAppointment->patient_id,
                'doctor_user_id' => $request->user()->id,
            ]);
            $lockedAppointment->update(['status' => 'completed']);
        });

        return redirect()
            ->route('medical-records.history', $appointment->patient_id)
            ->with('status', 'La consulta médica quedó registrada en el historial clínico.');
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
            && Carbon::parse($appointment->appointment_date->toDateString().' '.$appointment->start_time)->isPast();
    }
}
