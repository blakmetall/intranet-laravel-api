<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('users')->first();

        if(!$exists){

            $json = File::get("database/data/users.json");
            $data = json_decode($json);

            foreach ($data as $obj) {
                $id = DB::table('users')->insertGetId(array(
                    'id' => $obj->id,
                    'user_role_id' => $obj->user_role_id,
                    'email' => $obj->email,
                    'password' => Hash::make('123123'),
                    'valid_email' => $obj->valid_email,
                    'is_enabled' => $obj->is_enabled,
                ));

                if($id){
                    DB::table('user_profiles')->insertGetId(array(
                        'user_id' => $id,
                        'name' => $obj->profile->name,
                        'lastname' => $obj->profile->lastname,
                        'full_name' => $obj->profile->full_name,
                        'job_title' => $obj->profile->job_title,
                        'phone' => $obj->profile->phone,
                        'external_brandsite_enabled' => $obj->profile->external_brandsite_enabled,
                        'external_mahgazine_enabled' => $obj->profile->external_mahgazine_enabled,
                        'is_directory_enabled' => $obj->profile->is_directory_enabled,
                        'use_local_timezone' => $obj->profile->use_local_timezone,
                    ));
                }
            }
        }
    }
}
