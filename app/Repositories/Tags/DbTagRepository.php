<?php

namespace Nh\Repositories\Tags;
use Nh\Repositories\BaseRepository;

class DbTagRepository extends BaseRepository implements TagRepository
{
    public function __construct(Tag $tag)
    {
        $this->model = $tag;
    }

    public function store($params)
    {
        $lists = ['#229954', '#B7950B', '#196F3D', '#7D3C98', '#E74C3C', '#3498DB'];
        $key                = array_rand($lists);
        $params['slug']     = str_slug($params['name'], '-');
        $params['color']    = $lists[$key];
        $model              = $this->model->create($params);
        return $this->getById($model->id);
    }
}
