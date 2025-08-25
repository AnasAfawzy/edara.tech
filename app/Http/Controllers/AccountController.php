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

    public function __construct(
        AccountService $accountService,
        TreeCodeGeneratorService $treeCodeGeneratorService,
        AccountRepository $repository
    ) {
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
            'accounts'    => json_encode($jsTreeData, JSON_UNESCAPED_UNICODE),
            'allAccounts' => $allAccounts
        ]);
    }

    public function treeData()
    {
        $accounts = $this->accountService->getAccountTree();
        return response()->json($this->buildJsTreeFlat($accounts));
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $data = $this->mapTypeData($data);

        try {
            $account = $this->accountService->createAccount($data);

            $node = $this->buildJsTreeNode($account);

            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => true, 'message' => __('Account creation successful'), 'node' => $node, 'data' => $account])
                : redirect()->route('accounts.index')->with('success', __('Account created successfully'));
        } catch (\Exception $e) {
            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 400)
                : redirect()->route('accounts.index')->with('error', $e->getMessage());
        }
    }

    public function edit(Account $account)
    {
        $parent_details_html = '';
        if ($account->ownerEl) {
            $parent = $this->repository->find($account->ownerEl);
            if ($parent) {
                $parentType = $parent->has_sub ? __('title') : ($parent->slave ? __('sub account') : __('account'));

                $parent_details_html = sprintf(
                    '<b>%s:</b> %s<br><b>%s:</b> %s<br><b>%s:</b> %s',
                    __('Account Name'),
                    e($parent->name),
                    __('Account Code'),
                    e($parent->code),
                    __('Account Type'),
                    $parentType
                );
            }
        }

        $type = $this->resolveAccountType($account);

        return response()->json([
            'success'            => true,
            'account'            => $account,
            'has_children'       => $account->children()->exists(),
            'has_journals'       => $account->journalEntryDetails()->exists(),
            'is_sub_account'     => $type === 'account' || $type === 'sub_account',
            'is_title'           => $type === 'title',
            'type'               => $type,
            'parent_details_html' => $parent_details_html,
        ]);
    }

    // 📌 تعديل حساب
    public function update(Request $request, Account $account)
    {
        $data = $this->validateRequest($request);
        $data = $this->mapTypeData($data);

        try {
            $this->accountService->update($account->id, $data);

            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => true, 'message' => __('Updated successfully')])
                : redirect()->route('accounts.index')->with('success', __('Account updated successfully'));
        } catch (\Exception $e) {
            return $request->wantsJson() || $request->ajax()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 400)
                : redirect()->route('accounts.index')->with('error', $e->getMessage());
        }
    }


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
        return response()->json([
            'success'      => true,
            'has_children' => $account->children()->exists(),
            'creditor'     => $account->creditor,
            'debtor'       => $account->debtor,
            'account'      => $account,
        ]);
    }

    // ==============================
    // 🔹 Helpers
    // ==============================

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'name'      => 'required|string|max:100',
            'parent_id' => 'nullable|exists:accounts,id',
            'type'      => 'required|in:account,sub_account,title',
        ]);
    }

    private function mapTypeData(array $data): array
    {
        if (isset($data['parent_id'])) {
            $data['ownerEl'] = $data['parent_id'];
        }

        switch ($data['type']) {
            case 'account':
                $data['slave'] = 1;
                $data['has_sub'] = 0;
                break;
            case 'sub_account':
                $data['slave'] = 0;
                $data['has_sub'] = 1;
                break;
            default:
                $data['slave'] = 0;
                $data['has_sub'] = 0;
        }

        return $data;
    }

    private function resolveAccountType(Account $account): string
    {
        if ($account->slave && !$account->has_sub) {
            return 'account';
        } elseif (!$account->slave && $account->has_sub) {
            return 'sub_account';
        }
        return 'title';
    }

    private function buildJsTreeFlat($accounts)
    {
        return $accounts->map(fn($account) => $this->buildJsTreeNode($account));
    }

    private function buildJsTreeNode(Account $account)
    {
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

        return [
            'id'     => $account->id,
            'parent' => $account->ownerEl ?: '#',
            'text'   => $account->name,
            'icon'   => $icon,
            'type'   => $type,
            'code'   => $account->code,
            'li_attr' => ['class' => $class]
        ];
    }
}
