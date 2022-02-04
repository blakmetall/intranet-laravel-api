<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMahgazineEditionsTable extends Migration
{

    public $set_schema_table = 'mahgazine_editions';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->datetime('start_datetime');
            $table->datetime('end_datetime')->nullable();
            $table->integer('cover_file_id')->nullable();
            $table->smallInteger('is_published')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
