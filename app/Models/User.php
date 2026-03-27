<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'avatar_path',
        'role_id',
        'created_by',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    public function createdUsers(): HasMany
    {
        return $this->hasMany(self::class, 'created_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }

    public function cashierAttendances(): HasMany
    {
        return $this->hasMany(CashierAttendance::class, 'cashier_id');
    }

    public function hasRole(string ...$roles): bool
    {
        if (! $this->relationLoaded('role')) {
            $this->load('role');
        }

        $userRole = $this->role?->slug;

        return $userRole !== null && in_array($userRole, $roles, true);
    }

    public function normalizedAvatarPath(): ?string
    {
        $path = trim((string) ($this->avatar_path ?? ''));

        if ($path === '') {
            return null;
        }

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        if (Str::startsWith($path, 'public/')) {
            $path = Str::after($path, 'public/');
        }

        if (! Str::contains($path, '/')) {
            $path = 'avatars/' . $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        $fallbackPath = 'avatars/' . basename($path);

        if (Storage::disk('public')->exists($fallbackPath)) {
            return $fallbackPath;
        }

        return null;
    }

    public function avatarUrl(): ?string
    {
        $path = trim((string) ($this->avatar_path ?? ''));

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        $normalizedPath = $this->normalizedAvatarPath();

        return $normalizedPath ? asset('storage/' . $normalizedPath) : null;
    }
}
