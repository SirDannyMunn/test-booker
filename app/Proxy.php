<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    protected $guarded = [];

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
            'ip' => $data['ip'],
            'port' => $data['port'],
            'details' => json_encode($data),
            'user_id' => $user['id'],
            'last_used' => now()->toDateTimeString()
        ];

        Proxy::create($proxy);
    }
}
