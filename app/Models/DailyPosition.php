<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyPosition extends Model
{
    use HasFactory, SoftDeletes;

    // Table associated with the model
    protected $table = 'daily_positions'; // This is optional if the table name follows the Laravel convention

    // The attributes that are mass assignable
    protected $fillable = [
        'branch_id', 'consumer', 'commercial', 'micro', 'agri',
        'totalAssets', 'govtDeposit', 'privateDeposit',
        'totalDeposits', 'casa', 'tdr', 'totalCasaTdr',
        'grandTotal', 'noOfAccount', 'noOfAcc', 'profit', 'date',
        'updated_by_user_id', 'created_by_user_id'
    ];

    // The attributes that should be cast to native types
    protected $casts = [
        'date' => 'datetime', // Cast date to Carbon instance
    ];

    /**
     * Get the branch associated with the Daily Position.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created the daily position record.
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the user who updated the daily position record.
     */
    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
