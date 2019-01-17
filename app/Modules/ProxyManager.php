<?php
/**
 * Created by PhpStorm.
 * User: danie
 * Date: 15/12/2018
 * Time: 10:15
 */

namespace App\Modules;

use App\Proxy;
use App\User;
use GuzzleHttp\Client;

class ProxyManager
{
    private $proxies;
    private $timeLimit;

    public function __construct()
    {
        $this->proxies = Proxy::where('active', true);

        $this->timeLimit = now()->subMinutes(rand(8, 10))->toDateTimeString();
    }

    /**
     * @param bool $clean
     * @return mixed
     */
    public function getProxy($clean=false)
    {
        if ($clean) {
            return $this->proxies->where('completed', '!=', 0)->orderBy('last_used')->get()->first();
        }

        $activeProxy = $this->proxies->where('last_used', '<', $this->timeLimit)->get();

        if (filled($activeProxy)) {
            return $activeProxy->random();
        }

        return $this->newProxy();
    }

    public function newProxy()
    {
        $guzzle = new Client();

        $body = [
            'query' => [
                'apiKey' => 'Rr6QBHMTmVwpzfGJt3nYhvgqcdAb75KP',
                "connectionType" => "Residential",
                "referer" => false,
//                "country" => "GB"
            ]
        ];

        $response = $guzzle->get('http://falcon.proxyrotator.com:51337', $body);

        // Save & return proxy instance
        return (new Proxy)->store(
            json_decode(
                $response->getBody(), true
            )
        );
    }
}