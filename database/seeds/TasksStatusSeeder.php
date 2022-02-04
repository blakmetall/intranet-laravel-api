<?php

use Illuminate\Database\Seeder;

class TasksStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('tasks_status')->first();

        if(!$exists){

            $json = File::get("database/data/tasks-status.json");
            $data = json_decode($json);

            foreach ($data as $obj) {
                DB::table('tasks_status')->insertGetId(array(
                    'id' => $obj->id,
                    'name' => $obj->name,
                    'slug' => $obj->slug,
                ));
            }

        }
    }
}
