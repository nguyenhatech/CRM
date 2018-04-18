<?php

namespace Nh\Repositories\Feedback;
use Nh\Repositories\BaseRepository;

class DbFeedbackRepository extends BaseRepository implements FeedbackRepository
{
    public function __construct(Feedback $feedback)
    {
        $this->model = $feedback;
    }

    /**
     * Lưu thông tin 1 bản ghi mới
     *
     * @param  array $data
     * @return Eloquent
     */
    public function store($data)
    {
        $type                = array_get($data, 'type', 1); // Mặc định là từ khách hàng
        $answers             = array_get($data, 'answers', []);
        $customer_id         = array_get($data, 'customer_id');
        $customer_id         = \Hashids::decode($customer_id)[0];
        $data['customer_id'] = $customer_id;
        
        // Lưu ID CSHK tạo ra bản ghi nếu type = 2
        if ($type == 2) {
            $data['user_id'] = getCurrentUser()->id;
        }

        // Lưu Feedback
        $feedback = $this->model->create($data);

        // Sync câu trả lời
        // Sắp xếp mảng Ansert theo chiều value tăng đần để đảm bảo điều khách thích xếp trước khách ko thích
        $sorted = array_sort($answers);

        $feedback->answers()->sync($sorted);

        return $this->getById($feedback->id);
    }

    /**
     * Cập nhật bản ghi
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function update($id, $data)
    {
        $record  = $this->getById($id);

        // Cập nhật trả lời
        $record->fill($data)->save();

        return $this->getById($id);
    }

    /**
     * Xóa phản hồi
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete($id)
    {
        $record = $this->getById($id);
        
        // Sync câu trả lời
        $record->answers()->sync([]);

        return $record->delete();
    }


}
