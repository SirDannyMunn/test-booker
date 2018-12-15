<?php
/**
 * Created by PhpStorm.
 * User: danie
 * Date: 15/12/2018
 * Time: 14:35
 */

namespace App\Tasks;


class SlotManager
{
    /**
     * Matches users to appropriate slots based on certain factors
     * @param $slots
     * @param $location
     * @return \Illuminate\Support\Collection
     */
    public function getMatches($slots, $location)
    {
        $users = $location->users->sortByDesc('priority');
        $slots = $this->removeSlotsAfter($slots,
            Carbon::parse($users->pluck('test_date')->sort()->last())
        );

        $user_points = [];
        foreach ($slots[0] as $slot) {
            $user_points[$slot] = [];
            foreach ($users as $user) {
                $id = $user->id;
                $user_points[$slot][$id] = 0;
                if (Carbon::parse($slot)->greaterThan($user->test_date))
                    continue;
                if ($user->location == $location->name)
                    $user_points[$slot][$id] += 2;
                if ($user->priority)
                    $user_points[$slot][$id] += 1;
            }
        }

        if (!$user_points) {
            $this->window->quit();
            abort(500, 'No valid slots found');
        }

        $eligible_candidates = $this->sliceEligibleCandidates($user_points);

        $matched_slots = $eligible_candidates->map(function ($ids, $date) use ($eligible_candidates, $location) {

            // Sort IDs from eligible candidates with most points at top then map to new array.
            $user_points = collect($ids)->filter()->sort()->reverse()->map(function ($value, $key) {
                return ['id' => $key, 'points' => $value];
            })->values();

            // Select user by index of current date within sorted $user_points array.
            $user = $user_points[$eligible_candidates->keys()->search($date)];

            // Final user w' slot array.
            return ['user' => $user,
                'date' => $date,
                'location' => $location->name];
        })->values();

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
}