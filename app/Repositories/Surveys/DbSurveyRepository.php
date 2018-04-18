<?php

namespace Nh\Repositories\Surveys;
use Nh\Repositories\BaseRepository;

class DbSurveyRepository extends BaseRepository implements SurveyRepository
{
    public function __construct(Survey $survey)
    {
        $this->model = $survey;
    }

    /**
     * Lưu thông tin 1 bản ghi mới
     *
     * @param  array $data
     * @return Eloquent
     */
    public function store($data)
    {
        $questions = array_get($data, 'questions', []);
        
        $survey = $this->model->create($data);

        // Sync câu hỏi
        $survey->questions()->sync($questions);

        return $this->getById($survey->id);
    }

    public function update($id, $data)
    {
        $record  = $this->getById($id);

        $questions = array_get($data, 'questions', []);

        // Cập nhật câu hỏi
        $record->fill($data)->save();

        // Sync câu hỏi
        $record->questions()->sync($questions);

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

        // Xóa quan hệ:
        $record->questions()->sync([]);

        return $record->delete();
    }

}
