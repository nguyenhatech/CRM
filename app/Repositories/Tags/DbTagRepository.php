<?php

namespace Nh\Repositories\Tags;
use Nh\Repositories\BaseRepository;

class DbTagRepository extends BaseRepository implements TagRepository
{
    public function __construct(Tag $tag)
    {
        $this->model = $tag;
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
        $type     = array_get($params, 'type', '');
        $model      = $this->model;

        if (!empty($sorting) && array_key_exists(1, $sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($type != '') {
            $model->where('type', $type);
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('name	', 'like', "%{$query}%")
                    ->orWhere('description	', 'like', "%{$query}%");
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

}
