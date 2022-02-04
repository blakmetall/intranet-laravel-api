<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePasswordResetsTable extends Migration
{

    public $set_schema_table = 'password_resets';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->string('email');
            $table->string('token')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(){
        Schema::dropIfExists($this->set_schema_table);
    }
}
