<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\AccountRepository;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\Interfaces\AccountRepositoryInterface;

class AccountService extends BaseService
{
    protected $repository;
    protected $treeCodeGenerator;

    public function __construct(AccountRepository $repository, TreeCodeGeneratorService $treeCodeGenerator)
    {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->treeCodeGenerator = $treeCodeGenerator;
    }

    public function getAccountTree()
    {
        return $this->repository->getTree();
    }

    public function getMainAccounts()
    {
        return $this->repository->getMainAccounts();
    }

    public function generateAccountData(array $data): array
    {
        return $this->treeCodeGenerator->generate($data, $this->repository->getModel());
    }

    public function getChildrenOf(int $parentId)
    {
        return $this->repository->getModel()
            ->where('ownerEl', $parentId)
            ->get();
    }

    // 📌 إنشاء حساب جديد
    public function createAccount(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $data = $this->generateAccountData($data);
                return $this->repository->create($data);
            });
        } catch (\Exception $e) {
            Log::error(__('Account creation failed') . " : " . $e->getMessage());
            throw new \Exception(__('Account creation failed'));
        }
    }

    // 📌 تعديل حساب
    public function update($id, array $data): Model
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                if (!$id) {
                    throw new \Exception(__("Account not found"));
                }
                $data = $this->generateAccountData($data);
                return $this->repository->update($id, $data);
            });
        } catch (\Exception $e) {
            Log::error(__('Account update failed') . " : " . $e->getMessage());
            throw new \Exception(__('Account update failed'));
        }
    }


    // 📌 حذف حساب



    public function delete($id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $account = $this->repository->find($id);
                if (!$account) {
                    throw new \Exception(__("Account not found"));
                }

                // تحقق من وجود أبناء
                if ($account->children()->exists()) {
                    throw new \Exception(__('This account cannot be deleted because it has branches'));
                }

                // تحقق من الرصيد الدائن
                if ($account->creditor != 0 && $account->creditor != null) {
                    throw new \Exception(__('Cannot delete account because it has a creditor balance'));
                }

                // تحقق من الرصيد المدين
                if ($account->debtor != 0 && $account->debtor != null) {
                    throw new \Exception(__('Cannot delete account because it has a debtor balance'));
                }

                // تحقق من وجود قيود يومية
                if ($account->journalEntries()->exists()) {
                    throw new \Exception(__('Cannot edit parent account because it has journal entries'));
                }

                // تحقق من وجود بنوك مرتبطة
                if (method_exists($account, 'banks') && $account->banks()->exists()) {
                    throw new \Exception(__('Cannot delete this account because it is linked to bank data'));
                }

                return (bool) $this->repository->delete($id);
            });
        } catch (\Exception $e) {
            Log::error(__('Account delete failed') . " : " . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
