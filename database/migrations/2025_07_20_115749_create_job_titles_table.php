<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('job_titles', function (Blueprint $table) {
            $table->id();
            $table->string('job_title');
            $table->timestamps();
        });

        Schema::create('job_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_title_id')->constrained()->onDelete('cascade');
            $table->string('result_id')->unique();
            $table->string('text');
            $table->string('type');
            $table->boolean('is_generic')->nullable();
            $table->boolean('is_l2')->default(false);
            $table->float('similarity')->nullable();
            $table->json('embeddings')->nullable();
            $table->boolean('10th_or_below_10th')->nullable();
            $table->boolean('12th_pass')->nullable();
            $table->boolean('graduate')->nullable();
            $table->boolean('diploma')->nullable();
            $table->boolean('iti')->nullable();
            $table->boolean('post_graduate')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_results');
        Schema::dropIfExists('job_titles');
    }
};
