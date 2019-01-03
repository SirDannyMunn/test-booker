<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $locations = collect();
        foreach (['Skipton', 'Blackburn', 'Preston', 'Nelson', 'halifax'] as $place) {
            $locations->push(factory(App\Location::class)->create(['name' => $place]));
        }

        $userCount = rand(1, 10);
        $userCount = 10;

        for($i=0; $i<$userCount; $i++) {

            $user_locations = $locations->random(rand(1,5));

            $user = factory(App\User::class, $i==0 ? 'admin' : null)->create(['location' => $user_locations[0]->name]);

            foreach ($user_locations as $user_location) {

                factory(\App\UserLocation::class)->create(
                    ['user_id'=>$user->id, 'location_id'=>$user_location->id]
                );
            }
        }
    }
}
