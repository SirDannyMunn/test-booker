<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $guarded = [];

    public function userSlots()
    {
        return $this->hasMany('App\UserSlot', 'slot_id');
    }
}
