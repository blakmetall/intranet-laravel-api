<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserProfilesTable extends Migration
{

    public $set_schema_table = 'user_profiles';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');

            $table->integer('is_external')->nullable();
            $table->integer('hotel_id')->nullable();
            $table->integer('company_id')->nullable();

            $table->string('job_title')->nullable();
            $table->string('name')->nullable();
            $table->string('lastname')->nullable();
            $table->text('full_name')->nullable();
            $table->string('phone')->nullable();

            $table->tinyInteger('external_brandsite_enabled')->nullable();
            $table->tinyInteger('external_mahgazine_enabled')->nullable();
            $table->tinyInteger('is_directory_enabled')->nullable();

            $table->integer('profile_address_id')->nullable();
            $table->integer('profile_file_id')->nullable();

            $table->tinyInteger('use_local_timezone')->nullable();
            $table->string('timezone')->nullable();

            $table->timestamps();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
