<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateEducation extends Model
{
    use HasFactory;

    protected $table = 'candidate_educations';

    protected $fillable = [
        'candidate_id',
        'degree',
        'specialization',
        'college_name',
        'passing_marks',
        'pursuing',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
