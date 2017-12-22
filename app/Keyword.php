<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $guarded = [];

    static public function findByKeyword($keyword)
    {
        return self::where('keyword', strtolower($keyword))->first();
    }
}
