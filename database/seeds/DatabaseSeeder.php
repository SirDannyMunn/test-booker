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

        $userCount = count($this->users);

        for($i=0; $i<$userCount; $i++) {

            $user_locations = $locations->whereIn('name', isset($this->users[$i]['locations']) ? $this->users[$i]['locations'] : $locations->random(rand(1,3))->pluck('name'));

            $user = factory(App\User::class, $i==0 ? 'admin' : null)->create(
                ['location' => $this->users[$i]['preferred_location'],
                 'tier'=>$this->users[$i]['tier'],
                 'test_date'=>now()->addDays(rand(30,45))->startOfWeek()->addDays(rand(0,4))]
            );

            foreach ($user_locations as $user_location) {

                factory(\App\UserLocation::class)->create(
                    ['user_id'=>$user->id, 'location_id'=>$user_location->id]
                );
            }
        }
    }

    private $users = [
    [
        'preferred_location' => 'Skipton',
        'tier' => 'premium',
    ],[
        'preferred_location' => 'Skipton',
        'tier' => 'paid'
    ],[
        'preferred_location' => 'Blackburn',
        'tier' => 'premium',
        'locations' => [
            'Skipton'
        ]
    ],
    ];
}
