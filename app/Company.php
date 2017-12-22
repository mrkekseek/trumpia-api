<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    const PENDING = 'pending';
    const DENIED = 'denied';
    const VERIFIED = 'verified';

    protected $guarded = [];

    static public function findByName($name)
    {
        return self::where('name', $name)->first();
    }
}
