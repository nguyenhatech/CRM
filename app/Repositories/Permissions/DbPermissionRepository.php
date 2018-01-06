<?php

namespace Nh\Repositories\Permissions;
use Nh\Repositories\BaseRepository;

class DbPermissionRepository extends BaseRepository implements PermissionRepository
{
    public function __construct(Permission $permission)
    {
        $this->model = $permission;
    }

    /**
     * Lấy tất cả bản ghi có phân trang
     *
     * @param  integer $size Số bản ghi mặc định 25
     * @param  array $sorting Sắp xếp
     * @return Illuminate\Pagination\Paginator
     */
    public function getByQuery($params, $size = 25, $sorting = [])
    {
        $query = array_get($params, 'q', '');
        $model = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                    ->orWhere('display_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    public function syncRoles($model, $roles)
    {
        $roles = collect($roles);
        $ids = $roles->map(function ($role) {
            return $role['id'];
        })->toArray();

        return $model->roles()->sync($ids);
    }

}
