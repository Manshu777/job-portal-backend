<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Employer extends Model
{
    use HasFactory, HasApiTokens;

   protected $fillable = [
    'name',
    'gst_number',
    'company_name',
    'company_location',
    'contact_person',
    'contact_email',
    'contact_phone',
    'password',
    'email_verified_at',
    'otp',
    'is_verified',
    'session_token',
    'is_blocked',
    'job_post_credits',
    'database_credits',
    'remark',
];


    protected $hidden = ['password', 'otp'];

    protected $casts = [
        'email_verified_at' => 'datetime',
 
    'job_post_credits' => 'integer',
    'database_credits' => 'integer',
    ];

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class);
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class);
    }

 public function deductCredits($amount, $type = 'job_post')
{
    $field = $type . '_credits';

    if ($this->$field >= $amount) {
        $this->$field -= $amount;
        $this->save();

        $this->creditTransactions()->create([
            'amount' => -$amount,
            'type' => 'deduction',
            'credit_type' => $type,
            'description' => ucfirst($type) . ' credits deducted',
            'transaction_date' => now(),
        ]);
        return true;
    }
    return false;
}

    public function addCredits($amount, $type = 'job_post', $description = 'Credits purchased')
{
    $field = $type . '_credits';

    $this->$field += $amount;
    $this->save();

    $this->creditTransactions()->create([
        'amount' => $amount,
        'type' => 'purchase',
        'credit_type' => $type,
        'description' => ucfirst($type) . ' credits added - ' . $description,
        'transaction_date' => now(),
    ]);
    return true;
}
     public function viewedCandidates()
    {
        return $this->belongsToMany(Candidate::class, 'employer_candidate_views')
                    ->withPivot('number_revealed', 'revealed_at')
                    ->withTimestamps();
    }


    public function resetDailyCredits()
{
    $today = now()->toDateString();

    // Only reset once per day
    if ($this->last_credit_reset !== $today) {
        $this->job_post_credits = 30;
        $this->database_credits = 20;
        $this->last_credit_reset = $today;
        $this->save();
    }
}

  
public function hasEnoughCredits($amount, $type = 'job_post')
{
    $field = $type . '_credits';
   
   
    return $this->$field >= $amount;
}
}