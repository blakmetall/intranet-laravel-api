<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQualityVisitExensionRequestsTable extends Migration
{

    public $set_schema_table = 'quality_visit_exension_requests';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hotel_id');
            $table->integer('owner_user_id');
            $table->integer('verifier_user_id')->nullable();
            $table->integer('document_file_id')->nullable();
            $table->tinyInteger('is_verified')->nullable();
            $table->tinyInteger('is_approved')->nullable();
            $table->text('policy')->nullable();
            $table->text('application_reasoning')->nullable();
            $table->text('guests_collateral_damage')->nullable();

            $table->timestamps();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
