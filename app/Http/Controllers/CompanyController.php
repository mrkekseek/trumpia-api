<?php

namespace App\Http\Controllers;

use App\Company;
use App\Libraries\TrumpiaLibrary as Trumpia;
use App\Libraries\ResponseLibrary;
use Illuminate\Http\Request;
use App\Http\Requests\CompanyNameRequest;

class CompanyController extends Controller
{
    public function name(CompanyNameRequest $request)
    {
        $this->sync();

        $name = $request->name;
        $company = Company::findByName($name);
        if ( ! empty($company)) {
            return response()->success($company->status);
        }

        $response = Trumpia::saveCompany($name);
        if ($response['code'] == 200) {
            $data = [
                'name' => $name,
                'code' => $response['data']['org_name_id'],
                'status' => Company::PENDING,
            ];

            $company = Company::create($data);
            return response()->success($company->status);
        }

        return response()->success($response['data'], $response['message'], $response['code']);
    }

    public function remove($name)
    {
        $company = Company::findByName($name);
        if ( ! empty($company)) {
            Trumpia::removeCompany($company->code);
            $company->delete();
        }

        return response()->success(true, 'Company Name was successfully removed');
    }

    public function sync()
    {
        $count = Company::all()->count();
        if (empty($count)) {
            $companies = Trumpia::allCompanies();
            if ( ! empty($companies)) {
                foreach ($companies as $company_data) {
                    $data = [
                        'name' => $company_data['name'],
                        'code' => $company_data['org_name_id'],
                        'status' => $company_data['status'],
                    ];
                    $company = Company::create($data);
                }
            }
        }
    }

    public function savePush($data = [])
    {
        $company = Company::findByName($data['name']);
        $company->update(['status' => $data['status']]);
        ResponseLibrary::send('company/push', $data);
    }
}
