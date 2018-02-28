<?php

namespace Nh\Repositories\Cgroups;
use Nh\Repositories\BaseRepository;
use Nh\Repositories\UploadTrait;

class DbCgroupRepository extends BaseRepository implements CgroupRepository
{
	use UploadTrait;

    public function __construct(Cgroup $cgroup)
    {
        $this->model = $cgroup;
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

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                	->orWhere('uuid', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    public function store($data) {
        $data = array_only($data, ['name', 'avatar', 'description']);
        $data['client_id'] = getCurrentUser()->id;
        $model = $this->model->create($data);
        return $this->getById($model->id);
    }

}
