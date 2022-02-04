<?php

use Illuminate\Database\Seeder;

class FoldersSectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('folders_sections')->first();

        if(!$exists){

            $json = File::get("database/data/folders-sections.json");
            $data = json_decode($json);

            foreach ($data as $obj) {
                DB::table('folders_sections')->insertGetId(array(
                    'id' => $obj->id,
                    'name' => $obj->name,
                    'slug' => $obj->slug,
                ));
            }
        }
    }
}
