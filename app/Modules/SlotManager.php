<?php

namespace App\Modules;

use App\Location;
use App\Slot;
use App\UserSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlotManager
{
     /**
     * Matches users to appropriate slots based on certain factors
     * @param $slots array
     * @param $location Location
     * @return array|\Illuminate\Support\Collection
     */
    public function manageSlots($slots, $location)
    {
        $users = $location->users->sortByDesc('priority');

        /* Remove all slots past the latest users' test date */
        $latestTestDate = Carbon::parse($users->pluck('test_date')->sort()->last());
        $slots = $slots[0]->filter(function ($slot) use ($latestTestDate) {
            return $latestTestDate->greaterThan($slot);
        });

        [$slotsWithUserPoints, $needsAdditionalRanking] = $this->rankUserSlots($slots, $users, $location);

        $slotsWithUserPoints = $this->additionalRanking($slotsWithUserPoints, $needsAdditionalRanking);

        if (!$slotsWithUserPoints) {
            abort(200, 'No valid slots found');
        }

        $eligible_candidates = array_filter($slotsWithUserPoints, function ($userPoints) {
            return array_sum($userPoints)!=0;
        });

        ksort($eligible_candidates);

        $matched_slots = $this->selectAndMapUserSlots($eligible_candidates, $location);

        return $matched_slots;
    }

    public function hasDuplicate($array)
    {
        $unique = [];
        $duplicates = [];
        foreach($array as $value) {
            if (isset($unique[$value])) {
                $duplicates[] = $value;
                break;
            }

            $unique[$value] = $value;
        }
        return $duplicates && array_sum($duplicates) != 0 ?: false;
    }

    /**
     * Returns ranked list of user slots
     * @param $slots array [[string, ...]]
     * @param $users Collection [User::class, ...]
     * @param $location Location
     * @return array of ranked user against respective slots
     */
    private function rankUserSlots($slots, $users, $location)
    {
        $rankedUserSlots = [];
        $needsAdditionalRanking = [];

        foreach ($slots as $slotDate) {
            $rankedUserSlots[$slotDate] = [];
            foreach ($users as $user) {
                $id = $user->id;
                $rankedUserSlots[$slotDate][$id] = 0;
                if (Carbon::parse($slotDate)->greaterThanOrEqualTo($user->test_date))
                    continue;
                if ($user->priority)
                    $rankedUserSlots[$slotDate][$id] += 2;
                if ($user->location == $location->name)
                    $rankedUserSlots[$slotDate][$id] += 3; continue;
                /** @noinspection PhpUnreachableStatementInspection */
                if ($user->locations->pluck('name')->contains($location->name))
                    $rankedUserSlots[$slotDate][$id] += 1;
            }
            // Slot has ranked users here,
            // But all users have not seen all slots, so there could be better ones ahead.
            if ($this->hasDuplicate($rankedUserSlots[$slotDate])) {
                // 1) Should only include actual duplicates. I.e. [1,2,2] should only include [2,2]. This can then be merged
                // with original array afterwards

                // 2) If the duplicate isn't the highest, the highest will need to be incremented in anticipation of the
                // additional select incrementation. PROBABLY JUST WANT TO ONLY DO THIS EXTRA CHECK IF DUPLICATED IS
                // ALSO THE HIGHEST.

                $needsAdditionalRanking[$slotDate] = $rankedUserSlots[$slotDate];
            }
        }

        return [$rankedUserSlots,$needsAdditionalRanking];
//        return ['rankedUserSlots'=>$rankedUserSlots,'needsAdditionalRanking'=>$needsAdditionalRanking];
    }

    /*
     * Recursive. Returns all of values equal to the largest value in an array
     * */
    public function getAllOfBiggest($rankedUserSlot, $biggest)
    {
        if ($biggest==[]) asort($rankedUserSlot);

        end($rankedUserSlot);

//        $biggest[]=[key($rankedUserSlot) => $biggest_val=array_pop($rankedUserSlot)];
        $biggest[key($rankedUserSlot)]=$biggest_val=array_pop($rankedUserSlot);

        // check if others are biggest as well.
        if (end($rankedUserSlot) == $biggest_val) {
            // +1 to this as well
            return $this->getAllOfBiggest($rankedUserSlot, $biggest);
        }

        return $biggest;
    }

    public function additionalRanking($rankedUserSlots, $needsAdditionalRanking)
    {
        foreach ($needsAdditionalRanking as $date => $rankedUserSlot_duplicated) {
            $tally = [];
            foreach ($rankedUserSlots as $rankedUserSlot) {
                $biggest = $this->getAllOfBiggest($rankedUserSlot, []);
                foreach ($biggest as $user=>$point) {
                    $tally[$user]==null?$tally=1:$tally++;
                }
            }

        }

        return $rankedUserSlots;
    }

    public function popAndScore()
    {
        
    }

    /**
     * Sort the ranked slots and pick the best one for the job, while also storing other potential candidates for the slot
     * in case the best user doesn't want it.
     * @param $eligible_candidates
     * @param $location
     * @return mixed
     */
    private function selectAndMapUserSlots($eligible_candidates, $location)
    {
        return collect($eligible_candidates)->map(function ($userSlots, $datetime) use ($eligible_candidates, $location) {

            // Sort IDs from eligible candidates with most points at top then map to new array.
            $user_points = collect($userSlots)->sort()->reverse()->map(function ($value, $key) {
                return ['id' => $key, 'points' => $value];
            })->values();

            if (!filled($user_points)) return [];

            $slot = Slot::updateOrCreate(['location'=>$location->name,'datetime'=>$datetime]);

            (new UserSlot)->store($userSlots, $slot);

            // Select user by index of current date within sorted $user_points array.
//            $userIndex = collect($eligible_candidates)->keys()->search($datetime);
            $userIndex = collect($eligible_candidates)->keys()->search($datetime);

            $currentUser = $user_points[$userIndex];
            if ($currentUser['points']) {
                return $slot;
            }
        })->values()->filter();
    }
}