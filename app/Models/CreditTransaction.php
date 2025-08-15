<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'amount',
        'type',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'integer',
        'transaction_date' => 'datetime',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
}