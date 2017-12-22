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

    private function sync()
    {
        if ( ! Company::all()->count()) {
            $companies = Trumpia::allCompanies();
            if ( ! empty($companies)) {
                foreach ($companies as $company) {
                    $data = [
                        'name' => $company['name'],
                        'code' => $company['org_name_id'],
                        'status' => $company['status'],
                    ];
                    Company::create($data);
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
