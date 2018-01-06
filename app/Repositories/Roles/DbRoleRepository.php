<?php

namespace Nh\Repositories\Roles;
use Nh\Repositories\BaseRepository;

class DbRoleRepository extends BaseRepository implements RoleRepository
{
    public function __construct(Role $role)
    {
        $this->model = $role;
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
        $model = $this->model->where('name', '!=', 'superadmin');

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

    public function syncAdmins($model, $users)
    {
        $users = collect($users);
        $ids = $users->map(function ($user) {
            return $user['id'];
        })->toArray();

        return $model->users()->sync($ids);
    }

    public function syncPermissions($model, $permissions)
    {
        $permissions = collect($permissions);
        $ids = $permissions->map(function ($permission) {
            return $permission['id'];
        })->toArray();

        return $model->perms()->sync($ids);
    }

}
