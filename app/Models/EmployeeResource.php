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

    /**
     * The table associated with the model.
     */
    protected $table = 'employee_resources';

    /**
     * The primary key type is UUID.
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'user_id',
        'category_id',
        'division_id',
        'resource_no',
        'resource_number',
        'title',
        'description',
        'attachment',
    ];

    /**
     * Boot function to assign UUID and auto-generate resource_number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
            if (empty($model->resource_number)) {
                $model->resource_number = strtoupper('RES-' . Str::random(8));
            }
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
