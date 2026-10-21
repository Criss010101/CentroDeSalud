<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CareEncounter;
use App\Models\CareEncounterEvent;
use App\Models\PatientProfile;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReceptionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $appointments = Appointment::query()
            ->whereDate('appointment_date', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['patient.patientProfile', 'doctor.user', 'careEncounter'])
            ->orderBy('start_time')
            ->get();

        $appointmentMatches = null;
        $patientMatches = collect();

        if (filled($filters['q'] ?? null)) {
            $term = trim($filters['q']);
            $appointmentMatches = Appointment::query()
                ->where(function ($query) use ($term): void {
                    $query->where('booking_code', $term)
                        ->orWhereHas('patient.patientProfile', fn ($profile) => $profile->where('national_id', $term))
                        ->orWhereHas('patient', fn ($patient) => $patient->where('name', 'like', '%'.$term.'%'));
                })
                ->with(['patient.patientProfile', 'doctor.user', 'careEncounter'])
                ->orderByDesc('appointment_date')
                ->limit(20)
                ->get();

            $patientMatches = PatientProfile::query()
                ->with('user')
                ->where(function ($query) use ($term): void {
                    $query->where('national_id', $term)
                        ->orWhere('name', 'like', '%'.$term.'%')
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$term.'%'));
                })
                ->orderBy('name')
                ->limit(20)
                ->get();
        }

        $doctors = User::query()
            ->whereHas('role', fn ($query) => $query->where('code', 'doctor'))
            ->whereHas('staffProfile')
            ->with(['staffProfile.specialties'])
            ->orderBy('name')
            ->get();

        return view('reception.index', compact('appointments', 'appointmentMatches', 'patientMatches', 'filters', 'doctors'));
    }

    public function checkIn(Request $request, Appointment $appointment): RedirectResponse
    {
        DB::transaction(function () use ($request, $appointment): void {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->with(['patient.patientProfile', 'doctor.user'])
                ->firstOrFail();

            if (
                ! $lockedAppointment->appointment_date->isSameDay(today())
                || ! in_array($lockedAppointment->status, ['pending', 'confirmed'], true)
            ) {
                throw ValidationException::withMessages([
                    'appointment' => 'Solo se pueden admitir reservas activas del día.',
                ]);
            }

            $profile = $lockedAppointment->patient?->patientProfile;

            if ($profile === null) {
                throw ValidationException::withMessages([
                    'appointment' => 'La reserva no tiene una ficha de paciente vinculada; solicita apoyo administrativo.',
                ]);
            }

            if (CareEncounter::query()->where('appointment_id', $lockedAppointment->id)->exists()) {
                throw ValidationException::withMessages([
                    'appointment' => 'La llegada de esta reserva ya fue registrada.',
                ]);
            }

            $encounter = CareEncounter::query()->create([
                'patient_profile_id' => $profile->id,
                'appointment_id' => $lockedAppointment->id,
                'doctor_user_id' => $lockedAppointment->doctor->user_id,
                'received_by_user_id' => $request->user()->id,
                'source' => 'appointment',
                'status' => 'waiting',
                'received_at' => now(),
            ]);

            $encounter->events()->create([
                'appointment_id' => $lockedAppointment->id,
                'author_user_id' => $request->user()->id,
                'to_status' => 'waiting',
                'note' => 'Llegada registrada en recepción.',
            ]);
        });

        return redirect()->route('reception.index')->with('status', 'La llegada quedó registrada. La consulta médica aún no se ha realizado.');
    }

    public function markNoShow(Request $request, Appointment $appointment): RedirectResponse
    {
        DB::transaction(function () use ($request, $appointment): void {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                ! $lockedAppointment->appointment_date->isSameDay(today())
                || ! in_array($lockedAppointment->status, ['pending', 'confirmed'], true)
                || Carbon::parse($lockedAppointment->appointment_date->toDateString().' '.$lockedAppointment->start_time)->isFuture()
                || CareEncounter::query()->where('appointment_id', $lockedAppointment->id)->exists()
            ) {
                throw ValidationException::withMessages([
                    'appointment' => 'Esta reserva no puede marcarse como inasistencia.',
                ]);
            }

            $previousStatus = $lockedAppointment->status;
            $lockedAppointment->update(['status' => 'no_show']);
            CareEncounterEvent::query()->create([
                'appointment_id' => $lockedAppointment->id,
                'author_user_id' => $request->user()->id,
                'from_status' => $previousStatus,
                'to_status' => 'no_show',
            ]);
        });

        return redirect()->route('reception.index')->with('status', 'La inasistencia quedó registrada.');
    }

    public function storeWalkIn(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_profile_id' => ['nullable', 'integer', 'exists:patient_profiles,id'],
            'name' => ['required_without:patient_profile_id', 'string', 'max:255'],
            'national_id' => ['required_without:patient_profile_id', 'string', 'max:30', Rule::unique('patient_profiles', 'national_id')],
            'birth_date' => ['required_without:patient_profile_id', 'date', 'before_or_equal:today'],
            'sex' => ['required_without:patient_profile_id', 'in:F,M,O'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'doctor_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn(
                    'role_id',
                    Role::query()->where('code', 'doctor')->select('id'),
                )),
            ],
        ]);

        $doctor = User::query()
            ->whereKey($data['doctor_user_id'])
            ->whereHas('role', fn ($query) => $query->where('code', 'doctor'))
            ->whereHas('staffProfile')
            ->firstOrFail();

        DB::transaction(function () use ($request, $data, $doctor): void {
            $profile = isset($data['patient_profile_id'])
                ? PatientProfile::query()->whereKey($data['patient_profile_id'])->lockForUpdate()->firstOrFail()
                : PatientProfile::query()->create([
                    'name' => $data['name'],
                    'national_id' => $data['national_id'],
                    'birth_date' => $data['birth_date'],
                    'sex' => $data['sex'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                ]);

            $encounter = CareEncounter::query()->create([
                'patient_profile_id' => $profile->id,
                'doctor_user_id' => $doctor->id,
                'received_by_user_id' => $request->user()->id,
                'source' => 'walk_in',
                'status' => 'waiting',
                'received_at' => now(),
            ]);

            $encounter->events()->create([
                'author_user_id' => $request->user()->id,
                'to_status' => 'waiting',
                'note' => 'Atención presencial sin reserva registrada.',
            ]);
        });

        return redirect()->route('reception.index')->with('status', 'La atención presencial quedó registrada sin crear una cita ficticia.');
    }
}
