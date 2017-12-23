<?php

namespace App\Http\Controllers;

use App\Company;
use App\Keyword;
use App\Http\Requests\KeywordCreateRequest;
use App\Libraries\TrumpiaLibrary as Trumpia;
use App\Libraries\TrumpiaValidate as TV;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function create(KeywordCreateRequest $request)
    {
        $data = $request->only(['teams_id', 'keyword', 'company', 'response', 'email', 'phone']);

        $keyword = Keyword::findByKeyword($data['keyword']);
        if ( ! empty($keyword)) {
            return response()->error('Keyword is already exists');
        }

        $company = Company::findByName($data['company']);
        if (empty($company)) {
            return response()->error('Company Name is not verified', 422);
        }

        if ($company->status == Company::PENDING) {
            return response()->error('Company Name is still in pending');
        }

        if ($company->status == Company::DENIED) {
            return response()->error('Company Name is denied', 406);
        }

        if ( ! TV::message($data['response'])) {
            return response()->error('Response message contains forbidden characters', 422);
        }

        if ( ! TV::messageLength($data['response'], $data['company'], 140)) {
            return response()->error('Response message is too long', 422);
        }

        $data['code'] = $company->code;
        $response = Trumpia::saveKeyword($data);
        if ($response['code'] == 200) {
            $data = [
                'teams_id' => $data['teams_id'],
                'request_id' => $response['data']['request_id'],
                'token_id' => config('token.id'),
                'keyword' => strtolower($data['keyword']),
            ];

            Keyword::create($data);
        }

        return response()->success($response);
    }
}
