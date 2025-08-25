<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Services\AccountService;
use App\Repositories\AccountRepository;
use App\Services\TreeCodeGeneratorService;

class AccountController extends Controller
{
    protected $accountService;
    protected $repository;
    protected $treeCodeGeneratorService;

    public function __construct(AccountService $accountService, TreeCodeGeneratorService $treeCodeGeneratorService, AccountRepository $repository)
    {
        $this->accountService = $accountService;
        $this->repository = $repository;
        $this->treeCodeGeneratorService = $treeCodeGeneratorService;
    }

    public function index()
    {
        $accounts = $this->accountService->getAccountTree();

        $jsTreeData = $this->buildJsTreeFlat($accounts);

        $allAccounts = $this->repository->getModel()
            ->where('slave', 0)
            ->orWhere('has_sub', 1)
            ->get();

        return view('accounts.index', [
            'accounts' => json_encode($jsTreeData, JSON_UNESCAPED_UNICODE),
            'allAccounts' => $allAccounts
        ]);
    }

    public function treeData()
    {
        $accounts = $this->accountService->getAccountTree();
        $jsTreeData = $this->buildJsTreeFlat($accounts);

        return response()->json($jsTreeData);
    }

    private function buildJsTreeFlat($accounts)
    {
        $flat = [];
        foreach ($accounts as $account) {
            if ($account->has_sub == 1) {
                $icon = 'icon-base ti tabler-file-spark';
                $class = 'jstree-red';
                $type  = 'sub_account';
            } elseif ($account->slave == 1) {
                $icon = 'icon-base ti tabler-file';
                $class = 'jstree-blue';
                $type  = 'account';
            } else {
                $icon = 'icon-base ti tabler-folder';
                $class = '';
                $type  = 'title';
            }

            $flat[] = [
                'id'     => $account->id,
                'parent' => $account->ownerEl ? $account->ownerEl : '#',
                'text'   => $account->name,
                'icon'   => $icon,
                'type'   => $type,
                'code'   => $account->code,
                'li_attr' => [
                    'class' => $class
                ]
            ];
        }

        return $flat;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'parent_id' => 'nullable|exists:accounts,id',
            'type'      => 'required|in:account,sub_account,title',
        ]);

        if (isset($data['parent_id'])) {
            $data['ownerEl'] = $data['parent_id'];
        }
        if ($data['type'] === 'account') {
            $data['slave'] = 1;
            $data['has_sub'] = 0;
        } elseif ($data['type'] === 'sub_account') {
            $data['slave'] = 0;
            $data['has_sub'] = 1;
        } else {
            $data['slave'] = 0;
            $data['has_sub'] = 0;
        }

        try {
            $account = $this->accountService->createAccount($data);

            // أبني نود بصيغة jsTree لواجهة الجافاسكربت
            $node = [
                'id' => $account->id,
                'parent' => $account->ownerEl ? $account->ownerEl : '#',
                'text' => $account->name,
                'icon' => ($account->has_sub == 1) ? 'icon-base ti tabler-file-isr' : (($account->slave == 1) ? 'icon-base ti tabler-file' : 'icon-base ti tabler-folder'),
                'li_attr' => ['class' => ($account->has_sub == 1) ? 'jstree-red' : (($account->slave == 1) ? 'jstree-blue' : '')],
                'type' => ($account->has_sub == 1) ? 'sub_account' : (($account->slave == 1) ? 'account' : 'title'),
                'code' => $account->code ?? null,
            ];

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => __('general.Account_creation_successful'), 'node' => $node, 'data' => $account]);
            }

            return redirect()->route('accounts.index')->with('success', __('general.Account_created_successfully'));
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return redirect()->route('accounts.index')->with('error', $e->getMessage());
        }
    }

    public function edit(Account $account)
    {
        // هل له فروع؟
        $hasChildren = $account->children()->exists();

        // هل له قيود يومية؟
        $hasJournals = $account->journalEntryDetails()->exists();



        $parent_details_html = '';
        if ($account->ownerEl) {
            $parent = $this->repository->find($account->ownerEl);
            if ($parent) {
                $parent_details_html = '<b>' . __('general.Account_name') . ':</b> ' . e($parent->name) . '<br>'
                    . '<b>' . __('general.Account_code') . ':</b> ' . e($parent->code) . '<br>'
                    . '<b>' . __('general.Account_type') . ':</b> ' . ($parent->has_sub ? __('general.title') : ($parent->slave ? __('general.sub_account') : __('general.account')));
            }
        }

        if ($account->slave === true && $account->has_sub == false) {
            $type = 'account';
            $isTitle = false;
            $isAccount = true;
        } elseif ($account->slave === false && $account->has_sub == true) {
            $type = 'sub_account';
            $isTitle = false;
            $isAccount = true;
        } else {
            $type = 'title';
            $isTitle = true;
            $isAccount = false;
        }

        return response()->json([
            'success' => true,
            'account' => $account,
            'has_children' => $hasChildren,
            'has_journals' => $hasJournals,
            'is_sub_account' => $isAccount,
            'is_title' => $isTitle,
            'type' => $type,
            'parent_details_html' => $parent_details_html,
        ]);
    }

    // 📌 تعديل حساب
    public function update(Request $request, Account $account)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'parent_id' => 'nullable|exists:accounts,id',
            'type'      => 'required|in:account,sub_account,title',
        ]);

        if (isset($data['parent_id'])) {
            $data['ownerEl'] = $data['parent_id'];
        }
        if ($data['type'] === 'account') {
            $data['slave'] = 1;
            $data['has_sub'] = 0;
        } elseif ($data['type'] === 'sub_account') {
            $data['slave'] = 0;
            $data['has_sub'] = 1;
        } else {
            $data['slave'] = 0;
            $data['has_sub'] = 0;
        }

        try {
            $this->accountService->update($account->id, $data);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => __('general.Updated_successfully')]);
            }

            return redirect()->route('accounts.index')->with('success', __('general.Account_updated_successfully'));
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return redirect()->route('accounts.index')->with('error', $e->getMessage());
        }
    }

    // 📌 حذف حساب
    public function destroy(Account $account)
    {
        try {
            $this->accountService->delete($account->id);

            return response()->json([
                'success' => true,
                'message' => __('Account deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }


    public function getAccountDeleteInfo(Account $account)
    {
        $hasChildren = $account->children()->exists();
        $creditor = $account->creditor;
        $debtor = $account->debtor;

        return response()->json([
            'success' => true,
            'has_children' => $hasChildren,
            'creditor' => $creditor,
            'debtor' => $debtor,
            'account' => $account,
        ]);
    }
}
