<?php

use Illuminate\Database\Seeder;

class AssuranceVisitStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('quality_assurance_visit_status')->first();

        if(!$exists){

            $json = File::get("database/data/assurance-visit-status.json");
            $data = json_decode($json);

            foreach ($data as $obj) {
                DB::table('quality_assurance_visit_status')->insertGetId(array(
                    'id' => $obj->id,
                    'name' => $obj->name,
                    'slug' => $obj->slug,
                ));
            }

        }
    }
}
