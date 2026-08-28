<?php

namespace App\Models;

use App\Traits\UserTracking;
use Database\Factories\FileCategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileCategory extends Model
{
    /** @use HasFactory<FileCategoryFactory> */
    use HasFactory, HasUuids, SoftDeletes, UserTracking;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_code',
        'category_name',
        'is_active',
    ];

    /**
     * @return HasMany<FileManagementSystem, $this>
     */
    public function fileManagementSystems(): HasMany
    {
        return $this->hasMany(FileManagementSystem::class);
    }
}
