<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatesTable extends Migration
{

    public $set_schema_table = 'states';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->nullable();
            $table->string('name')->nullable();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
