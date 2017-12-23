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
use App\Libraries\ResponseLibrary;

class MessageController extends Controller
{
    protected $sendFrom = 9;
    protected $sendTo = 21;
    protected $blockHours = 24;

    public function send(MessageSendRequest $request, $landline = false)
    {
        $data = $request->only(['type', 'target_id', 'clients', 'message', 'company', 'attachment', 'max', 'block']);

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
        if ( ! empty($data['block']) && ($hour < $this->sendFrom || $hour >= $this->sendTo)) {
            return response()->error('Message sending is forbidden till '.$this->sendFrom.' AM', 409);
        }

        $attachment = ! empty($data['attachment']) ? $data['attachment'] : false;
        $message = $this->create($data, $attachment);

        $phones = [];
        foreach ($data['clients'] as $client) {
            $phones[$client['phone']] = [
                'message' => '',
                'finish' => 0,
                'success' => 0,
            ];

            $text = trim($data['message']);
            if ( ! empty($client['firstname'])) {
                $text = str_replace('[$FirstName]', $client['firstname'], $text);
            }

            if ( ! empty($client['lastname'])) {
                $text = str_replace('[$LastName]', $client['lastname'], $text);
            }

            if ( ! empty($client['link'])) {
                $text = str_replace('[$Link]', $client['link'], $text);
            }

            $request_id = '';
            if (TV::phone($client['phone'])) {
                if (TV::messageLength($text, $data['company'], ! empty($data['max']) ? $data['max'] : null)) {
                    if ($this->blockPhone($client['phone'])) {
                        $phones[$client['phone']]['message'] = __('For the last '.$this->blockHours.' hours this phone number already received a text'); 
                        $phones[$client['phone']]['finish'] = 1;
                    } else {
                        $response = Trumpia::sendText($client['phone'], $company->code, ' '.$text, $attachment);
                        $request_id = $response['data']['request_id'];
                        if ($response['code'] != 200) {
                            $phones[$client['phone']]['message'] = $response['message'];
                            $phones[$client['phone']]['finish'] = 1;
                        }
                    }
                } else {
                    $phones[$client['phone']]['message'] = __('Message is too long');
                    $phones[$client['phone']]['finish'] = 1;
                }
            } else {
                $phones[$client['phone']]['message'] = __('Phone Number is incorrect');
                $phones[$client['phone']]['finish'] = 1;
            }

            $this->receiver($message->id, $company->code, $client, $text, $attachment, $phones[$client['phone']]['message'], $request_id);
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
            'target_id' => $data['target_id'],
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
            'firstname' => ! empty($data['firstname']) ? $data['firstname'] : '',
            'lastname' => ! empty($data['lastname']) ? $data['lastname'] : '',
            'link' => ! empty($data['link']) ? $data['link'] : '',
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
                $response = Trumpia::sendText($receiver->phone, $receiver->company, $this->landlineText($receiver->text, $receiver->company), $receiver->attachment, true);
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

    private function landlineText($text, $code)
    {
        $company = Company::whrere('code', $code)->first();
        return $company->name.': '.$text;
    }

    private function response($message_id)
    {
        $message = Message::find($message_id);
        $receivers = Receiver::allFinished($message_id);
        if (count($receivers) == $message->phones) {
            ResponseLibrary::send($message->type.'/push/'.$message->target_id, $receivers);
        }
    }
}
