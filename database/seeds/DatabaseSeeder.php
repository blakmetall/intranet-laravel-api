<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){

        $this->call([
            // app settings
            SettingsSeeder::class,

            // countries and states
            CountriesSeeder::class,

            // status of assurance visit
            AssuranceVisitStatusSeeder::class,

            // brandsite default sections
            BrandsiteSectionsSeeder::class,

            // tasks status to be selected
            TasksStatusSeeder::class,

            // user roles available
            UserRolesSeeder::class,

            // default image sizes
            MediaSizesSeeder::class,

            // default folders for "my-files" and "quality files"
            FoldersSectionsSeeder::class,

            // users
            UsersSeeder::class,

            // company categories
            CompanyCategoriesSeeder::class,

            // companies
            CompaniesSeeder::class,

            // permissions groups
            PermissionsGroupsSeeder::class
        ]);

    }
}
