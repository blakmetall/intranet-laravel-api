<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoldersTable extends Migration
{

    public $set_schema_table = 'folders';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('polymorphic_id');
            $table->string('polymorphic_type');
            $table->string('name')->nullable();
            $table->string('slug')->unique();
            $table->tinyInteger('is_root')->nullable();
            $table->tinyInteger('is_private')->nullable();
            $table->tinyInteger('is_featured')->nullable();
            $table->integer('user_owner_id');

            $table->timestamps();
            $table->softDeletes();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
