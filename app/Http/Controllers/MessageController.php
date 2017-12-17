<?php

namespace App\Http\Controllers;

use App\Company;
use App\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Libraries\TrumpiaValidate as TV;

class MessageController extends Controller
{
    protected $sendFrom = 8;
    protected $sendTo = 21;
    protected $resendAfter = 24;

    public function send($data = [], $landline = false)
    {
        $data = [
            'clients' => [
                [
                    'firstname' => 'John',
                    'lastname' => 'Smith',
                    'phone' => '501531717',
                ], [
                    'firstname' => 'Bill',
                    'lastname' => 'Jones',
                    'phone' => '681531717',
                ], [
                    'firstname' => 'Colin',
                    'lastname' => 'Best',
                    'phone' => '501531717',
                ],
            ],
            'message' => '[$FirstName] are you want to make money?',
            'company' => 'Div Art Company',
            'attachment' => false
        ];

        $company = Company::where('name', $data['company'])->first();
        if ( ! empty($company) && $company->status == 'verified') {
            if (count($data['clients'])) {
                if (TV::message($data['message'])) {
                    $hour = Carbon::now('America/New_York')->hour;
                    foreach ($data['clients'] as $client) {
                        $text = trim($data['message']);
                        if ( ! empty($client['firstname'])) {
                            $text = str_replace('[$FirstName]', $client['firstname'], $text);
                        }

                        if ( ! empty($client['lastname'])) {
                            $text = str_replace('[$LastName]', $client['lastname'], $text);
                        }

                        if (TV::messageLength($text, $company->name, ! empty($data['max']) ? $data['max'] : null)) {
                            if ($hour >= $this->sendFrom && $hour < $this->sendTo) {
                                
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
}
