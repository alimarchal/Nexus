<?php

namespace App\Models;

use App\Traits\UserTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchRegister extends Model
{
    /** @use HasFactory<\Database\Factories\DispatchRegisterFactory> */
    use HasFactory;
    use UserTracking;
}
