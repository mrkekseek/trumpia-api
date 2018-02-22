<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Message;
use DB;

class ReportsController extends Controller
{
    public function get(Request $request)
    {
        DB::enableQueryLog();
        $response = Message::whereDate('created_at', Carbon::parse($request->date)->toDateString());
        if ( ! empty($request->type)) {
            $response->where('type', $request->type);
        }

        $ids = $request->ids;
        $response->where(function($query) use ($ids) {
            if ( ! empty($ids['dialog'])) {
                $dialog_ids = $ids['dialog'];
                $query->orWhere(function($q) use ($dialog_ids) {
                    $q->where('type', 'dialog')->whereIn('target_id', $dialog_ids);
                });
            }

            if ( ! empty($ids['review'])) {
                $review_ids = $ids['review'];
                $query->orWhere(function($q) use ($review_ids) {
                    $q->where('type', 'review')->whereIn('target_id', $review_ids);
                });
            }

            if ( ! empty($ids['alert'])) {
                $alert_ids = $ids['alert'];
                $query->orWhere(function($q) use ($alert_ids) {
                    $q->where('type', 'alert')->whereIn('target_id', $alert_ids);
                });
            }
        });

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