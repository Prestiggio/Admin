<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLayoutSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_admin_layout_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('layout_id');
            $table->string('name');
            $table->text('default_setup')->nullable();
            $table->timestamps();
            
            $table->foreign('layout_id')->references('id')->on('ry_admin_layouts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ry_admin_layout_sections');
    }
}
