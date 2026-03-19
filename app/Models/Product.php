<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'description',
        'image_path',
        'price',
        'price_small',
        'price_medium',
        'price_large',
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
            'discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
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

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
