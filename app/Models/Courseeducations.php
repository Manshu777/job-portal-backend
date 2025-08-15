<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courseeducations extends Model
{
    
    protected $fillable = [
        'ordered_id',
        'title',
        'verification_status',
        'education_level',
        'cohort_id'
    ];

    protected $casts = [
        'specializations' => 'array'
    ];
}
