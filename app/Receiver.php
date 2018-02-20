<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Receiver extends Model
{
    protected $guarded = [];
    protected $dates = ['sent_at', 'created_at', 'updated_at'];

    public function trumpia()
    {
        return $this->hasOne('App\Trumpia', 'request_id', 'request_id');
    }

    static public function findByRequest($request_id)
    {
        return self::where('request_id', $request_id)->first();
    }

    static public function allFinished($message_id)
    {
        return self::where('message_id', $message_id)->where('finish', true)->where('parent_id', 0)->get();
    }

    static public function wasSent($id, $phone, $hours)
    {
        return self::where('id', '<>', $id)->where('phone', $phone)->where('request_id', '!=', '')->where('sent_at', '>', Carbon::now()->subHours($hours))->count() > 0;
    }
}
