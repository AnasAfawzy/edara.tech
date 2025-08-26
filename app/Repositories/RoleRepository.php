<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    protected $cachePrefix = 'roles_';

    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function all(): Collection
    {
        $cacheKey = $this->cachePrefix . 'all';
        return Cache::remember($cacheKey, 300, function () {
            return $this->model->all();
        });
    }

    public function find(int $id): Model
    {
        $cacheKey = $this->cachePrefix . 'find_' . $id;
        return Cache::remember($cacheKey, 300, function () use ($id) {
            return $this->model->find($id);
        });
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data): Model
    {
        $role = $this->model->create(['name' => $data['name']]);
        // ربط الصلاحيات إذا وجدت
        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        // ربط الموديولات إذا وجدت
        if (isset($data['sidebar_modules'])) {
            $role->modules()->sync($data['sidebar_modules']);
        }
        $this->clearRolesCache();
        return $role;
    }

    public function update(int $id, array $data): ?Model
    {
        $role = $this->model->find($id);
        if ($role) {
            $role->update(['name' => $data['name']]);
            // تحديث الصلاحيات
            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            } else {
                $role->syncPermissions([]);
            }
            // تحديث الموديولات
            if (isset($data['sidebar_modules'])) {
                $role->modules()->sync($data['sidebar_modules']);
            } else {
                $role->modules()->sync([]);
            }
            $this->clearRolesCache();
        }
        return $role;
    }

    public function delete(int $id): bool
    {
        $role = $this->model->find($id);
        if ($role) {
            $deleted = $role->delete();
            if ($deleted) {
                $this->clearRolesCache();
            }
            return $deleted;
        }
        return false;
    }

    public function paginateWithPermissions(int $perPage, string $search = '')
    {
        $cacheKey = $this->cachePrefix . "paginate_{$perPage}_" . md5($search);
        return Cache::remember($cacheKey, 300, function () use ($perPage, $search) {
            $query = $this->model->with('permissions');
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }
            return $query->orderBy('id', 'desc')->paginate($perPage);
        });
    }

    protected function clearRolesCache()
    {
        // لمسح كل الكاش الخاص بالأدوار فقط (يدعم redis)
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            $redis = Cache::getRedis();
            $keys = $redis->keys(config('cache.prefix') . ':' . $this->cachePrefix . '*');
            foreach ($keys as $key) {
                $redis->del($key);
            }
        } else {
            // في حالة file cache أو غيره، خزّن كل المفاتيح في مصفوفة وامسحها هنا
            Cache::forget($this->cachePrefix . 'all');
            // لا يمكن حذف كل المفاتيح دفعة واحدة في file cache، يمكنك استخدام مكتبة spatie/laravel-responsecache أو حل مخصص
        }
    }
}
