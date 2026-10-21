<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'patient_profile_id',
    'appointment_id',
    'doctor_user_id',
    'received_by_user_id',
    'source',
    'status',
    'received_at',
])]
class CareEncounter extends Model
{
    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    public function patientProfile(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_user_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function nursingIntake(): HasOne
    {
        return $this->hasOne(NursingIntake::class);
    }

    public function medicalConsultation(): HasOne
    {
        return $this->hasOne(MedicalConsultation::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CareEncounterEvent::class);
    }

    public function transitionTo(string $status, User $author, ?string $note = null): void
    {
        $allowedTransitions = [
            'waiting' => ['in_nursing', 'in_consultation', 'no_show'],
            'in_nursing' => ['pending_consultation'],
            'pending_consultation' => ['in_consultation', 'attended'],
            'in_consultation' => ['attended'],
        ];

        if (! in_array($status, $allowedTransitions[$this->status] ?? [], true)) {
            throw new \DomainException("La transición de atención {$this->status} a {$status} no está permitida.");
        }

        $previousStatus = $this->status;
        $this->update(['status' => $status]);
        $this->events()->create([
            'appointment_id' => $this->appointment_id,
            'author_user_id' => $author->id,
            'from_status' => $previousStatus,
            'to_status' => $status,
            'note' => $note,
        ]);
    }
}
