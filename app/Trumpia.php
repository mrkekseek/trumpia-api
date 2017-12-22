<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trumpia extends Model
{
    protected $table = 'trumpia';
    protected $guarded = [];
    protected $casts = [
        'data' => 'json',
        'response' => 'json',
        'push' => 'json',
    ];

    static public function findRequest($request_id)
    {
        return self::where('request_id', $request_id)->first();
    }
}
