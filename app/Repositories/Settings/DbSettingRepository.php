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

    /**
     * Cập nhật thông tin 1 bản ghi theo ID
     *
     * @param  integer $id ID bản ghi
     * @return bool
     */
    public function update($id, $data)
    {
        if (array_get($data, 'special_day', [])) {
            $data['special_day'] = json_encode($data['special_day']);
        } else {
            $data['special_day'] = '';
        }

        if (isset($data['levels']['normal'])) {
            $data['level_normal'] = $data['levels']['normal'];
        }
        if (isset($data['levels']['sliver'])) {
            $data['level_sliver'] = $data['levels']['sliver'];
        }
        if (isset($data['levels']['gold'])) {
            $data['level_gold'] = $data['levels']['gold'];
        }
        if (isset($data['levels']['diamond'])) {
            $data['level_diamond'] = $data['levels']['diamond'];
        }

        $record = $this->getById($id);

        $record->fill($data)->save();
        
        return $this->getById($id);
    }

}
