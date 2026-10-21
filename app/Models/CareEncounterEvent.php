<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'care_encounter_id',
    'appointment_id',
    'author_user_id',
    'from_status',
    'to_status',
    'note',
])]
class CareEncounterEvent extends Model
{
    public function careEncounter(): BelongsTo
    {
        return $this->belongsTo(CareEncounter::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
