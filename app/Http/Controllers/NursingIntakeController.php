<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\NursingIntake;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NursingIntakeController extends Controller
{
    public function index(): View
    {
        $appointments = Appointment::query()
            ->whereDate('appointment_date', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['patient.patientProfile', 'doctor.user', 'nursingIntake'])
            ->orderBy('start_time')
            ->get();

        return view('nursing.intakes.index', compact('appointments'));
    }

    public function edit(Appointment $appointment): View
    {
        abort_unless(
            $appointment->appointment_date->isSameDay(today())
            && in_array($appointment->status, ['pending', 'confirmed'], true),
            404,
        );

        $appointment->load([
            'patient.patientProfile',
            'doctor.user',
            'nursingIntake.nurse',
            'nursingIntake.amendments.author',
        ]);

        $latestAmendment = $appointment->nursingIntake?->amendments->last();
        $measurements = $latestAmendment?->data ?? $appointment->nursingIntake?->measurementData() ?? [];

        return view('nursing.intakes.edit', compact('appointment', 'latestAmendment', 'measurements'));
    }

    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $measurements = $this->validatedMeasurements($request);

        DB::transaction(function () use ($appointment, $measurements, $request): void {
            $lockedAppointment = Appointment::query()
                ->whereKey($appointment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                ! $lockedAppointment->appointment_date->isSameDay(today())
                || ! in_array($lockedAppointment->status, ['pending', 'confirmed'], true)
            ) {
                throw ValidationException::withMessages([
                    'appointment' => 'Solo se pueden registrar datos iniciales para citas activas del día.',
                ]);
            }

            if ($lockedAppointment->nursingIntake()->exists()) {
                throw ValidationException::withMessages([
                    'appointment' => 'Esta cita ya cuenta con un registro inicial. Agrega una rectificación si necesitas corregirlo.',
                ]);
            }

            $lockedAppointment->nursingIntake()->create([
                ...$measurements,
                'patient_id' => $lockedAppointment->patient_id,
                'nurse_user_id' => $request->user()->id,
                'measured_at' => now(),
            ]);
        });

        return redirect()->route('nursing.intakes.index')->with('status', 'Los datos iniciales quedaron disponibles para el médico responsable.');
    }

    public function amend(Request $request, NursingIntake $nursingIntake): RedirectResponse
    {
        $data = $request->validate([
            ...$this->measurementRules(),
            'correction_reason' => ['required', 'string', 'max:500'],
        ]);
        $measurements = $this->onlyMeasurements($data);
        $this->validateBloodPressurePair($measurements);

        DB::transaction(function () use ($nursingIntake, $measurements, $data, $request): void {
            $lockedIntake = NursingIntake::query()
                ->whereKey($nursingIntake->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedIntake->amendments()->create([
                'author_user_id' => $request->user()->id,
                'reason' => $data['correction_reason'],
                'data' => $measurements,
            ]);
        });

        return redirect()
            ->route('nursing.intakes.edit', $nursingIntake->appointment_id)
            ->with('status', 'La rectificación quedó registrada y conserva el historial anterior.');
    }

    public function doctorShow(Appointment $appointment): View
    {
        abort_unless(
            (int) $appointment->doctor()->value('user_id') === (int) auth()->id(),
            404,
        );

        $nursingIntake = $appointment->nursingIntake()
            ->with(['nurse', 'amendments.author'])
            ->firstOrFail();
        $latestAmendment = $nursingIntake->amendments->last();
        $measurements = $latestAmendment?->data ?? $nursingIntake->measurementData();

        return view('nursing.intakes.doctor-show', compact(
            'appointment',
            'nursingIntake',
            'latestAmendment',
            'measurements',
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedMeasurements(Request $request): array
    {
        $measurements = $this->onlyMeasurements($request->validate($this->measurementRules()));
        $this->validateBloodPressurePair($measurements);

        return $measurements;
    }

    /**
     * @return array<string, mixed>
     */
    private function measurementRules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
            'blood_pressure_systolic' => ['nullable', 'integer', 'between:1,300'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'between:1,300'],
            'heart_rate' => ['nullable', 'integer', 'between:1,300'],
            'temperature_celsius' => ['nullable', 'numeric', 'between:20,50'],
            'respiratory_rate' => ['nullable', 'integer', 'between:1,100'],
            'oxygen_saturation' => ['nullable', 'integer', 'between:0,100'],
            'weight_kg' => ['nullable', 'numeric', 'between:0.1,500'],
            'height_cm' => ['nullable', 'numeric', 'between:20,300'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyMeasurements(array $data): array
    {
        return [
            'reason' => $data['reason'],
            'blood_pressure_systolic' => $data['blood_pressure_systolic'] ?? null,
            'blood_pressure_diastolic' => $data['blood_pressure_diastolic'] ?? null,
            'heart_rate' => $data['heart_rate'] ?? null,
            'temperature_celsius' => $data['temperature_celsius'] ?? null,
            'respiratory_rate' => $data['respiratory_rate'] ?? null,
            'oxygen_saturation' => $data['oxygen_saturation'] ?? null,
            'weight_kg' => $data['weight_kg'] ?? null,
            'height_cm' => $data['height_cm'] ?? null,
            'observations' => $data['observations'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $measurements
     */
    private function validateBloodPressurePair(array $measurements): void
    {
        $hasSystolic = filled($measurements['blood_pressure_systolic']);
        $hasDiastolic = filled($measurements['blood_pressure_diastolic']);

        if ($hasSystolic !== $hasDiastolic) {
            throw ValidationException::withMessages([
                $hasSystolic ? 'blood_pressure_diastolic' : 'blood_pressure_systolic' => 'Registra ambos valores de presión arterial o deja ambos en blanco.',
            ]);
        }
    }
}
