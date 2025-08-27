<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected $cachePrefix = 'users_';

    public function __construct(User $model)
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

    public function all()
    {
        $cacheKey = $this->cachePrefix . 'all';
        $this->addCacheKey($cacheKey);
        return Cache::remember($cacheKey, 300, function () {
            return $this->model->all();
        });
    }

    public function find(int $id): User
    {
        $cacheKey = $this->cachePrefix . 'find_' . $id;
        $this->addCacheKey($cacheKey);
        return Cache::remember($cacheKey, 300, function () use ($id) {
            return $this->model->with('roles')->find($id);
        });
    }

    public function getModel(): User
    {
        return $this->model;
    }

    public function createUser(array $data): User
    {
        $user = $this->model->create($data);
        $this->clearUsersCache();
        return $user;
    }

    public function updateUser(int $id, array $data): User
    {
        $user = $this->model->findOrFail($id);
        $user->update($data);
        $this->clearUsersCache();
        return $user;
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->model->findOrFail($id);
        $deleted = $user->delete();
        if ($deleted) {
            $this->clearUsersCache();
        }
        return $deleted;
    }

    public function paginateWithRoles(int $perPage, string $search = ''): LengthAwarePaginator
    {
        $cacheKey = $this->cachePrefix . "paginate_{$perPage}_" . md5($search);
        $this->addCacheKey($cacheKey);

        return Cache::remember($cacheKey, 300, function () use ($perPage, $search) {
            $query = $this->model->with('roles');
            if ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }
            return $query->orderBy('id', 'desc')->paginate($perPage);
        });
    }

    protected function clearUsersCache()
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
                $this->clearTrackedKeys();
            }
            return;
        }

        $this->clearTrackedKeys();
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
}
