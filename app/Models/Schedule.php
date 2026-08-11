<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['professional_id', 'day_of_week', 'starts_at', 'ends_at', 'slot_interval_minutes', 'is_active'])]
class Schedule extends Model
{
    protected $table = 'professional_schedules';

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_interval_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }
}
