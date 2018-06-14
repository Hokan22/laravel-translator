<?php

/**
 * Migration
 */
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Translator Migration
 *
 * @package  Hokan22\LaravelTranslator\migrations
 * @author   Alexander Viertel <alexander@aviertel.de>
 * @license  http://opensource.org/licenses/MIT MIT
 */
class CreateTranslatorTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::beginTransaction();

        // Create table for storing roles
        Schema::create('translation_identifiers', function(Blueprint $table) {
            $table->increments('id')->unique();
            $table->text('identifier');

            $table->string('parameters', 512)->nullable();
            $table->string('group')->default('default');

            $table->string('page_name')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });

        // Create table for storing roles
        Schema::create('translations', function(Blueprint $table) {
            $table->integer('translation_identifier_id')->unsigned();
            $table->foreign('translation_identifier_id')->references('id')->on('translation_identifiers')->onDelete('cascade')->onUpdate('no action');

            $table->string('locale', 5);
            $table->primary(['translation_identifier_id', 'locale']);

            $table->text('translation');

            $table->timestamps();
        });

        DB::statement('ALTER TABLE `translations` CHANGE `updated_at` `updated_at` TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT NULL;');

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('translations');
        Schema::drop('translation_identifiers');
    }
}
