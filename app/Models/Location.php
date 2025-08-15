<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'area_id',
        'city_id',
        'sub_district_id',
        'cluster_id',
        'all_areas_enabled',
        'area_name',
        'sub_district_name',
        'sub_district_district'
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }
}
