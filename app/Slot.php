<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $guarded = [];

    public function getDatetimeObjectAttribute()
    {
        return Carbon::parse($this->datetime);
    }

    public function userSlots()
    {
        return $this->hasMany('App\UserSlot', 'slot_id');
    }

    public function withinLimit()
    {
        return now()->endOfDay()
            ->lessThanOrEqualTo($this->datetime);
    }

    public function getBestUser()
    {
        $alternativeUserSlots = $this->userSlots->sortByDesc('points')->sortBy('tries')->load('user');        
        
        foreach($alternativeUserSlots as $userSlot) {

            // Check each user availability (whether they currently have an offer open)
            if( ! $userSlot->user->offer_open && $userSlot->tries < 1)
                return $bestUser = $userSlot;
        }

        return null;
    }

    public function currentUserPlace()
    {
        return $this->userSlots->sortByDesc('points')->search(function($item) {
            return $item->id == auth()->id();
        }) + 1;
    }
}
