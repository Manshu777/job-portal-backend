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
            $table->string('industry')->nullable()->after('job_type');
            $table->string('department')->nullable()->after('industry');
            $table->string('job_role')->nullable()->after('department');
                     $table->decimal('min_salary', 15, 2)->nullable()->after('compensation');
            $table->decimal('max_salary', 15, 2)->nullable()->after('min_salary');
            $table->decimal('incentive', 15, 2)->nullable()->after('max_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            //
        });
    }
};
