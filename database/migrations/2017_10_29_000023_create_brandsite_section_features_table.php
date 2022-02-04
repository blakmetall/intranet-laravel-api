<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrandsiteSectionFeaturesTable extends Migration
{

    public $set_schema_table = 'brandsite_section_features';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('brandsite_section_id');
            $table->string('name_es')->nullable();
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
