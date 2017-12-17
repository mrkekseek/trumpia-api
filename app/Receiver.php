<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Receiver extends Model
{
    protected $guarded = [];
    protected $dates = ['sent_at', 'created_at', 'updated_at'];

    static public function findByRequest($request_id)
    {
        return self::where('request_id', $request_id)->first();
    }

    static public function allFinished($message_id)
    {
        return self::where('message_id', $message_id)->where('finish', true)->get();
    }

    static public function wasSent($phone, $hours)
    {
        return self::where('phone', $phone)->where('sent_at', '>', Carbon::now()->addHours($hours * (-1)))->count() > 0;
    }
}
