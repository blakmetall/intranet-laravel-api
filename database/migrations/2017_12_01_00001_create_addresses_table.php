<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration
{

    public $set_schema_table = 'addresses';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('polymorphic_id')->nullable();
            $table->string('polymorphic_type')->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->string('municipality_or_county')->nullable();

            $table->string('street')->nullable();
            $table->string('exterior_number')->nullable();
            $table->string('interior_number')->nullable();
            $table->string('colony')->nullable();
            $table->string('zip')->nullable();

            $table->timestamps();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
