<?php

namespace Nh\Repositories\LineCalls;
use Nh\Repositories\BaseRepository;

class DbLineCallRepository extends BaseRepository implements LineCallRepository
{
    public function __construct(LineCall $lineCall)
    {
        $this->model = $lineCall;
    }

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
        $query      = array_get($params, 'q', '');
        $userId     = array_get($params, 'user_id', '');
        $model      = $this->model;

        if (!empty($sorting) && array_key_exists(1, $sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($userId != '') {
            $model = $model->whereHas('user', function ($model) use ($userId) {
                $model->where('id', convert_uuid2id($userId));
            });
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('phone_account	', 'like', "%{$query}%")
                    ->orWhere('email_account	', 'like', "%{$query}%");
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
        if (array_key_exists('user_id', $data)) {
            $data['user_id'] = convert_uuid2id($data['user_id']);
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
        if (array_key_exists('user_id', $data)) {
            $data['user_id'] = convert_uuid2id($data['user_id']);
        }
        $record = $this->getById($id);
        $record->fill($data)->save();
        return $this->getById($id);
    }

}
