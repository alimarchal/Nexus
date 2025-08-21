<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'type',
    ];

    /**
     * Relationship: A category can have many employee resources.
     */
    public function employeeResources()
    {
        return $this->hasMany(EmployeeResource::class, 'category_id');
    }
}
