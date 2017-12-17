<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trumpia extends Model
{
    protected $table = 'trumpia';
    protected $casts = [
        'data' => 'json',
        'response' => 'json',
        'push' => 'json',
    ];
}
