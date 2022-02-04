<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQualityAssuranceVisitsTable extends Migration
{

    public $set_schema_table = 'quality_assurance_visits';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quality_assurance_visit_category_id');
            $table->integer('hotel_id');
            $table->integer('owner_user_id');
            $table->datetime('datetime')->nullable();
            $table->smallInteger('revision_number')->nullable();
            $table->decimal('score',5,2)->nullable();
            $table->integer('notification_file_id')->nullable();
            $table->integer('report_file_id')->nullable();

            $table->timestamps();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
