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
            Log::error("فشل إنشاء الحساب: " . $e->getMessage());
            throw new \Exception(__('Account creation failed'));
        }
    }

    // 📌 تعديل حساب
    public function update($id, array $data): Model
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                if (!$id) {
                    throw new \Exception("الحساب غير موجود");
                }
                $data = $this->generateAccountData($data);
                return $this->repository->update($id, $data);
            });
        } catch (\Exception $e) {
            Log::error("فشل تعديل الحساب: " . $e->getMessage());
            throw new \Exception("حصل خطأ أثناء تعديل الحساب");
        }
    }


    // 📌 حذف حساب



    public function delete($id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $account = $this->repository->find($id);
                if (!$account) {
                    throw new \Exception("الحساب غير موجود");
                }

                // تحقق من وجود أبناء
                if ($account->children()->exists()) {
                    throw new \Exception("لا يمكن حذف هذا الحساب لأنه يحتوي على فروع.");
                }

                // تحقق من الرصيد الدائن
                if ($account->creditor != 0 && $account->creditor != null) {
                    throw new \Exception("لا يمكن حذف هذا الحساب لأنه له رصيد دائن.");
                }

                // تحقق من الرصيد المدين
                if ($account->debtor != 0 && $account->debtor != null) {
                    throw new \Exception("لا يمكن حذف هذا الحساب لأنه له رصيد مدين.");
                }

                // تحقق من وجود قيود يومية
                if ($account->journalEntries()->exists()) {
                    throw new \Exception("لا يمكن حذف هذا الحساب لأنه مرتبط بقيود يومية.");
                }

                // تحقق من وجود بنوك مرتبطة
                if (method_exists($account, 'banks') && $account->banks()->exists()) {
                    throw new \Exception("لا يمكن حذف هذا الحساب لأنه مرتبط ببيانات بنكية.");
                }

                return (bool) $this->repository->delete($id);
            });
        } catch (\Exception $e) {
            Log::error("فشل حذف الحساب: " . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
