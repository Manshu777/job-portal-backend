<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarkToEmployersTable extends Migration
{
    public function up()
    {
        Schema::table('employers', function (Blueprint $table) {
            $table->text('remark')->nullable(); // Add the remark field
        });
    }

    public function down()
    {
        Schema::table('employers', function (Blueprint $table) {
            $table->dropColumn('remark'); // Remove the remark field if rolling back
        });
    }
}
