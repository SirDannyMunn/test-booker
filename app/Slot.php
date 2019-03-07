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
        $alternativeUserSlots = $this->rankUserSlots()->load('user');        
        
        foreach($alternativeUserSlots as $userSlot) {

            // Check each user availability (whether they currently have an offer open)
            if( ! $userSlot->user->offer_open && $userSlot->tries < 1)
                return $bestUser = $userSlot;
        }

        return null;
    }

    public function rankUserSlots()
    {
        return $this->userSlots->sortByDesc('points')->sortBy('tries');        
    }

    public function currentUserPlace()
    {
        return $this->rankUserSlots()->values()->search(function($item) {
            return $item->id == auth()->id();
        }) + 1;
    }

    public function currentUserSlot()
    {
        return $this->userSlots->where('user_id', auth()->id())->first();
    }

    // public function scopePromotable($query) {
    //     return $query->where('user.id', '<', 99);
    // }

    public function promotable()
    {
        foreach($this->userSlots as $userSlot) {
            if ($userSlot->points >= 99) {
                return 0;
            }
        }

        return 1;
    }
}
