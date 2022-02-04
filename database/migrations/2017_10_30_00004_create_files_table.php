<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{

    public $set_schema_table = 'files';


    public function up(){
        if (Schema::hasTable($this->set_schema_table)) return;
        Schema::create($this->set_schema_table, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('polymorphic_id');
            $table->string('polymorphic_type');
            $table->string('input_id')->nullable(); // string identifier for special files
            $table->tinyInteger('is_featured')->nullable();
            $table->tinyInteger('is_image')->nullable();
            $table->tinyInteger('flip_page_enabled')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->string('name')->nullable();
            $table->string('original_name')->nullable();
            $table->string('slug')->unique();
            $table->string('extension', 10)->nullable();
            $table->string('mime_type')->nullable();
            $table->decimal('size_bytes', 20)->nullable(); //size in MegaBytess
            $table->smallInteger('version')->nullable()->default(1);
            $table->string('system_folder')->nullable();
            $table->text('filepath')->nullable();
            $table->text('url')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

     public function down(){
       Schema::dropIfExists($this->set_schema_table);
     }
}
