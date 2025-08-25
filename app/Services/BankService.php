<?php

namespace App\Services;

use App\Repositories\BankRepository;
use App\Repositories\Interfaces\BankRepositoryInterface;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankService extends BaseService
{
    protected $repository;
    protected $accountService;
    protected $ParentAccountId ;

    public function __construct(BankRepository $repository, AccountService $accountService)
    {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->accountService = $accountService;
        $this->ParentAccountId = acc_setting('default_bank_account');
    }

    public function getAllBanks()
    {
        return $this->accountService->getChildrenOf($this->ParentAccountId);
    }

    public function createBank(array $data)
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
                    throw new \Exception(__('Bank account creation failed'));
                }

                // 2️⃣ إنشاء البنك وربطه بالحساب
                $bankData = $data;
                $bankData['account_id'] = $account->id;
                $bank = $this->repository->create($bankData);

                if (!$bank || !$bank->id) {
                    throw new \Exception(__('Failed To Create The Bank'));
                }

                return $bank;
            });
        } catch (\Exception $e) {
            Log::error("Error creating bank: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateBank(int $id, array $data)
    {
        $bank = $this->repository->findOrFail($id);

        if (!$bank) {
            throw new \Exception("البنك غير موجود.");
        }

        $bank->update($data);

        return $bank;
    }

    public function delete($id): bool
    {
        $bank = $this->repository->findOrFail($id);

        // التأكد من الرصيد
        if ($bank->balance != 0) {
            throw new \Exception(__('The bank cannot be deleted if it already has a balance'));
        }

        // التأكد من عدم وجود قيود يومية مرتبطة بالحساب أو الحساب نفسه
        if ($bank->journalEntries()->exists()) {
            throw new \Exception(__('Cannot delete bank because it has journal entries'));
        }
        if ($bank->account && $bank->account->journalEntries()->exists()) {
            throw new \Exception(__('Cannot delete bank because its account has journal entries'));
        }

        // احفظ account_id قبل الحذف
        $accountId = $bank->account_id;

        // احذف البنك أولاً
        $bank->delete();

        // ثم احذف الحساب المرتبط إذا وجد
        if ($accountId) {
            $this->accountService->delete($accountId);
        }

        return true;
    }

    public function searchBanks($search = '', $perPage = 10)
    {
        return $this->repository->getModel()->with('currency')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('account_number', 'like', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }
}
