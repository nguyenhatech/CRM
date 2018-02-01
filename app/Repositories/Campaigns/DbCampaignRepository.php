<?php

namespace Nh\Repositories\Campaigns;
use Nh\Repositories\BaseRepository;

class DbCampaignRepository extends BaseRepository implements CampaignRepository
{
    public function __construct(Campaign $campaign)
    {
        $this->model = $campaign;
    }

    public function getById($id)
    {
        if (!is_numeric($id)) {
            $id = strtolower($id);
            $id = \Hashids::decode($id)[0];
        }
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
        $query       = array_get($params, 'q', '');
        $status      = array_get($params, 'status', null);
        $client_id   = array_get($params, 'client_id', null);
        $template_id = array_get($params, 'template_id', null);
        $cgroup_id   = array_get($params, 'cgroup_id', null);

        $model = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%");
            });
        }

        if (! is_null($status)) {
            $model = $model->where('status', $status);
        }

        if (! is_null($client_id)) {
            $model = $model->where('client_id', $client_id);
        }

        if (! is_null($template_id)) {
            $model = $model->where('template_id', $template_id);
        }

        if (! is_null($cgroup_id)) {
            $model = $model->where('cgroup_id', $cgroup_id);
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

}
