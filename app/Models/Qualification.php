<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    protected $fillable = [
        'title',
        'verification_status',
        'education_level',
        'aliases',
        'cohort_id',
    ];

    protected $casts = [
        'aliases' => 'array',
    ];
}
