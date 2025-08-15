<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = ['job_title'];

    public function results()
    {
        return $this->hasMany(JobResult::class);
    }
}