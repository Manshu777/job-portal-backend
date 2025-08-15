<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $primaryKey = 'city_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['city_id', 'name', 'status'];

    public function locations()
    {
        return $this->hasMany(Location::class, 'city_id', 'city_id');
    }
}
