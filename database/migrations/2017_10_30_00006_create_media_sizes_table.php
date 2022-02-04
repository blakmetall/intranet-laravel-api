<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaSizesTable extends Migration
{

    public $set_schema_table = 'media_sizes';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('slug')->unique();
            $table->smallInteger('width')->nullable();
            $table->smallInteger('height')->nullable();
            $table->tinyInteger('fit')->nullable();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
