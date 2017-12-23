<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TokensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tokens')->truncate();

        DB::table('tokens')->insert([
            'token' => '$2y$10$hBYMT3gYolARvwHUf4q4NuTkwcNLpwZHR3VF4nF3esfjcqIg7rHfO',
            'project' => 'ContractorTexter',
            'domain' => 'app.contractortexter.com',
            'secure' => 0,
        ]);

        DB::table('tokens')->insert([
            'token' => '$2y$10$IAaNcSvQvFWtUrphyN31IO8qMnn1AXRiaifzkMppusf3Wr17BYAMS',
            'project' => 'ContractorTexter',
            'domain' => 'app.contractortexter.da',
            'secure' => 0,
        ]);

        DB::table('tokens')->insert([
            'token' => '$2y$10$8vzTvn93JapzciD9ZT1CdOr/4KlRGuwksaHkAGR9plzgYDV3BiJHK',
            'project' => 'ContractorTexter',
            'domain' => 'ct.da',
            'secure' => 0,
        ]);

        DB::table('tokens')->insert([
            'token' => '$2y$10$hBYMT3gYolARvwHUf4q4NuTkwcNLpwZHR3VF4nF3esfjcqIg7rHf1',
            'project' => 'ContractorTexter',
            'domain' => '34.214.246.59',
            'secure' => 0,
        ]);
    }
}
