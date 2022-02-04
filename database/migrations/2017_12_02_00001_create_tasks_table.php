<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{

    public $set_schema_table = 'tasks';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_status_id');
            $table->integer('owner_user_id');
            $table->integer('assigned_user_id');
            $table->tinyInteger('is_pinned_to_calendar')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('is_finished')->nullable();

            $table->timestamps();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
