<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidate>
 */
class CandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name,
            'dob' => $this->faker->date('Y-m-d', '2003-01-01'),
            'gender' => $this->faker->randomElement(['Male', 'Female', 'Other']),
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'prefers_night_shift' => $this->faker->boolean,
            'prefers_day_shift' => $this->faker->boolean,
            'work_from_home' => $this->faker->boolean,
            'work_from_office' => $this->faker->boolean,
            'field_job' => $this->faker->boolean,
            'employment_type' => $this->faker->randomElement(['Full-Time', 'Part-Time', 'Intern']),
            'preferred_language' => $this->faker->randomElement(['English', 'Hindi', 'Punjabi']),
            'resume' => null,
            'skills' => implode(',', $this->faker->randomElements(['PHP', 'Laravel', 'React', 'JavaScript', 'SQL'], 3)),
            'active_user' => true,
            'last_login' => now(),
            'total_jobs_applied' => rand(0, 10),
            'total_job_views' => rand(0, 20),

            'degree' => $this->faker->randomElement(['10th', '12th','ITI','graduate', 'postgraduate', 'diploma']),
            'specialization' => $this->faker->randomElement(['Computer Science', 'Electronics', 'Mechanical']),
            'college_name' => $this->faker->company . ' College',
            'passing_marks' => rand(60, 100) . '%',
            'pursuing' => $this->faker->boolean,
        
            // Experience (optional)
            'experience_years' => rand(0, 5),
            'experience_months' => rand(0, 11),
            'job_title' => $this->faker->jobTitle,
            'job_roles' => json_encode($this->faker->randomElements([
                'Developer', 'Designer', 'Tester', 'Manager'
            ], rand(1, 3))),
            'company_name' => $this->faker->company,
            'current_salary' => $this->faker->randomFloat(2, 10000, 50000),
            'start_date' => $this->faker->date(),
        
            // Preferences
            'prefers_night_shift' => $this->faker->boolean,
            'prefers_day_shift' => $this->faker->boolean,
            'work_from_home' => $this->faker->boolean,
            'work_from_office' => $this->faker->boolean,
            'field_job' => $this->faker->boolean,
            'employment_type' => $this->faker->randomElement(['Full-Time', 'Part-Time', 'Intern']),
            'preferred_language' => implode(',', $this->faker->randomElements(['English', 'Hindi', 'Punjabi'], 2)),
        
            // Others
            'resume' => null,
            'skills' => implode(',', $this->faker->randomElements(['PHP', 'Laravel', 'React', 'JavaScript', 'SQL'], 3)),
            'active_user' => true,
            'last_login' => now(),
            'total_jobs_applied' => rand(0, 10),
            'total_job_views' => rand(0, 20),
            
        ];
    }
}
