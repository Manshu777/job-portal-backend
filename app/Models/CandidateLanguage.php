<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateLanguage extends Model
{
    use HasFactory;

    protected $table = 'candidate_languages';

    protected $fillable = [
        'candidate_id',
        'language_name',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
