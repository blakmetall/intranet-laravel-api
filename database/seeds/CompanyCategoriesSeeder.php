<?php

use Illuminate\Database\Seeder;

class CompanyCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('company_categories')->first();

        if(!$exists){

            $json = File::get("database/data/company-categories.json");
            $data = json_decode($json);

            foreach ($data as $obj) {
                DB::table('company_categories')->insertGetId(array(
                    'id' => $obj->id,
                    'name' => $obj->name,
                    'slug' => $obj->slug,
                ));
            }
        }
    }
}
