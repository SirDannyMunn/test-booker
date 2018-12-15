<?php
/**
 * Created by PhpStorm.
 * User: danie
 * Date: 15/12/2018
 * Time: 10:15
 */

namespace App\Tasks;

use GuzzleHttp\Client;

class ProxyManager
{
    public function getProxy($user)
    {
//        if ($user->proxy) {
//            return
//        }

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

        return json_decode(
            $response->getBody(), true
        );
    }
}