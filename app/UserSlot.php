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
}
