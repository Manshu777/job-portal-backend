<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitiesData extends Model
{
 use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'tier',
        'is_live',
        'centroid_latitude',
        'centroid_longitude',
        'childrens',
    ];

    protected $casts = [
        'is_live' => 'boolean',
        'childrens' => 'array',
        'centroid_latitude' => 'float',
        'centroid_longitude' => 'float',
    ];
}
