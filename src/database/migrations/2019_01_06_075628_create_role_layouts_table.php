<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleLayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_admin_role_layouts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('layout_id');
            $table->text('sections_setup')->nullable();
            $table->timestamps();
            
            $table->foreign('role_id')->references('id')->on('ry_admin_roles')->onDelete('cascade');
            $table->unique(['role_id', 'layout_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ry_admin_role_layouts');
    }
}
