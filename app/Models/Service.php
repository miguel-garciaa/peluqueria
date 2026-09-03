<?php

namespace App\Models;

use App\Models\Concerns\DeletesPublicImage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name', 'description', 'image_path', 'duration_minutes', 'price_from', 'is_custom', 'is_active'])]
class Service extends Model
{
    use DeletesPublicImage;

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'price_from' => 'decimal:2',
            'is_custom' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function professionals(): BelongsToMany
    {
        return $this->belongsToMany(Professional::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
