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

        $answers = array_get($data, 'answers', []);
        
        $feedback = $this->model->create($data);

        // Sync câu hỏi
        $feedback->answers()->sync($answers);

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

        // Cập nhật câu hỏi
        $record->fill($data)->save();

        return $this->getById($id);
    }


}
