<?php
namespace Nh\Repositories;
abstract class BaseRepository {

    protected $model;
    /**
     * Lấy thông tin 1 bản ghi xác định bởi ID
     *
     * @param  integer $id ID bản ghi
     * @return Eloquent
     */

    public function getById($id)
    {
        return $this->model->find($id);
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
        $record->fill($data)->save();
        return $this->getById($id);
    }

    /**
     * Xóa 1 bản ghi. Nếu model xác định 1 SoftDeletes
     * thì method này chỉ đưa bản ghi vào trash. Dùng method destroy
     * để xóa hoàn toàn bản ghi.
     *
     * @param  integer $id ID bản ghi
     * @return bool|null
     */
    public function delete($id)
    {
        $record = $this->getById($id);
        return $record->delete();
    }

    /**
     * Lấy tất cả bản ghi có phân trang
     *
     * @param  integer $size Số bản ghi mặc định 25
     * @param  array $sorting Sắp xếp
     * @return Illuminate\Pagination\Paginator
     */
    public function getByPaginate($size = 25, $sorting = [])
    {
        $lModel = $this->model;

        if (!empty($sorting)) {
            $lModel = $lModel->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        return $size <= 0 ? $lModel->get() : $lModel->paginate($size);
    }
}
