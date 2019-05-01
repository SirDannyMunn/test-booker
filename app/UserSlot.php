<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSlot extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function slot()
    {
        return $this->hasOne('App\Slot', 'id', 'slot_id');
    }

    public function tried()
    {
        $this->update(['tries'=>$this->tries+1]);
    }

    public function promote()
    {
        $newScore = $this->points + 100;

        return $this->update([
            'points' => $newScore, 'tries' => 0
        ]);
    }

    public function storeMany($userSlots)
    {
        foreach ($userSlots as $user_id => $point) {
            if ($point==0) continue;

            $userSlot = UserSlot::updateOrCreate(['user_id'=>$user_id,'slot_id'=>$slot->id,'points'=>$point]);

            if ( ! $userSlot->exists()) {
                $slot->userSlots()->save($userSlot);
            }
        }
    }
}
