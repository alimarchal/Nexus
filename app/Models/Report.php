<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
      // Define the table name if it's not the plural form of the class name
      protected $table = 'reports';

      // Define the fields that are mass assignable
      protected $fillable = ['date', 'branch_name', 'branch_code', 'status'];
  
      // If you have timestamps in your database, this is optional
      public $timestamps = true;
}
