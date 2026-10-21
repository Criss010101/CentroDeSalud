<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nursing_intake_id', 'author_user_id', 'reason', 'data'])]
class NursingIntakeAmendment extends Model
{
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function nursingIntake(): BelongsTo
    {
        return $this->belongsTo(NursingIntake::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
