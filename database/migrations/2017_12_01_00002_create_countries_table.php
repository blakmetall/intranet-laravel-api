<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration
{

    public $set_schema_table = 'countries';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country_code_long')->nullable();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
