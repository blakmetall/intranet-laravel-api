<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileHasFeaturesTable extends Migration
{

    public $set_schema_table = 'file_has_features';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('file_id');
            $table->integer('brandsite_section_feature_id');
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
