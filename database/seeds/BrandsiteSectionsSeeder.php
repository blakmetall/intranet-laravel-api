<?php

use Illuminate\Database\Seeder;

class BrandsiteSectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('brandsite_sections')->first();

        if(!$exists){

            $json = File::get("database/data/brandsite-sections.json");
            $data = json_decode($json);

            foreach ($data as $obj) {

                $id = DB::table('brandsite_sections')->insertGetId(array(
                    'name_es' => $obj->name_es,
                    'name_en' => $obj->name_en,
                    'slug' => $obj->slug,
                    'is_predefined' => 1
                ));

                // brandsite features
                if(count($obj->features)){
                    foreach($obj->features as $obj_feature){

                        $feature = new \App\Models\BrandsiteSectionFeature;

                        $feature->brandsite_section_id = $id;
                        $feature->name_es = $obj_feature->name_es;
                        $feature->name_en = $obj_feature->name_en;

                        $feature->save();
                    }
                }

            }

        }
    }
}
