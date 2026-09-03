<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'method',
    'status',
    'amount',
    'currency',
    'paid_at',
    'gateway_provider',
    'gateway_reference',
    'metadata',
])]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    public static function methodOptions(): array
    {
        return [
            'cash' => 'Efectivo en el salón',
            'bizum' => 'Bizum',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'pending' => 'Pendiente',
            'paid' => 'Pagado',
            'failed' => 'Fallido',
            'refunded' => 'Reembolsado',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'booking_id');
    }
}
