<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateEducation extends Model
{
    use HasFactory;

    protected $table = 'candidate_educations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'candidate_id',
        'education_type',     // graduation | post_graduation | other
        'education_level',    // B.Tech, M.Sc, 10th, etc.
        'specialization',     // CSE, Marketing, etc.
        'college_name',       // IIT Delhi, etc.
        'complete_years',     // 2023
        'complete_month',     // June
        'school_medium',      // English, Hindi
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'complete_years' => 'integer',
        'education_type' => 'string',
    ];

    /**
     * Relationship: belongs to a candidate
     */
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    // =================================================================
    // OPTIONAL: Scopes for convenience
    // =================================================================

    /**
     * Scope: Only graduation records
     */
    public function scopeGraduation($query)
    {
        return $query->where('education_type', 'graduation');
    }

    /**
     * Scope: Only post-graduation records
     */
    public function scopePostGraduation($query)
    {
        return $query->where('education_type', 'post_graduation');
    }

    /**
     * Scope: Only completed (not pursuing)
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('complete_years');
    }
}