<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StationeryTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\StationeryTransactionFactory> */
    use HasFactory, UserTracking;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'printed_stationery_id',
        'stock_out_to',
        'division_id',
        'branch_id',
        'region_id',
        'type',
        'quantity',
        'unit_price',
        'balance_after_transaction',
        'document_path',
        'transaction_date',
        'reference_number',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_date' => 'date',
        'quantity' => 'integer',
        'balance_after_transaction' => 'integer',
        'unit_price' => 'decimal:2'
    ];

    /**
     * Get the stationery item associated with this transaction.
     */
    public function printedStationery(): BelongsTo
    {
        return $this->belongsTo(PrintedStationery::class);
    }

    /**
     * Get the branch associated with this transaction.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the region associated with this transaction.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the division associated with this transaction.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Scope for opening balance transactions.
     */
    public function scopeOpeningBalance($query)
    {
        return $query->where('type', 'opening_balance');
    }

    /**
     * Scope for stock in transactions.
     */
    public function scopeStockIn($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for stock out transactions.
     */
    public function scopeStockOut($query)
    {
        return $query->where('type', 'out');
    }
}
