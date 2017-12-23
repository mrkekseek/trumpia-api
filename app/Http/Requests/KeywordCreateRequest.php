<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiRequest;

class KeywordCreateRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'keyword' => 'required|alpha|min:4|max:50',
            'company' => 'required',
            'teams_id' => 'required',
        ];
    }
}
