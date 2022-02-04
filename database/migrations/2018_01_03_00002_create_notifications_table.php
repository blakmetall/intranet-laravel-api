<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{

    public $set_schema_table = 'notifications';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->text('url')->nullable();
            $table->smallInteger('viewed')->nullable();

            $table->timestamps();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
