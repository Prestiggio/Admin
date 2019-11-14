<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ry_admin_timelines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('serializable');
            $table->unsignedBigInteger('revert_id')->nullable();
            $table->json('setup')->nullable();
            $table->enum('action', ['created', 'updated', 'deleted', 'cancelled'])->nullable();
            $table->boolean('active');
            $table->timestamp('save_at')->nullable();
            $table->timestamp('delete_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ry_admin_timelines');
    }
}
