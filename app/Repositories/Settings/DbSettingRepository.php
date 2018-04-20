<?php

namespace Nh\Repositories\Settings;
use Nh\Repositories\BaseRepository;

class DbSettingRepository extends BaseRepository implements SettingRepository
{
    public function __construct(Setting $setting)
    {
        $this->model = $setting;
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
        $query  = array_get($params, 'q', '');
        $status = array_get($params, 'status', null);

        $model  = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if (!is_null($status)) {
            $status = (int) $status;
            $model  = $model->where('status', $status);
        }

        if (!empty($query)) {
            $query = '%' . $query . '%';
            $model = $model->where(function ($q) use ($query) {
                $q->where('code','LIKE', $query);
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
        if (array_get($data, 'special_day', null)) {
            $data['special_day'] = json_encode($data['special_day']);
        }
        
        $model = $this->model->create($data);
        return $this->getById($model->id);
    }

}
