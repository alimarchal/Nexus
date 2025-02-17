<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $fillable = [
        'division_id',
        'manager_user_id',
        'title',
        'created_by_user_id',
        'updated_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function managerUser()
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'update_by');
    }
}