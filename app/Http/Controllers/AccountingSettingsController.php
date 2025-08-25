<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Models\AccountingSetting;
use App\Repositories\AccountRepository;

class AccountingSettingsController extends Controller
{
    protected $AccountRepository;
    public function __construct(AccountRepository $AccountRepository)
    {
        $this->AccountRepository = $AccountRepository;
    }

    public function index()
    {
        $accounts = $this->AccountRepository->getModel()->where('slave', 0)->orWhere('has_sub', 1)->get();
        return view('AccountingSettings.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'default_customer_account' => 'nullable|exists:accounts,id',
            'default_supplier_account' => 'nullable|exists:accounts,id',
            'default_bank_account'     => 'nullable|exists:accounts,id',
            'default_cash_vault_account' => 'nullable|exists:accounts,id',
        ]);

        foreach ($validated as $key => $value) {
            AccountingSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        foreach (['default_customer_account', 'default_supplier_account', 'default_bank_account', 'default_cash_vault_account'] as $key) {
            $id = $request->input($key);
            $account = null;
            if ($id) {
                $account = $this->AccountRepository->find($id);
            }
            $nameKey = $key . '_name';
            $nameValue = $account ? $account->name . ' — ' . $account->code : '';
            AccountingSetting::updateOrCreate(
                ['key' => $nameKey],
                ['value' => $nameValue]
            );
        }

        return redirect()->back()->with('success', __('Accounts settings updated successfully'));
    }
}
