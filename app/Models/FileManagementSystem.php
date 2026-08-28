<?php

namespace App\Models;

use App\Traits\UserTracking;
use Database\Factories\FileManagementSystemFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileManagementSystem extends Model
{
    /** @use HasFactory<FileManagementSystemFactory> */
    use HasFactory, HasUuids, SoftDeletes, UserTracking;
}
