<?php

namespace App\Libraries;

use App\Trumpia;
use \GuzzleHttp\Client as Guzzle;

class TrumpiaFake
{
    static public function request($uri, $type, $data = [], $method = 'put')
    {
        $result = [];
        
        switch ($type) {
            case 'company/all':
                $result = self::allCompanies();
                break;
            case 'company/save':
                $result = self::saveCompany();
                break;
        }

        return $result;
    }

    static public function allCompanies()
    {
        return [
            [
                "org_name_id" => 1,
                "name" => "First Testovich Company",
                "default" => true,
                "status" => "verified"
            ], [
                "org_name_id" => 2,
                "name" => "Second Testovich Company",
                "default" => false,
                "status" => "denied"
            ], [
                "org_name_id" => 3,
                "name" => "Third Testovich Company",
                "default" => false,
                "status" => "pending"
            ], [
                "org_name_id" => 4,
                "name" => "Fourth Testovich Company",
                "default" => false,
                "status" => "denied"
            ], [
                "org_name_id" => 5,
                "name" => "Fifth Testovich Company",
                "default" => false,
                "status" => "verified"
            ], [
                "org_name_id" => 6,
                "name" => "Sixth Testovich Company",
                "default" => false,
                "status" => "denied"
            ], [
                "org_name_id" => 7,
                "name" => "Seventh Testovich Company",
                "default" => false,
                "status" => "verified"
            ], [
                "org_name_id" => 8,
                "name" => "Eighth Testovich Company",
                "default" => false,
                "status" => "verified"
            ], [
                "org_name_id" => 9,
                "name" => "Nineth Testovich Company",
                "default" => false,
                "status" => "verified"
            ], [
                "org_name_id" => 10,
                "name" => "Tenth Testovich Company",
                "default" => false,
                "status" => "denied"
            ]
        ];
    }

    static public function saveCompany()
    {
        return [
            "request_id" => "1234561234567asdf123",
            "org_name_id" => 11,
            "status_code" => "MPCE4001"
        ];
    }
}
