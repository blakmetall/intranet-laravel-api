<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrandsiteSectionsTable extends Migration
{

    public $set_schema_table = 'brandsite_sections';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name_es')->nullable();
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->tinyInteger('is_predefined')->nullable();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
