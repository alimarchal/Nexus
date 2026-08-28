<?php

namespace App\Models;

use App\Traits\UserTracking;
use Database\Factories\FileCategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileCategory extends Model
{
    /** @use HasFactory<FileCategoryFactory> */
    use HasFactory, HasUuids, SoftDeletes, UserTracking;
}
