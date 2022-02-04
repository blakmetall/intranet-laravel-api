<?php

use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('countries')->first();

        if(!$exists){

            $json = File::get("database/data/countries.json");
            $data = json_decode($json);

            foreach ($data as $obj) {
                $id = DB::table('countries')->insertGetId(array(
                    'name' => $obj->name,
                    'country_code' => $obj->country_code,
                    'country_code_long' => $obj->country_code_long,
                ));
                if($obj->states){
                    foreach ( $obj->states as $key) {
                        foreach ( $key as $value) {
                            DB::table('states')->insert(array(
                                'country_id' => $id,
                                'name' =>$value,
                            ));
                        }
                    }
                }
            }

        }
    }
}
