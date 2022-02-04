<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMahgazineEditionSectionsTable extends Migration
{

    public $set_schema_table = 'mahgazine_edition_sections';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mahgazine_edition_id');
            $table->string('name')->nullable();
            $table->integer('order')->nullable();
            $table->string('color')->nullable();
            $table->string('template_slug')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
