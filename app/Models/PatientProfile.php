<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'name', 'national_id', 'birth_date', 'sex', 'phone', 'address'])]
class PatientProfile extends Model
{
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->user?->name ?: 'Paciente sin nombre';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function careEncounters(): HasMany
    {
        return $this->hasMany(CareEncounter::class);
    }

    public function nursingIntakes(): HasMany
    {
        return $this->hasMany(NursingIntake::class);
    }

    public function medicalConsultations(): HasMany
    {
        return $this->hasMany(MedicalConsultation::class);
    }
}
