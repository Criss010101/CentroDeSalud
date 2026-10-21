<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable(['staff_profile_id', 'patient_id', 'appointment_date', 'start_time', 'status', 'reason'])]
class Appointment extends Model
{
    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment): void {
            if (blank($appointment->booking_code)) {
                $appointment->booking_code = 'SJ-'.Str::upper(Str::random(12));
            }
        });
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function nursingIntake(): HasOne
    {
        return $this->hasOne(NursingIntake::class);
    }

    public function medicalConsultation(): HasOne
    {
        return $this->hasOne(MedicalConsultation::class);
    }

    public function careEncounter(): HasOne
    {
        return $this->hasOne(CareEncounter::class);
    }
}
