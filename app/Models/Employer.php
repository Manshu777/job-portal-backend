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
        'credits',
        'remark',
    ];

    protected $hidden = ['password', 'otp'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'credits' => 'integer',
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

    public function deductCredits($amount)
    {
        if ($this->credits >= $amount) {
            $this->credits -= $amount;
            $this->save();
            $this->creditTransactions()->create([
                'amount' => -$amount,
                'type' => 'deduction',
                'description' => 'Credits deducted for job posting',
                'transaction_date' => now(),
            ]);
            return true;
        }
        return false;
    }

    public function addCredits($amount, $description = 'Credits purchased')
    {
        $this->credits += $amount;
        $this->save();
        $this->creditTransactions()->create([
            'amount' => $amount,
            'type' => 'purchase',
            'description' => $description,
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

    public function hasEnoughCredits($amount)
    {
        return $this->credits >= $amount;
    }
}