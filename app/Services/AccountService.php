<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AccountRepository;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\Interfaces\AccountRepositoryInterface;
use App\Services\TreeCodeGeneratorService;

class AccountService extends BaseService
{
    protected $repository;
    protected $treeCodeGenerator;

    // Cache settings
    protected int $cacheTtl = 3600; // seconds
    protected string $cachePrefix = 'accounts:'; // prefix for keys
    protected bool $useTags = false;

    public function __construct(AccountRepository $repository, TreeCodeGeneratorService $treeCodeGenerator)
    {
        
        parent::__construct($repository);
        $this->repository = $repository;
        $this->treeCodeGenerator = $treeCodeGenerator;

        // Detect if the cache store supports tags (Redis/Memcached taggable store)
        try {
            $store = Cache::getStore();
            $this->useTags = method_exists($store, 'tags');
        } catch (\Throwable $e) {
            $this->useTags = false;
        }
    }

    protected function cacheKey(string $key): string
    {
        return $this->cachePrefix . $key;
    }

    protected function remember(string $key, \Closure $callback)
    {
        $fullKey = $this->cacheKey($key);
        if ($this->useTags) {
            return Cache::tags('accounts')->remember($fullKey, $this->cacheTtl, $callback);
        }
        return Cache::remember($fullKey, $this->cacheTtl, $callback);
    }

    protected function forget(string $key)
    {
        $fullKey = $this->cacheKey($key);
        if ($this->useTags) {
            // tags->flush will clear all tagged keys; here we attempt targeted forget if supported
            try {
                Cache::tags('accounts')->forget($fullKey);
                return;
            } catch (\Throwable $e) {
                Cache::tags('accounts')->flush();
                return;
            }
        }
        Cache::forget($fullKey);
    }

    protected function clearAccountsCache(array $specificKeys = [])
    {
        if ($this->useTags) {
            Cache::tags('accounts')->flush();
            return;
        }

        $defaults = [
            $this->cacheKey('tree'),
            $this->cacheKey('main'),
        ];

        $keys = array_unique(array_merge($defaults, array_map(function ($k) {
            return $this->cacheKey($k);
        }, $specificKeys)));

        foreach ($keys as $k) {
            Cache::forget($k);
        }
    }

    public function getAccountTree()
    {
        return $this->remember('tree', function () {
            return $this->repository->getTree();
        });
    }

    public function getMainAccounts()
    {
        return $this->remember('main', function () {
            return $this->repository->getMainAccounts();
        });
    }

    public function generateAccountData(array $data): array
    {
        return $this->treeCodeGenerator->generate($data, $this->repository->getModel());
    }

    public function getChildrenOf(int $parentId)
    {
        $key = "children:{$parentId}";
        return $this->remember($key, function () use ($parentId) {
            return $this->repository->getModel()
                ->where('ownerEl', $parentId)
                ->get();
        });
    }

    // 📌 إنشاء حساب جديد
    public function createAccount(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $data = $this->generateAccountData($data);
                $result = $this->repository->create($data);

                // Clear relevant caches after create
                // clear full accounts cache (tree/main). If created under a parent, clear that parent's children key.
                $parentKey = [];
                if (!empty($data['ownerEl'])) {
                    $parentKey[] = "children:{$data['ownerEl']}";
                }
                $this->clearAccountsCache($parentKey);

                return $result;
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

                // find existing to detect parent change
                $existing = $this->repository->find($id);
                if (!$existing) {
                    throw new \Exception(__("Account not found"));
                }

                $oldParent = $existing->ownerEl ?? null;

                $data = $this->generateAccountData($data);
                $result = $this->repository->update($id, $data);

                // Clear caches: tree, main and children of old/new parent if present
                $keys = [];
                if ($oldParent !== null) {
                    $keys[] = "children:{$oldParent}";
                }
                if (!empty($data['ownerEl'])) {
                    $keys[] = "children:{$data['ownerEl']}";
                }
                $this->clearAccountsCache($keys);

                return $result;
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

                $deleted = (bool) $this->repository->delete($id);

                // clear cache after delete (tree/main and parent's children)
                $keys = [];
                if (!empty($account->ownerEl)) {
                    $keys[] = "children:{$account->ownerEl}";
                }
                $this->clearAccountsCache($keys);

                return $deleted;
            });
        } catch (\Exception $e) {
            Log::error(__('Account delete failed') . " : " . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
