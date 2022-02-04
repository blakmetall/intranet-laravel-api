<?php

use Illuminate\Database\Seeder;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('companies')->first();

        if(!$exists){

            $json = File::get("database/data/companies.json");
            $data = json_decode($json);

            foreach ($data as $obj) {
                DB::table('companies')->insertGetId(array(
                    'id' => $obj->id,
                    'company_category_id' => $obj->company_category_id,
                    'name' => $obj->name,
                    'email' => $obj->email,
                    'phone' => $obj->phone
                ));
            }
        }
    }
}
