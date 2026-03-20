<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    public const TYPE_MONEY_IN = 'money_in';
    public const TYPE_MONEY_OUT = 'money_out';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'amount',
        'note',
        'happened_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'happened_at' => 'datetime',
        ];
    }

    public function scopeMoneyIn(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MONEY_IN);
    }

    public function scopeMoneyOut(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MONEY_OUT);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
