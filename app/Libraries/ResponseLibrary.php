<?php

namespace App\Libraries;

use \GuzzleHttp\Client as Guzzle;

class ResponseLibrary
{
    static public function send($uri, $data)
    {
        $client = new Guzzle(['base_uri' => self::baseUri()]);
        $response = $client->request('POST', $uri, [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Apikey' => config('token.token'),
            ],
            'http_errors' => false,
            'json' => $data,
        ]);
        
        //print_r($response->getBody()->getContents());
        //$json = json_decode($response->getBody(), true);
    }

    static private function baseUri()
    {
        return 'http'.(config('token.secure') ? 's' : '').'://'.config('token.domain').'/';
    }
}
