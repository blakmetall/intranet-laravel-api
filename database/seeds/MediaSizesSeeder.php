<?php

use Illuminate\Database\Seeder;

class MediaSizesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('media_sizes')->first();

        if(!$exists){

            $json = File::get("database/data/media-sizes.json");
            $data = json_decode($json);

            foreach ($data as $obj) {
                DB::table('media_sizes')->insertGetId(array(
                    'id' => $obj->id,
                    'name' => $obj->name,
                    'slug' => $obj->slug,
                    'width' => $obj->width,
                    'height' => $obj->height,
                    'fit' => $obj->fit
                ));
            }
        }
    }
}
