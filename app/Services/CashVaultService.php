<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\CashVaultRepository;

class CashVaultService extends BaseService
{
    protected $repository;
    protected $accountService;
    protected $ParentAccountId;

    public function __construct(CashVaultRepository $repository, AccountService $accountService)
    {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->accountService = $accountService;
        $this->ParentAccountId = acc_setting('default_cash_vault_account');
    }

    public function getAllCashVaults()
    {
        return $this->repository->all();
    }

    public function getChildAccounts()
    {
        return $this->accountService->getChildrenOf($this->ParentAccountId);
    }

    public function findCashVault(int $id)
    {
        return $this->repository->find($id);
    }

    public function searchVaults($search = '', $perPage = 10)
    {
        return $this->repository->getModel()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function createVault(array $data)
    {

        try {
            return DB::transaction(function () use ($data) {
                $accountData = [
                    'name'    => $data['name'],
                    'ownerEl' => $this->ParentAccountId,
                    'slave'   => 0,
                    'has_sub' => 0,
                    'is_sub'  => 1,
                    'level'   => 0,
                ];

                $accountData = $this->accountService->generateAccountData($accountData);
                $account = $this->accountService->create($accountData);

                if (!$account || !$account->id) {
                    throw new \Exception(__('Vault account creation failed'));
                }

                $vaultData = $data;
                $vaultData['account_id'] = $account->id;
                $vault = $this->repository->create($vaultData);

                if (!$vault || !$vault->id) {
                    throw new \Exception(__('Failed To Create The Vault'));
                }

                return $vault;
            });
        } catch (\Exception $e) {
            Log::error("Error creating vault: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateVault(int $id, array $data)
    {
        $vault = $this->repository->findOrFail($id);

        if (!$vault) {
            throw new \Exception(__('The vault does not exist.'));
        }

        $vault->update($data);

        return $vault;
    }

    public function deleteVault($id)
    {
        $vault = $this->repository->findOrFail($id);

        // 1. التأكد من الرصيد
        if ($vault->balance != 0) {
            throw new \Exception(__('The Vault cannot be deleted if it already has a balance'));
        }

        // 2. التأكد من عدم وجود قيود يومية مرتبطة بالحساب
        if ($vault->journalEntries()->exists()) {
            throw new \Exception(__('Cannot delete vault because it has journal entries'));
        }

        // 3. احفظ account_id قبل الحذف
        $accountId = $vault->account_id;

        // 4. احذف الخزنة أولاً
        $vault->delete();

        // 5. ثم احذف الحساب المرتبط إذا وجد
        if ($accountId) {
            $this->accountService->delete($accountId);
        }

        return true;
    }
}
