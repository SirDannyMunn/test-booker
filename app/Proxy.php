<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    protected $guarded = [];

    // Update tries or deactivate based on amount of successful scrapes
    public function failed($body=true)
    {
        $properties = ['fails' => $this->fails + 1, 'last_used' => now()->toDateTimeString()];

        if ($this->completed==0 || !$body || $this->fails>2) {
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
     * @return Proxy
     */
    public function store($data)
    {
        $proxy = [
            'proxy' => $data['proxy'],
            'details' => json_encode($data),
            'last_used' => now()->toDateTimeString()
        ];

        return Proxy::create($proxy);
    }
}
