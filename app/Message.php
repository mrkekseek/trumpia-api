<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded = [];

    public function receivers()
    {
        return $this->hasMany('App\Receiver');
    }
}
