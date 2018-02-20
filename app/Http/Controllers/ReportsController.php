<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Message;

class ReportsController extends Controller
{
    public function get(Request $request)
    {
        $response = Message::whereDate('created_at', Carbon::parse($request->date)->toDateString());
        if ( ! empty($request->type)) {
            $response->where('type', $request->type);
        }

        if ( ! empty($request->phone)) {
            $phone = $request->phone;
            $response->whereHas('receivers', function($query) use ($phone) {
                $query->where('phone', $phone);
            });
        }

        $data = $response->with(['receivers', 'receivers.trumpia'])->get();
        return response()->success($data);
    }
}