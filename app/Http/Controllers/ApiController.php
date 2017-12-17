<?php

namespace App\Http\Controllers;

use App\Token;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function run($unit, $method)
    {
        $controller = app()->make('\App\Http\Controllers\\'.ucfirst($unit).'Controller');
        $response = $controller->callAction($method, [
            'request' => request(),
        ]);
        return json_encode($response, JSON_NUMERIC_CHECK);
    }
}
