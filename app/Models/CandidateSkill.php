<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateSkill extends Model
{
    use HasFactory;
    protected $table = 'candidate_skills';
    protected $fillable = [
        'candidate_id',
        'skill_name',
    ];

 


    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
