<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Circular extends Model
{
    use HasFactory;

    protected $fillable = [
        'circular_no',
        'division_id',
        'user_id',
        'update_by',
        'attachment',
        'title',
        'description',
    ];

    // Define the relationship with the User (created by)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define the relationship with the Division
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    // Define the relationship with the User (updated by)
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'update_by');
    }
}