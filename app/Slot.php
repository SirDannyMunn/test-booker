<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $guarded = [];

    public function userSlots()
    {
        return $this->hasMany('App\UserSlot', 'slot_id');
    }

    public function withinLimit()
    {
        return now()->endOfDay()
            ->lessThanOrEqualTo($this->datetime);
    }
}
