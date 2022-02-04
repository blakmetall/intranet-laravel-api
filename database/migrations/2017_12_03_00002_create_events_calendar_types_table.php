<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsCalendarTypesTable extends Migration
{

    public $set_schema_table = 'events_calendar_categories';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('slug')->unique();
            $table->string('color', 7);
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
