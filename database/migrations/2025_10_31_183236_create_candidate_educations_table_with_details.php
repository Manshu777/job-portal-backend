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
        Schema::create('candidate_educations', function (Blueprint $table) {

            $table->enum('education_type', ['graduation', 'post_graduation', 'other'])->default('other');
        $table->string('education_level')->nullable();        // B.Tech, M.Tech, 10th, etc.
        $table->string('specialization')->nullable();
        $table->string('college_name')->nullable();
        $table->year('complete_years')->nullable();
        $table->string('complete_month')->nullable();
        $table->string('school_medium')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_educations');
    }
};
