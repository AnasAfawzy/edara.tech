<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\RoleRepositoryInterface;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    protected $cachePrefix = 'roles_';

    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    protected function trackedKeysKey(): string
    {
        return $this->cachePrefix . 'keys';
    }

    protected function addCacheKey(string $key): void
    {
        $trackedKey = $this->trackedKeysKey();
        $keys = Cache::get($trackedKey, []);
        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::forever($trackedKey, $keys);
        }
    }

    public function all(): Collection
    {
        $cacheKey = $this->cachePrefix . 'all';
        $this->addCacheKey($cacheKey);
        return Cache::remember($cacheKey, 300, fn() => $this->model->all());
    }

    public function find(int $id): Model
    {
        $cacheKey = $this->cachePrefix . 'find_' . $id;
        $this->addCacheKey($cacheKey);
        return Cache::remember($cacheKey, 300, fn() => $this->model->find($id));
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data): Model
    {
        DB::beginTransaction();
        try {
            $role = $this->model->create(['name' => $data['name']]);

            // التأكد من وجود الصلاحيات وإنشائها إذا لم تكن موجودة
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $permissions = $this->ensurePermissionsExist($data['permissions']);
                $role->syncPermissions($permissions);
            }

            if (isset($data['sidebar_modules']) && is_array($data['sidebar_modules'])) {
                $role->modules()->sync($data['sidebar_modules']);
            }

            $this->clearRolesCache($role->id);
            DB::commit();
            return $role;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function update(int $id, array $data): ?Model
    {
        DB::beginTransaction();
        try {
            $role = $this->model->find($id);
            if ($role) {
                $role->update(['name' => $data['name']]);

                // التأكد من وجود الصلاحيات وإنشائها إذا لم تكن موجودة
                if (isset($data['permissions']) && is_array($data['permissions'])) {
                    $permissions = $this->ensurePermissionsExist($data['permissions']);
                    $role->syncPermissions($permissions);
                } else {
                    $role->syncPermissions([]);
                }

                $role->modules()->sync($data['sidebar_modules'] ?? []);
                Cache::forget("role_main_modules_{$role->id}");
                $this->clearRolesCache($role->id);
            }
            DB::commit();
            return $role;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $role = $this->model->find($id);
        if ($role) {
            $deleted = $role->delete();
            if ($deleted) {
                $this->clearRolesCache($id);
            }
            return $deleted;
        }
        return false;
    }

    public function paginateWithPermissions(int $perPage, string $search = '')
    {
        $cacheKey = $this->cachePrefix . "paginate_{$perPage}_" . md5($search);
        $this->addCacheKey($cacheKey);
        return Cache::remember($cacheKey, 300, function () use ($perPage, $search) {
            $query = $this->model->with('permissions');
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }
            return $query->orderBy('id', 'desc')->paginate($perPage);
        });
    }

    protected function clearRolesCache(?int $roleId = null)
    {
        // مسح الكاش العام للـ roles
        $this->clearTrackedKeys();

        // مسح كاش FileStore بشكل آمن
        if (Cache::getStore() instanceof \Illuminate\Cache\FileStore) {
            $store = Cache::getStore();
            $cacheDirectory = $store->getDirectory();
            $files = glob($cacheDirectory . '/*');
            foreach ($files as $file) {
                // تحقق من صلاحية الملف قبل محاولة قراءته أو حذفه
                if (!is_readable($file) || !is_writable($file)) {
                    continue;
                }
                $base = basename($file);
                if (
                    strpos($base, $this->cachePrefix) === 0 ||
                    ($roleId && strpos($base, "role_main_modules_{$roleId}") !== false)
                ) {
                    @unlink($file);
                }
            }
        }

        // مسح كاش Redis
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            try {
                $redis = Cache::getRedis();
                $prefix = config('cache.prefix') ? config('cache.prefix') . ':' : '';
                $keys = $redis->keys($prefix . $this->cachePrefix . '*');
                foreach ($keys as $key) {
                    $redis->del($key);
                }
                if ($roleId) {
                    $redis->del($prefix . "role_main_modules_{$roleId}");
                }
            } catch (\Throwable $e) {
                $this->clearTrackedKeys();
            }
        }
    }

    protected function clearTrackedKeys()
    {
        $trackedKey = $this->trackedKeysKey();
        $keys = Cache::get($trackedKey, []);
        foreach ($keys as $k) {
            Cache::forget($k);
        }
        Cache::forget($this->cachePrefix . 'all');
        Cache::forget($trackedKey);
    }

    protected function ensurePermissionsExist(array $permissionNames): array
    {
        $permissions = [];

        foreach ($permissionNames as $permissionName) {
            // محاولة العثور على الصلاحية أولاً
            $permission = Permission::where('name', $permissionName)
                ->where('guard_name', 'web')
                ->first();

            // إذا لم توجد، قم بإنشائها
            if (!$permission) {
                $permission = Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'web'
                ]);
            }

            $permissions[] = $permission;
        }

        return $permissions;
    }
}
