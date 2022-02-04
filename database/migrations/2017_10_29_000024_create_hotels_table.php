<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelsTable extends Migration
{

    public $set_schema_table = 'hotels';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hotel_address_id')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->unique();

            $table->string('presentation_file_id')->nullable();
            $table->string('logo_file_id')->nullable();

            $table->smallInteger('stars')->nullable();
            $table->tinyInteger('is_enabled')->nullable();
            $table->text('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();

            $table->text('social_facebook')->nullable();
            $table->text('social_twitter')->nullable();
            $table->text('social_instagram')->nullable();
            $table->text('social_youtube')->nullable();
            $table->text('social_pinterest')->nullable();
            $table->text('social_tripadvisor')->nullable();
            $table->text('social_linkedin')->nullable();

            $table->smallInteger('order')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(){
        Schema::dropIfExists($this->set_schema_table);
    }
}
