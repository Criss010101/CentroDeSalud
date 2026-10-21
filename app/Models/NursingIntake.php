<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'appointment_id',
    'care_encounter_id',
    'patient_id',
    'patient_profile_id',
    'nurse_user_id',
    'reason',
    'blood_pressure_systolic',
    'blood_pressure_diastolic',
    'heart_rate',
    'temperature_celsius',
    'respiratory_rate',
    'oxygen_saturation',
    'weight_kg',
    'height_cm',
    'observations',
    'measured_at',
])]
class NursingIntake extends Model
{
    protected function casts(): array
    {
        return [
            'temperature_celsius' => 'decimal:1',
            'weight_kg' => 'decimal:2',
            'height_cm' => 'decimal:2',
            'measured_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function careEncounter(): BelongsTo
    {
        return $this->belongsTo(CareEncounter::class);
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nurse_user_id');
    }

    public function amendments(): HasMany
    {
        return $this->hasMany(NursingIntakeAmendment::class);
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function measurementData(): array
    {
        return [
            'reason' => $this->reason,
            'blood_pressure_systolic' => $this->blood_pressure_systolic,
            'blood_pressure_diastolic' => $this->blood_pressure_diastolic,
            'heart_rate' => $this->heart_rate,
            'temperature_celsius' => $this->temperature_celsius,
            'respiratory_rate' => $this->respiratory_rate,
            'oxygen_saturation' => $this->oxygen_saturation,
            'weight_kg' => $this->weight_kg,
            'height_cm' => $this->height_cm,
            'observations' => $this->observations,
        ];
    }
}
