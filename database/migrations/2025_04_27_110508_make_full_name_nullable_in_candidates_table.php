<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            
                $table->string('number')->nullable(); // Add column if it doesn't exist
            

            $table->string('full_name')->nullable()->change();
            $table->date('dob')->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('degree')->nullable()->change();
            $table->string('specialization')->nullable()->change();
            $table->string('college_name')->nullable()->change();
            $table->string('passing_marks')->nullable()->change();
            $table->tinyInteger('pursuing')->default(0)->change();
            $table->integer('experience_years')->nullable()->change();
            $table->integer('experience_months')->nullable()->change();
            $table->string('job_title')->nullable()->change();
            $table->string('job_roles')->nullable()->change();
            $table->string('company_name')->nullable()->change();
            $table->decimal('current_salary', 10, 2)->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->tinyInteger('prefers_night_shift')->default(0)->change();
            $table->tinyInteger('prefers_day_shift')->default(1)->change();
            $table->tinyInteger('work_from_home')->default(0)->change();
            $table->tinyInteger('work_from_office')->default(1)->change();
            $table->tinyInteger('field_job')->default(0)->change();
            $table->string('employment_type')->nullable()->change();
            $table->string('preferred_language')->nullable()->change();
            $table->string('resume')->nullable()->change();
            $table->tinyInteger('active_user')->default(1)->change();
            $table->timestamp('last_login')->nullable()->change();
            $table->integer('total_jobs_applied')->default(0)->change();
            $table->integer('total_job_views')->default(0)->change();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
            $table->string('otp')->nullable()->change();
            $table->string('token')->nullable();
            $table->string('password')->nullable();
        $table->json('skills')->default('[]')->change();
            $table->boolean("doneprofile")->default(1);
            $table->timestamp('otp_expires_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('number'); // Optional: Drop if rolling back
        });
    }
};
