<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;
use App\Models\Account;

class BankSeeder extends Seeder
{
    public function run()
    {
        $banks = [
            [
                'id' => 14,
                'name' => 'بنك اسكندريه',
                'branch' => null,
                'account_number' => '01020101',
                'currency_id' => 1,
                'balance' => 0,
                'status' => 1,
                'swift_code' => null,
            ],
            [
                'id' => 15,
                'name' => 'بنكQNB',
                'branch' => null,
                'account_number' => '01020102',
                'currency_id' => 1,
                'balance' => 0,
                'status' => 1,
                'swift_code' => null,
            ],
            [
                'id' => 268,
                'name' => 'بنك البركة',
                'branch' => null,
                'account_number' => '01020103',
                'currency_id' => 1,
                'balance' => 0,
                'status' => 1,
                'swift_code' => null,
            ],
            [
                'id' => 339,
                'name' => 'بنك اسكندرية عملة (فرنك)',
                'branch' => null,
                'account_number' => '01020104',
                'currency_id' => 1,
                'balance' => 0,
                'status' => 1,
                'swift_code' => null,
            ],
        ];

        foreach ($banks as $bankData) {

            // إنشاء البنك وربطه بالحساب
            Bank::create([
                'name'           => $bankData['name'],
                'branch'         => $bankData['branch'],
                'account_number' => $bankData['account_number'],
                'currency_id'    => $bankData['currency_id'],
                'account_id'     => $bankData['id'],
                'balance'        => $bankData['balance'],
                'status'         => $bankData['status'],
                'swift_code'     => $bankData['swift_code'],
            ]);
        }
    }
}
