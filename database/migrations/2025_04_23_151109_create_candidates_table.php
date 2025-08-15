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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
       
            $table->string('full_name');
            $table->date('dob');
            $table->string('gender');
            $table->string('email')->unique();
            $table->string('address');
            $table->string('city');
            $table->string('state');
    
            // Education
            $table->string('degree');
            $table->string('specialization');
            $table->string('college_name');
            $table->string('passing_marks');
            $table->boolean('pursuing')->default(false);
    
            // Experience
            $table->integer('experience_years')->nullable();
            $table->integer('experience_months')->nullable();
            $table->string('job_title')->nullable();
            $table->json('job_roles')->nullable(); // store as JSON
            $table->string('company_name')->nullable();
            $table->decimal('current_salary', 10, 2)->nullable();
            $table->date('start_date')->nullable();
    
            // Preferences
            $table->boolean('prefers_night_shift')->default(false);
            $table->boolean('prefers_day_shift')->default(true);
            $table->boolean('work_from_home')->default(false);
            $table->boolean('work_from_office')->default(true);
            $table->boolean('field_job')->default(false);
            $table->string('employment_type')->nullable();
            $table->string('preferred_language')->nullable();
    
            // Others
            
            $table->string('resume')->nullable(); // for resume file path
            $table->text('skills')->nullable();
    
            // Extra fields
            $table->boolean('active_user')->default(true);
            $table->timestamp('last_login')->nullable();
            $table->integer('total_jobs_applied')->default(0);
            $table->integer('total_job_views')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
