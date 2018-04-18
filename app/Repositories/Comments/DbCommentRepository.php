<?php

namespace Nh\Repositories\Comments;
use Nh\Repositories\BaseRepository;

class DbCommentRepository extends BaseRepository implements CommentRepository
{
    public function __construct(Comment $comment)
    {
        $this->model = $comment;
    }

    /**
     * Lưu thông tin 1 bản ghi mới
     *
     * @param  array $data
     * @return Eloquent
     */
    public function store($data)
    {
    	if (getCurrentUser()) {
    		$data['user_id'] = getCurrentUser()->id;
    	}
        $model = $this->model->create($data);
        return $this->getById($model->id);
    }

}
