<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class DispatchRegister extends Model
{
    /** @use HasFactory<\Database\Factories\DispatchRegisterFactory> */
    use HasFactory, UserTracking;

    protected $fillable = [
        'reference_number', // Changed from reference_no to match database column
        'date',
        'dispatch_no',
        'division_id',
        'name_of_courier_service', // Changed from courier_name to match form
        'receipt_no',
        'particulars',
        'address',
        'attachment',
        'created_by',
        'updated_by'
    ];

    // Example relationship
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}