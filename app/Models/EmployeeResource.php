<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Traits\UserTracking;

class EmployeeResource extends Model
{
    use HasFactory, SoftDeletes, UserTracking;

    protected $table = 'employee_resources';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'user_id',
        'category',        // CHANGED: from 'category_id' to 'category'
        'division_id',
        'resource_no',
        'resource_number',
        'title',
        'description',
        'attachment',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->resource_number)) {
                $model->resource_number = 'RES-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    // Change the category relationship to use 'category' as foreign key
    public function category()
    {
        return $this->belongsTo(Category::class, 'category'); // Use 'category' instead of 'category_id'


    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

}
