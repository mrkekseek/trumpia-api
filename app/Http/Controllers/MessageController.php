<?php

namespace App\Http\Controllers;

use App\Company;
use App\Message;
use App\Receiver;
use Carbon\Carbon;
use App\Http\Requests\MessageSendRequest;
use Illuminate\Http\Request;
use App\Libraries\TrumpiaLibrary as Trumpia;
use App\Libraries\TrumpiaValidate as TV;

class MessageController extends Controller
{
    protected $sendFrom = 9;
    protected $sendTo = 21;
    protected $blockHours = 24;

    public function send(MessageSendRequest $request, $landline = false)
    {
        $data = $request->only(['type', 'clients', 'message', 'company', 'attachment', 'max', 'block']);

        $company = Company::findByName($data['company']);
        if (empty($company)) {
            return response()->error('Company Name is not verified', 422);
        }

        if ($company->status == Company::PENDING) {
            return response()->error('Company Name is still in pending');
        }

        if ($company->status == Company::DENIED) {
            return response()->error('Company Name is denied', 406);
        }

        if ( ! TV::message($data['message'])) {
            return response()->error('Message contains forbidden characters', 422);
        }

        $hour = Carbon::now()->hour;
        if ( ! empty($data['block']) && ($hour <= $this->sendFrom || $hour > $this->sendTo)) {
            return response()->error('Message sending is forbidden till '.$this->sendFrom.' AM', 409);
        }

        $attachment = ! empty($data['attachment']) ? $data['attachment'] : false;
        $message = $this->create($data, $attachment);

        $phones = [];
        foreach ($data['clients'] as $client) {
            $phones[$client['phone']] = __('Ok');
            $text = trim($data['message']);
            if ( ! empty($client['firstname'])) {
                $text = str_replace('[$FirstName]', $client['firstname'], $text);
            }

            if ( ! empty($client['lastname'])) {
                $text = str_replace('[$LastName]', $client['lastname'], $text);
            }

            $request_id = '';
            if (TV::phone($client['phone'])) {
                if (TV::messageLength($text, $data['company'], ! empty($data['max']) ? $data['max'] : null)) {
                    $response = Trumpia::sendText($client['phone'], $company->code, $text, $attachment);
                    $request_id = $response['data']['request_id'];
                    if ($response['code'] != 200) {
                        $phones[$client['phone']] = $response['message'];
                    } else {
                        if ($this->blockPhone($client['phone'])) {
                           $phones[$client['phone']] = __('For the last '.$this->blockHours.' hours this phone number already received a text'); 
                        }
                    }
                } else {
                    $phones[$client['phone']] = __('Message is too long');
                }
            } else {
                $phones[$client['phone']] = __('Phone Number is incorrect');
            }

            $this->receiver($message->id, $company->code, $client, $text, $attachment, $phones[$client['phone']], $request_id);
        }

        return response()->success($phones);
    }

    private function blockPhone($phone)
    {
        return Receiver::wasSent($phone, $this->blockHours);
    }

    private function create($data, $attachment)
    {
        $message = [
            'phones' => count($data['clients']),
            'type' => $data['type'],
            'text' => $data['message'],
            'company' => $data['company'],
            'attachment' => $attachment,
        ];

        return Message::create($message);
    }

    private function receiver($message_id, $company, $data, $text, $attachment, $message, $request_id)
    {
        $receiver = [
            'message_id' => $message_id,
            'request_id' => $request_id,
            'phone' => $data['phone'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'text' => $text,
            'company' => $company,
            'attachment' => $attachment,
            'finish' => empty($request_id),
            'message' => $message,
            'sent_at' => Carbon::now(),
        ];

        return Receiver::create($receiver);
    }

    public function sendPush($data = [])
    {
        $receiver = Receiver::findByRequest($data['request_id']);
        $update = [];

        if ( ! empty($data['sms']['sent'])) {
            $update = [
                'finish' => true,
                'success' => true,
            ];
        } else {
            if (empty($receiver->landline)) {
                $response = Trumpia::sendText($receiver->phone, $receiver->company, $receiver->text, $receiver->attachment, true);
                $request_id = $response['data']['request_id'];

                $update = [
                    'landline' => true,
                    'request_id' => $request_id,
                    'sent_at' => Carbon::now(),
                ];
            } else {
                $update = [
                    'finish' => true,
                ];

                if ( ! empty($data['status_code'])) {
                    $update['message'] = Trumpia::message($data['status_code']);
                }

                if ( ! empty($data['delivery_report']['sms'][0]['dr_code'])) {
                    $update['message'] = Trumpia::report($data['delivery_report']['sms'][0]['dr_code']);
                }
            }
        }

        $receiver->update($update);
        $this->response($receiver->message_id);
    }

    private function response($message_id)
    {
        $message = Message::find($message_id);
        $receivers = Receiver::allFinished($message_id);
        if (count($receivers) == $message->phones) {
            dd("Send back");
        }
    }
}
