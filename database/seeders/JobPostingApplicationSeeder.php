<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPostingApplication; // Import the model
use App\Models\Candidate; // Import Candidate model
use App\Models\JobPosting; // Import JobPosting model

class JobPostingApplicationSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all candidates and job postings (or use your own logic)
        $candidates = Candidate::all();
        $jobPostings = JobPosting::all();

        // Create sample job posting applications
        foreach ($candidates as $candidate) {
            foreach ($jobPostings->random(2) as $jobPosting) { // Randomly select 2 job postings
                JobPostingApplication::create([
                    'candidate_id' => $candidate->id, 
                    'user_id' => 1,
                    'job_posting_id' => $jobPosting->id,
                    'status' => fake()->randomElement(['applied', 'interview', 'rejected', 'hired']),
                ]);
            }
        }
    }
}
