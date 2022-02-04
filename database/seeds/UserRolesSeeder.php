<?php

use Illuminate\Database\Seeder;

class UserRolesSeeder extends Seeder
{

    public function run()
    {
        $exists = DB::table('user_roles')->first();

        if(!$exists){

            $json = File::get("database/data/user-roles.json");
            $data = json_decode($json);

            foreach ($data as $obj) {
                DB::table('user_roles')->insertGetId(array(
                    'id' => $obj->id,
                    'name' => $obj->name,
                    'slug' => $obj->slug,
                ));
            }

        }
    }
}
