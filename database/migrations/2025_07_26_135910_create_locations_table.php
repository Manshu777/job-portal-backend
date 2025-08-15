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
        Schema::create('locations', function (Blueprint $table) {
          $table->id();
            $table->string('name', 100);
            $table->string('area_id', 24)->nullable();
            $table->string('city_id', 24)->nullable();
            $table->string('sub_district_id', 24)->nullable();
            $table->string('cluster_id', 24)->nullable();
            $table->boolean('all_areas_enabled');
            $table->string('area_name', 100)->nullable();
            $table->string('sub_district_name', 100)->nullable();
            $table->string('sub_district_district', 100)->nullable();
            $table->foreign('city_id')->references('city_id')->on('cities')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
