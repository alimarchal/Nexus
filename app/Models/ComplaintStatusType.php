<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintStatusType extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'status_id');
    }
}