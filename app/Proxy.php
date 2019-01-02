<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Proxy extends Model
{
    protected $guarded = [];

    // Update tries or deactivate based on amount of successful scrapes
    public function failed($body=true)
    {
        $properties = ['fails' => $this->fails + 1, 'last_used' => now()->toDateTimeString()];

        if ($this->completed==0 || !$body) {
            $properties = array_merge($properties, ['active' => false, 'deactivated_at' => now()]);
        }

        $this->update($properties);
    }


    public function user()
    {
        return $this->hasOne('App\User');
    }

    /**
     * @param $data
     * @param $user
     */
    public function store($data, $user)
    {
        $proxy = [
            'proxy' => $data['proxy'],
            'details' => json_encode($data),
            'user_id' => $user['id'],
            'last_used' => now()->toDateTimeString()
        ];

        return Proxy::create($proxy);
    }
}
