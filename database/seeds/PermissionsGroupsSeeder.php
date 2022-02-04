<?php

use Illuminate\Database\Seeder;

class PermissionsGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('user_permissions_groups')->first();

        if(!$exists){

            $json = File::get("database/data/permissions-groups.json");
            $data = json_decode($json);

            foreach ($data as $obj) {
                $id = DB::table('user_permissions_groups')->insertGetId(array(
                    'name' => $obj->name,
                    'slug' => $obj->slug,
                    'is_administrative_group' => $obj->is_administrative_group
                ));

                if($id){

                    if(count($obj->permissions)){
                        foreach($obj->permissions as $permission){
                            DB::table('user_permissions')->insertGetId(array(
                                'user_permissions_group_id' => $id,
                                'name' => $permission->name,
                                'slug' => $permission->slug,
                            ));
                        }
                    }
                }
            }
        }
    }
}
