<?php

namespace Nh\Repositories\Users;
use Nh\Repositories\BaseRepository;
use Nh\Repositories\UploadTrait;
use Nh\Repositories\Roles\Role;
use Nh\User;

class DbUserRepository extends BaseRepository implements UserRepository
{
    use UploadTrait;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /**
     * Lấy thông tin 1 bản ghi xác định bởi ID
     *
     * @param  integer $id ID bản ghi
     * @return Eloquent
     */

    public function getById($id)
    {
        $id = convert_uuid2id($id);
        return $this->model->find($id);
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

        if (!getCurrentUser()->isSuperAdmin()) {
            $model = $model->whereHas('roles', function($q) {
                return $q->where('type', '<>', Role::TYPE_SYSTEM);
            });
        }

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    /**
     * Lưu thông tin 1 bản ghi mới
     *
     * @param  array $data
     * @return Eloquent
     */
    public function store($data)
    {
        $model = $this->model->create($data);

        $roles = array_get($data, 'roles', []);

        $model->roles()->sync($roles);
        return $this->getById($model->id);
    }

    /**
     * Cập nhật thông tin 1 bản ghi theo ID
     *
     * @param  integer $id ID bản ghi
     * @return bool
     */
    public function update($id, $data)
    {
        $record = $this->getById($id);

        if ($password = array_get($data, 'password', null)) {
            $data['password'] = bcrypt($data['password']);
        }

        $record->fill($data)->save();
        $roles = array_get($data, 'roles', '');
        if ($roles != '') {
            if (!getCurrentUser()->isSuperAdmin()) {
                $sysRoles = $record->roles()->where('type' , \Nh\Repositories\Roles\Role::TYPE_SYSTEM)->get()->pluck('id')->toArray();
                $roles = array_merge($sysRoles, $roles);
            }

            $record->roles()->sync($roles);
        };

        return $this->getById($id);
    }

}
