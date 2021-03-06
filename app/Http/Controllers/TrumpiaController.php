<?php

namespace App\Http\Controllers;

use Parser;
use App\Trumpia;
use App\Token;
use App\Receiver;
use App\Message;
use Illuminate\Http\Request;
use App\Libraries\ResponseLibrary;

class TrumpiaController extends Controller
{
    public function push(Request $request)
    {
        $data = $request->json()->all();
        $request = $this->save($data);
        if ( ! empty($request)) {
            $this->getToken($request->token_id);

            list($controller, $method) = explode('/', $request->type);
            $controller = app()->make('\App\Http\Controllers\\'.ucfirst($controller).'Controller');
            if (method_exists($controller, $method.'Push')) {
                $controller->callAction($method.'Push', [
                    'data' => $data,
                ]);
            }
        }
    }

    public function save($data)
    {
        $request = Trumpia::findRequest($data['request_id']);
        if ( ! empty($request)) {
            $request->update(['push' => $data]);
        }
        return $request;
    }

    private function getToken($token_id)
    {
        $token = Token::find($token_id);
        if ( ! empty($token)) {
            config(['token.id' => $token->id]);
            config(['token.token' => $token->token]);
            config(['token.project' => $token->project]);
            config(['token.domain' => $token->domain]);
            config(['token.secure' => $token->secure]);
        }
    }

    public function inbox()
    {
        $xml = $_GET['xml'];
        $this->saveLog($xml, 'INBOX');
        $xml = Parser::xml($xml);
        $receiver = Receiver::where('phone', $xml['PHONENUMBER'])->orderBy('sent_at', 'desc')->first();
        if ( ! empty($receiver)) {
            $request = Trumpia::findRequest($receiver->request_id);
            if ( ! empty($request)) {
                $this->getToken($request->token_id);
                $message = Message::find($receiver->message_id);
                ResponseLibrary::send('inbox/'.$message->type.'/'.$message->target_id, $xml);
            }
        }
    }

    public function saveLog($data, $source)
    {
        if ( ! file_exists('logs')) {
            mkdir('logs', 0777);
        }
        file_put_contents('logs/logger.txt', date('[Y-m-d H:i:s] ').$source.': '.print_r($data, true).PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
