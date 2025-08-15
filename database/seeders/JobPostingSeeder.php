<?php

namespace Database\Seeders;

use App\Models\JobPosting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JobPostingSeeder extends Seeder
{
    public function run()
    {
        $jobTitles = [
            'Software Developer', 'Sales Manager', 'HR Executive', 'Digital Marketer',
            'Backend Developer', 'Frontend Developer', 'Network Engineer', 'Business Analyst',
            'Graphic Designer', 'Content Writer', 'Customer Support Executive', 'QA Tester'
        ];

        $locations = [
            'Bangalore, India', 'Mumbai, India', 'Hyderabad, India', 'Pune, India', 'Chennai, India'
        ];

        $payTypes = ['hourly', 'monthly', 'yearly'];
        $workLocationTypes = ['remote', 'onsite', 'hybrid'];
        $jobTypes = ['full_time', 'part_time', 'internship', 'freelance'];

        // Add jobs for employer_id 1 and 2
        foreach (range(1, rand(10, 20)) as $i) {
            JobPosting::create([
                'employer_id' => rand(1, 2),
                'job_title' => $jobTitles[array_rand($jobTitles)],
                'job_type' => $jobTypes[array_rand($jobTypes)],
                'location' => $locations[array_rand($locations)],
                'work_location_type' => $workLocationTypes[array_rand($workLocationTypes)],
                'compensation' => rand(3, 20) . ' LPA', // e.g., "6 LPA"
                
       
                'basic_requirements' => 'Basic knowledge of the domain, strong communication skills.',
                'additional_requirements' => ['Team player', 'Problem-solving skills'],
                'job_description' => 'We are looking for a passionate individual to join our team.',
                'is_walkin_interview' => rand(0, 1),
                'total_experience_required' => rand(0, 5),
                'other_job_titles' => ['Junior ' . $jobTitles[array_rand($jobTitles)], 'Assistant ' . $jobTitles[array_rand($jobTitles)]],
                'degree_specialization' => ['B.Tech', 'MBA', 'B.Sc IT'],
              
                'number_of_candidates_required' => rand(1, 10),
            ]);
        }
    }
}
