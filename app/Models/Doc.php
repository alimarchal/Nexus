<?php

// app/Models/Doc.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Builder;



class Doc extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'division_id',
        'title',
        'description',
        'document'
    ];

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

    public function scopeTitle(Builder $query, string $search): void
    {
        $query->where('title', 'LIKE', "%{$search}%");
    }

    /**
     * Scope for created_at filter
     */
    public function scopeCreatedAt(Builder $query, string $date): void
    {
        $query->whereDate('created_at', $date);
    }

}
