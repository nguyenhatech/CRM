<?php

namespace Nh\Repositories\Questions;
use Nh\Repositories\BaseRepository;
use Nh\Repositories\Answers\Answer;

class DbQuestionRepository extends BaseRepository implements QuestionRepository
{
    public function __construct(Question $question)
    {
        $this->model = $question;
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
        $query          = array_get($params, 'q', '');
        $status         = array_get($params, 'status', null);

        $model = $this->model;

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
                $q->where('content','LIKE', $query);
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
        $likes   =  array_get($data, 'likes');
        $unlikes =  array_get($data, 'unlikes');

        $dataQuestion = [
            'content' => array_get($data, 'content')
        ];

        // Lưu câu hỏi
        $question = $this->model->create($data);

        // Lưu câu trả lời thích
        foreach ($likes as $key_like => $like) {
            $dataLike = [
                'content' => $like['content'],
                'type'    => Answer::LIKE
            ];
            $question->answers()->create($dataLike);
        }
        // Lưu câu trả lời ko thích
        foreach ($unlikes as $key_unlike => $unlike) {
            $dataUnLike = [
                'content' => $unlike['content'],
                'type'    => Answer::UNLIKE
            ];
            $question->answers()->create($dataUnLike);
        }

        return $this->getById($question->id);
    }

    public function update($id, $data)
    {
        $record  = $this->getById($id);
        $likes   =  array_get($data, 'likes', null);
        $unlikes =  array_get($data, 'unlikes', null);

        $dataQuestionUpdate = [
            'content' => array_get($data, 'content', $record->content),
            'status'  => array_get($data, 'status', $record->status)
        ];

        // Cập nhật câu hỏi
        $record->fill($dataQuestionUpdate)->save();

        // Cập nhật phần thích
        if (!is_null($likes)) {
            $answersRepo = \App::make('Nh\Repositories\Answers\Answer');
            $answerInDB = $answersRepo->where('question_id', $record->id)
                                            ->where('type', Answer::LIKE)->get();
            $answerInDB = array_pluck($answerInDB, 'id');

            $listIDLikeNew = [];
            foreach ($likes as $keyLike => $like) {
                $dataLike = [
                    'content' => $like['content'],
                    'status'  => $like['status'],
                    'type'    => Answer::LIKE
                ];
                // Cái nào ko có Id và question_id thì lưu thẳng, có thì cập nhật
                if ( !isset($like['id']) && !isset($like['question_id']) ) {
                    $record->answers()->create($dataLike);
                } else {
                    // Lát so sánh với ID đã có để xóa những cái không có trong mảng này đi
                    $listIDLikeNew[] = $like['id'];
                    // Update
                    $answer = $answersRepo->where('question_id', $record->id)
                                            ->where('type', Answer::LIKE)
                                            ->where('id', $like['id'])->first();
                    if (!is_null($answer)) {
                        $answer->fill($dataLike)->save();
                    }
                }
            }
            // Xóa những cái không có
            $listLikeNewDelete = array_diff($answerInDB, $listIDLikeNew);

            foreach ($listLikeNewDelete as $keyDel => $valueDel) {
                $recordDel  = $answersRepo->where('id', $valueDel)->first();
                if (!is_null($recordDel)) {
                    $recordDel->delete();
                }
            }
        }

        // Xử lý tương tự hàm trên nhưng đổi type
        if (!is_null($unlikes)) {
            $answersRepo = \App::make('Nh\Repositories\Answers\Answer');
            $answerInDB = $answersRepo->where('question_id', $record->id)
                                            ->where('type', Answer::UNLIKE)->get();
            $answerInDB = array_pluck($answerInDB, 'id');

            $listIDLikeNew = [];
            foreach ($unlikes as $keyLike => $like) {
                $dataLike = [
                    'content' => $like['content'],
                    'status'  => $like['status'],
                    'type'    => Answer::UNLIKE
                ];
                // Cái nào ko có Id và question_id thì lưu thẳng, có thì cập nhật
                if ( !isset($like['id']) && !isset($like['question_id']) ) {
                    $record->answers()->create($dataLike);
                } else {
                    // Lát so sánh với ID đã có để xóa những cái không có trong mảng này đi
                    $listIDLikeNew[] = $like['id'];
                    // Update
                    $answer = $answersRepo->where('question_id', $record->id)
                                            ->where('type', Answer::UNLIKE)
                                            ->where('id', $like['id'])->first();
                    if (!is_null($answer)) {
                        $answer->fill($dataLike)->save();
                    }
                }
            }
            // Xóa những cái không có
            $listLikeNewDelete = array_diff($answerInDB, $listIDLikeNew);

            foreach ($listLikeNewDelete as $keyDel => $valueDel) {
                $recordDel  = $answersRepo->where('id', $valueDel)->first();
                if (!is_null($recordDel)) {
                    $recordDel->delete();
                }
            }
        }

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
        $record->answers()->delete();

        return $record->delete();
    }



}
