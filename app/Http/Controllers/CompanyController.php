<?php

namespace App\Http\Controllers;

use App\Company;
use App\Libraries\TrumpiaLibrary as Trumpia;
use Illuminate\Http\Request;
use App\Http\Requests\CompanyNameRequest;

class CompanyController extends Controller
{
    public function name(CompanyNameRequest $request)
    {
        $this->sync();

        $company = Company::where('name', $request->name)->first();
        if ( ! empty($company)) {
            return $company->status;
        }

        $response = Trumpia::saveCompany($request->name);
        if (empty($response['error'])) {
            $company = new Company();
            $company->name = $request->name;
            $company->code = $response['org_name_id'];
            $company->status = 'pending';
            $company->save();

            return $company->status;
        }

        return $response;
    }

    public function remove($data = [])
    {
        $data['name'] = 'alt alt team';
        $company = Company::where('name', $data['name'])->first();
        if ( ! empty($company)) {
            Trumpia::removeCompany($company->code);
            $company->delete();
        }

        return true;
    }

    public function sync()
    {
        $count = Company::all()->count();
        if (empty($count)) {
            $companies = Trumpia::allCompanies();
            if ($companies) {
                foreach ($companies as $company_data) {
                    $company = new Company();
                    $company->name = $company_data['name'];
                    $company->code = $company_data['org_name_id'];
                    $company->status = $company_data['status'];
                    $company->save();
                }
            }
        }
    }
}
