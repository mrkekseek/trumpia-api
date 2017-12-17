<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Receiver extends Model
{
    protected $guarded = [];

    static public function findByRequest($request_id)
    {
        return self::where('request_id', $request_id)->first();
    }

    static public function allFinished($message_id)
    {
        return self::where('message_id', $message_id)->where('finish', true)->get();
    }
}
