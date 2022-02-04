<?php

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $exists = DB::table('settings')->first();

        if(!$exists){
            DB::table('settings')->insertGetId(array(
                'id' => 1,
                'timezone' => 'America/Mexico_City',
            ));
        }
    }
}
