<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPermissionsTable extends Migration
{

    public $set_schema_table = 'user_permissions';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_permissions_group_id');
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
