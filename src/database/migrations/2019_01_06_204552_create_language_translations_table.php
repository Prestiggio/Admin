<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLanguageTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * SELECT t0.lang AS idl0, t0.translation_string AS trad0, t1.lang AS idl1, t1.translation_string AS trad1, t2.lang AS idl2, t2.translation_string AS trad2, t3.lang AS idl3, t3.translation_string AS trad3 FROM `ry_admin_language_translations` t0 
LEFT JOIN ry_admin_language_translations t1 ON t0.translation_id = t0.translation_id AND t1.lang = 'de'
LEFT JOIN ry_admin_language_translations t2 ON t2.translation_id = t0.translation_id AND t2.lang = 'en'
LEFT JOIN ry_admin_language_translations t3 ON t3.translation_id = t0.translation_id AND t3.lang = 'es'
WHERE t0.lang = 'fr';
         */
        
        Schema::create('ry_admin_language_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('translation_id');
            $table->char('lang', 15);
            $table->text('translation_string');
            
            $table->unique(['translation_id', 'lang'], 'translation_id_lang');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ry_admin_language_translations');
    }
}
