<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{

    public $set_schema_table = 'users';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('user_role_id')->nullable();
            $table->string('email');
            $table->string('password')->nullable();
            $table->tinyInteger('valid_email')->default(0);
            $table->string('recovery_key')->nullable();
            $table->tinyInteger('is_enabled')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->rememberToken();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
