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

    protected function trackedKeysKey(): string
    {
        return $this->cachePrefix . 'keys';
    }

    protected function addCacheKey(string $key): void
    {
        // keep a list of keys we create so we can clear them for non-redis stores
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
        return Cache::remember($cacheKey, 300, function () {
            return $this->model->all();
        });
    }

    public function find(int $id): Model
    {
        $cacheKey = $this->cachePrefix . 'find_' . $id;
        $this->addCacheKey($cacheKey);
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
        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
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
            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            } else {
                $role->syncPermissions([]);
            }
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
        $this->addCacheKey($cacheKey);

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
        // If Redis store available try to remove by pattern
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            try {
                $redis = Cache::getRedis();
                $prefix = config('cache.prefix') ? config('cache.prefix') . ':' : '';
                $keys = $redis->keys($prefix . $this->cachePrefix . '*');
                foreach ($keys as $key) {
                    $redis->del($key);
                }
            } catch (\Throwable $e) {
                // fallback to removing tracked keys
                $this->clearTrackedKeys();
            }
            return;
        }

        // For file / array / other stores: use the tracked keys list and delete them
        $this->clearTrackedKeys();
    }

    protected function clearTrackedKeys()
    {
        $trackedKey = $this->trackedKeysKey();
        $keys = Cache::get($trackedKey, []);
        foreach ($keys as $k) {
            Cache::forget($k);
        }
        // also forget the 'all' key and the tracked keys index
        Cache::forget($this->cachePrefix . 'all');
        Cache::forget($trackedKey);
    }
}
