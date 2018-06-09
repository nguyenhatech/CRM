<?php

namespace Nh\Repositories\Tags;
use Nh\Repositories\BaseRepository;

class DbTagRepository extends BaseRepository implements TagRepository
{
    public function __construct(Tag $tag)
    {
        $this->model = $tag;
    }

}
