<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_title_id',
        'result_id',
        'text',
        'type',
        'is_generic',
        'is_l2',
        'similarity',
        'embeddings',
        '10th_or_below_10th',
        '12th_pass',
        'graduate',
        'diploma',
        'iti',
        'post_graduate',
        'source'
    ];

    protected $casts = [
        'is_generic' => 'boolean',
        'is_l2' => 'boolean',
        'similarity' => 'float',
        'embeddings' => 'json',
        '10th_or_below_10th' => 'boolean',
        '12th_pass' => 'boolean',
        'graduate' => 'boolean',
        'diploma' => 'boolean',
        'iti' => 'boolean',
        'post_graduate' => 'boolean',
    ];

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class);
    }
}