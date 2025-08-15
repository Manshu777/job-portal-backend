<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
     protected $fillable = [
        'title',
        'verification_status',
        'cohort_id',
        'keywords',
    ];
}
