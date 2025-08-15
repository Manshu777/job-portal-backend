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
        Schema::create('courseeducations', function (Blueprint $table) {
            $table->id();
             $table->integer('ordered_id');
            $table->string('title');
            $table->string('verification_status');
            $table->integer('education_level');
            $table->integer('cohort_id')->nullable();
            $table->json('specializations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courseeducations');
    }
};
