<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employer_id')->constrained('employers');  // Foreign key to employers table
            $table->string('job_title');
            $table->enum('job_type', ['Full-Time', 'Part-Time', 'Freelance']);
            $table->string('location');
            $table->enum('work_location_type', ['Work from Home', 'Work from Office', 'Hybrid']);
            $table->string('compensation');
            $table->enum('pay_type', ['Hourly', 'Salary', 'Per Project']);
            $table->boolean('joining_fee')->default(false);
            $table->text('basic_requirements');
            $table->json('additional_requirements')->nullable();
       
            $table->boolean('is_walkin_interview')->default(false);
            $table->enum('communication_preference', ['Call', 'Whatsapp', 'No Preference']);

            $table->integer('total_experience_required')->nullable(); // Total experience required
            $table->json('other_job_titles')->nullable(); // Candidates from which other job titles/roles can apply
            $table->json('degree_specialization')->nullable(); // Degree / specialization
            $table->text('job_description')->nullable(); // Job description (Already exists but add it to the migration if needed)
            $table->integer('job_expire_time')->default(7); // Job expire time in days (default is 7)
            $table->integer('number_of_candidates_required')->default(1); // Number of candidates required

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
