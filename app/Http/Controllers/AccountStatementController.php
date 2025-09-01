<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Services\AccountStatementService;
use Illuminate\Http\Request;

class AccountStatementController extends Controller
{
    protected $statementService;

    public function __construct(AccountStatementService $statementService)
    {
        $this->statementService = $statementService;
    }

    public function show(Request $request)
    {
        $accounts = Account::where('slave', 1)->where('has_sub', 0)->get();
        return view('accounts.statement', [
            'accounts' => $accounts,
            'startDate' => $request->input('start_date', now()->startOfYear()->toDateString()),
            'endDate' => $request->input('end_date', now()->toDateString()),
        ]);
    }

    public function getStatement(Request $request)
    {
        $accountId = $request->input('account_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $account = Account::find($accountId);

        if (!$account) {
            return response()->json(['error' => __('Account not found.')], 404);
        }

        $statementData = $this->statementService->getStatement(
            $account,
            $startDate,
            $endDate
        );

        // Add account to statementData for the view
        $statementData['account'] = $account;

        $detailsHtml = view('accounts.partials.statement_details', $statementData)->render();


        return response()->json([
            'details' => $detailsHtml,
        ]);
    }
}
