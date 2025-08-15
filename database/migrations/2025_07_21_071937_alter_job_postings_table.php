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
        Schema::table('job_postings', function (Blueprint $table) {
            // Change total_experience_required to string to support "Any", "Fresher", etc.
            $table->string('total_experience_required')->nullable()->change();
            
            // Add missing fields from formData
            $table->json('preferred_roles')->nullable()->after('other_job_titles');
            $table->text('key_responsibilities')->nullable()->after('job_description');
            $table->json('required_skills')->nullable()->after('key_responsibilities');
            $table->json('perks')->nullable()->change(); 
            $table->string('joining_fee_required')->nullable()->after('joining_fee');
            $table->string('interview_mode')->nullable()->after('interview_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
