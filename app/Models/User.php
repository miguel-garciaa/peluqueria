<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\SpanishPhone;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['google_id', 'name', 'email', 'phone', 'email_verified_at', 'avatar_url', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => SpanishPhone::format($value),
            set: fn (?string $value): ?string => SpanishPhone::format($value),
        );
    }

    public function isPanelAdmin(): bool
    {
        $configuredAdminEmail = Str::lower(trim((string) config('admin.email')));
        $matchesConfiguredEmail = $configuredAdminEmail !== ''
            && Str::lower(trim((string) $this->email)) === $configuredAdminEmail;

        return $this->is_admin || $matchesConfiguredEmail;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->isPanelAdmin();
    }

    public function scopeCustomers(Builder $query): Builder
    {
        $configuredAdminEmail = Str::lower(trim((string) config('admin.email')));

        return $query
            ->where('is_admin', false)
            ->when(
                $configuredAdminEmail !== '',
                fn (Builder $query): Builder => $query->whereRaw('LOWER(email) <> ?', [$configuredAdminEmail]),
            );
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
