<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_admin_languages', function (Blueprint $table) {
            $table->increments('id');
            $table->string("code", 15);
            $table->string("lcidb", 15);
            $table->string("lcidt", 15);
            $table->string("french")->nullable();
            $table->string("english")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ry_admin_languages');
    }
}
