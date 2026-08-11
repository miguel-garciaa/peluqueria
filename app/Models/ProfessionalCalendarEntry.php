<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['professional_id', 'date', 'type', 'all_day', 'starts_at', 'ends_at', 'slot_interval_minutes', 'reason'])]
class ProfessionalCalendarEntry extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'immutable_date',
            'all_day' => 'boolean',
            'slot_interval_minutes' => 'integer',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }
}
