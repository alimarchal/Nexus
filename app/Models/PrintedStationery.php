<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintedStationery extends Model
{
    /** @use HasFactory<\Database\Factories\PrintedStationeryFactory> */
    use HasFactory, UserTracking;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_code',
        'name',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'current_stock',
        'latest_purchase_price',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'item_code';
    }

    /**
     * Get all transactions for this stationery item.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(StationeryTransaction::class);
    }

    /**
     * Get the current stock balance for this stationery item.
     */
    public function getCurrentStockAttribute(): int
    {
        $lastTransaction = $this->transactions()
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastTransaction ? $lastTransaction->balance_after_transaction : 0;
    }

    /**
     * Get the latest purchase price for this stationery item.
     */
    public function getLatestPurchasePriceAttribute(): ?float
    {
        $latestPurchase = $this->transactions()
            ->whereNotNull('unit_price')
            ->where(function($query) {
                $query->where('type', 'in')
                    ->orWhere('type', 'opening_balance');
            })
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestPurchase ? $latestPurchase->unit_price : null;
    }
}
