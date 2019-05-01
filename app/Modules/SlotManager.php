<?php

namespace App\Modules;

use App\Slot;
use App\UserSlot;
use Carbon\Carbon;

class SlotManager
{
    /**
     * @param $locationSlots
     * @return Illuminate\Support\Collection
     */
    public function mapLocationSlots($locationSlots)
    {
        return $locationSlots->map(function ($item) {
                    
            $slots = $this->getQualifiedSlots($item['slots'], $item['location']);

            if (filled($slots)) return $slots;
        });
    }

    /**
     * Matches users to appropriate slots based on certain factors
     * @param $slots
     * @param $location
     * @return array|\Illuminate\Support\Collection
     */
    public function getQualifiedSlots($slots, $location)
    {
        $users = $location->users->sortByDesc('priority');

        $slots[0]->filter(function ($slot) use ($users) {
            return Carbon::parse($users->pluck('test_date')->sort()->last())->lessThan($slot);
        });

        $user_points = $this->rankUserSlots($slots, $users, $location);

        if (!$user_points) {
            $this->window->quit();
            abort(500, 'No valid slots found');
        }

        $eligible_candidates = array_filter($user_points, function ($user_point) {
            return $user_point==0;
        });

        $matched_slots = $this->mapUserSlots($eligible_candidates, $location);

        return $matched_slots;
    }

    /**
     * @param $slots [[string, ...]]
     * @param $users [User::class]
     * @param $location [Location::class]
     * @return array of ranked user against respective slots
     */
    private function rankUserSlots($slots, $users, $location)
    {
        $user_points = [];
        foreach ($slots[0] as $slot) {
            $user_points[$slot] = [];
            foreach ($users as $user) {
                $id = $user->id;
                $user_points[$slot][$id] = 0;
                if (Carbon::parse($slot)->greaterThanOrEqualTo($user->test_date))
                    continue;
                if ($user->priority)
                    $user_points[$slot][$id] += 2;
                if ($user->location == $location->name)
                    $user_points[$slot][$id] += 3; continue;
                /** @noinspection PhpUnreachableStatementInspection */
                if ($user->locations->pluck('name')->contains('Skipton'))
                    $user_points[$slot][$id] += 1;
            }
        }

        return $user_points;
    }

    /**
     * @param $eligible_candidates
     * @param $location
     * @return mixed
     */
    private function mapUserSlots($eligible_candidates, $location)
    {
        return collect($eligible_candidates)->map(function ($userSlots, $datetime) use ($eligible_candidates, $location) {

            // Sort IDs from eligible candidates with most points at top then map to new array.
            $user_points = collect($userSlots)->sort()->reverse()->map(function ($value, $key) {
                return ['id' => $key, 'points' => $value];
            })->values();

            if (!filled($user_points)) return [];

            $slot = Slot::updateOrCreate(['location'=>$location->name,'datetime'=>$datetime]);

            (new UserSlot)->storeMany($userSlots);

            // Select user by index of current date within sorted $user_points array.
            $user = $user_points[$eligible_candidates->keys()->search($datetime)];

            if ($user['points']) {
                return $slot;
            }
        })->values()->filter();
    }
}