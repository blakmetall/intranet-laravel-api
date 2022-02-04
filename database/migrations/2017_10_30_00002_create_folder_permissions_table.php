<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFolderPermissionsTable extends Migration
{

    public $set_schema_table = 'folder_permissions';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('folder_id');
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
