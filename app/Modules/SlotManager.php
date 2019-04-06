<?php

namespace App\Modules;

use App\Slot;
use App\UserSlot;
use Carbon\Carbon;

class SlotManager
{
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

        $slots = $this->removeSlotsAfter(
            $slots,
            Carbon::parse($users->pluck('test_date')->sort()->last())
        );

        $user_points = $this->rankUsers($slots, $users, $location);

        if (!$user_points) {
            $this->window->quit();
            abort(500, 'No valid slots found');
        }

        $eligible_candidates = $this->sliceEligibleCandidates($user_points);

        $matched_slots = $this->mapUserSlots($eligible_candidates, $location);

        return $matched_slots;
    }

    /**
     * @param $user_points
     * @return \Illuminate\Support\Collection
     */
    private function sliceEligibleCandidates($user_points)
    {
        $eligible_candidates = array_where(array_first($user_points), function ($value) {
            return $value != 0;
        });

        return collect(array_slice($user_points, 0, count($eligible_candidates)));
    }

    /**
     * Slices array when slot dates pass latest user's slot date (i.e. become useless)
     * @param $slots
     * @param $latest_test_date Carbon
     * @return array
     */
    private function removeSlotsAfter($slots, $latest_test_date)
    {
        // Loop though slots until get to the point then break and slice array with index
        foreach ($slots as $index => $slot) {
            if ($latest_test_date->lessThanOrEqualTo($slot)) {
                return array_slice($slots, 0, $index);
            }
        }

        return $slots;
    }

    private function rankUsers($slots, $users, $location)
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
                if ($user->locations->pluck('name')->contains('Skipton'))
                    $user_points[$slot][$id] += 1;
            }
        }

        return $user_points;
    }

    private function mapUserSlots($eligible_candidates, $location)
    {
        return collect($eligible_candidates)->map(function ($userPoints, $datetime) use ($eligible_candidates, $location) {

            // Sort IDs from eligible candidates with most points at top then map to new array.
            $user_points = collect($userPoints)->sort()->reverse()->map(function ($value, $key) {
                return ['id' => $key, 'points' => $value];
            })->values();

            if (!filled($user_points)) return [];

            // Select user by index of current date within sorted $user_points array.
            $user = $user_points[$eligible_candidates->keys()->search($datetime)];

            $slot = Slot::updateOrCreate(['location'=>$location->name,'datetime'=>$datetime]);

            // Make UserSlots (maybe abstract to userSlot model ? )
            foreach ($userPoints as $user_id => $point) {
                if ($point==0) continue;

                $userSlot = UserSlot::updateOrCreate(['user_id'=>$user_id,'slot_id'=>$slot->id,'points'=>$point]);

                if ( ! $userSlot->exists()) {
                    $slot->userSlots()->save($userSlot);
                }
            }

            if ($user['points']) {
                return $slot;
            }
        })->values()->filter();
    }
}