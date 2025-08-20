<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    // Relation with employee resources
    public function employeeResources()
    {
        return $this->hasMany(EmployeeResource::class, 'category_id');
    }
}
