<?php

namespace App\Http\Controllers;

use App\Models\CareEncounter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientHistoryController extends Controller
{
    public function show(Request $request, User $patient): View
    {
        abort_unless($patient->hasRole('patient'), 404);

        $doctor = $request->user()->staffProfile;
        abort_unless(
            $doctor !== null
            && $doctor->appointments()
                ->where('patient_id', $patient->id)
                ->where('status', '!=', 'cancelled')
                ->exists(),
            404,
        );

        $patient->load('patientProfile');
        $appointments = $patient->appointments()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query): void {
                $query->whereHas('medicalConsultation')
                    ->orWhereHas('nursingIntake');
            })
            ->with([
                'doctor.user',
                'medicalConsultation.doctor',
                'nursingIntake.nurse',
                'nursingIntake.amendments' => fn ($query) => $query->orderBy('id'),
            ])
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->paginate(15);
        $careEncounters = $patient->patientProfile?->careEncounters()
            ->with(['doctor', 'nursingIntake.nurse', 'medicalConsultation.doctor'])
            ->orderByDesc('received_at')
            ->paginate(15) ?? collect();

        return view('medical-records.history', compact('patient', 'appointments', 'careEncounters'));
    }

    public function showEncounter(Request $request, CareEncounter $encounter): View
    {
        abort_unless((int) $encounter->doctor_user_id === (int) $request->user()->id, 404);

        $patientProfile = $encounter->patientProfile;
        $careEncounters = $patientProfile->careEncounters()
            ->with(['doctor', 'nursingIntake.nurse', 'medicalConsultation.doctor'])
            ->orderByDesc('received_at')
            ->paginate(15);

        return view('medical-records.encounter-history', compact('patientProfile', 'careEncounters', 'encounter'));
    }
}
