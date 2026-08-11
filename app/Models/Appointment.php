<?php

namespace App\Models;

use App\Support\SpanishPhone;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['reference', 'user_id', 'service_id', 'professional_id', 'customer_name', 'customer_phone', 'custom_details', 'starts_at', 'ends_at', 'status', 'cancelled_at'])]
class Appointment extends Model
{
    protected $table = 'bookings';

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment): void {
            $appointment->reference ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }

    protected function customerPhone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => SpanishPhone::format($value),
            set: fn (?string $value): ?string => SpanishPhone::format($value),
        );
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true)
            && $this->starts_at->isFuture();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }
}
