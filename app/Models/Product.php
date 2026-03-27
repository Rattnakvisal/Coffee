<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'created_by',
        'description',
        'image_path',
        'price',
        'price_small',
        'price_medium',
        'price_large',
        'is_small_active',
        'is_medium_active',
        'is_large_active',
        'discount_percent',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_small' => 'decimal:2',
            'price_medium' => 'decimal:2',
            'price_large' => 'decimal:2',
            'is_small_active' => 'boolean',
            'is_medium_active' => 'boolean',
            'is_large_active' => 'boolean',
            'discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function isSizeActive(string $size): bool
    {
        return match (strtolower($size)) {
            'medium' => (bool) ($this->is_medium_active ?? true),
            'large' => (bool) ($this->is_large_active ?? true),
            default => (bool) ($this->is_small_active ?? true),
        };
    }

    public function defaultActiveSize(): string
    {
        if ($this->isSizeActive('small')) {
            return 'small';
        }

        if ($this->isSizeActive('medium')) {
            return 'medium';
        }

        return 'large';
    }

    public function sizeBasePrice(string $size): float
    {
        return match (strtolower($size)) {
            'medium' => (float) ($this->price_medium ?? $this->price ?? 0),
            'large' => (float) ($this->price_large ?? $this->price ?? 0),
            default => (float) ($this->price_small ?? $this->price ?? 0),
        };
    }

    public function normalizedDiscountPercent(): float
    {
        return max(0.0, min(100.0, (float) ($this->discount_percent ?? 0)));
    }

    public function normalizedImagePath(): ?string
    {
        $path = trim((string) ($this->image_path ?? ''));

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
            $path = 'products/' . $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        $fallbackPath = 'products/' . basename($path);

        if (Storage::disk('public')->exists($fallbackPath)) {
            return $fallbackPath;
        }

        return null;
    }

    public function imageUrl(): ?string
    {
        $path = trim((string) ($this->image_path ?? ''));

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        $normalizedPath = $this->normalizedImagePath();

        return $normalizedPath ? asset('storage/' . $normalizedPath) : null;
    }

    public function applyDiscount(float $price): float
    {
        $discountFactor = 1 - ($this->normalizedDiscountPercent() / 100);

        return max(round($price * $discountFactor, 2), 0.0);
    }

    public function sizePrice(string $size): float
    {
        return $this->applyDiscount($this->sizeBasePrice($size));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
