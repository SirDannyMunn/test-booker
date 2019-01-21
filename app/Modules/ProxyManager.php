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

        $this->timeLimit = now()->subMinutes(env('PROXY_WAIT_PERIOD'))->toDateTimeString();
    }

    /**
     * @param bool $clean
     * @param bool $hunting
     * @return mixed
     */
    public function getProxy($clean=false, $hunting=false)
    {
        if ($clean) {
            return $this->proxies->where('completed', '!=', 0)->orderBy('last_used')->get()->first();
        }

        $activeProxy = $this->proxies->where('last_used', '<', $this->timeLimit)->orderBy('last_used')->get();

        if (filled($activeProxy) && !$hunting) {
            return $activeProxy->last();
        }

        return $this->newProxy();
    }

    public function newProxy()
    {
        $guzzle = new Client();

        $body = [
            'query' => [
//                'Rr6QBHMTmVwpzfGJt3nYhvgqcdAb75KP'
                'apiKey' => 'CJNRT6zVxcBD8fQYbj95tSGKL2MevXEW',
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