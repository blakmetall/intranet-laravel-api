<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsCalendarTable extends Migration
{

    public $set_schema_table = 'events_calendar';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('events_calendar_category_id');
            $table->integer('user_id');
            $table->integer('hotel_id');
            $table->tinyInteger('is_private')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->datetime('start_datetime')->nullable();
            $table->datetime('end_datetime')->nullable();
            $table->tinyInteger('is_finished')->nullable();

            $table->timestamps();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
