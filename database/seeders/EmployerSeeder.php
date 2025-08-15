<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employer;
class EmployerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        Employer::create([
            'company_name' => 'Techverse Pvt Ltd',
            'contact_email' => 'hr@techverse.com',
            'contact_person' => 'hr@techverse.com',

            'contact_phone' => '9876543210',

            'company_location' => 'Bangalore, India',
        ]);

        Employer::create([
            'company_name' => 'MediCare Solutions',
            'contact_email' => 'hr@techverse.com',
            'contact_person' => 'contact@medicare.com',

            'contact_phone' => '9123456789',

        
            'company_location' => 'Mumbai, India',
        ]);
    }
}
