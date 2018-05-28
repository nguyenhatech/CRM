<?php

namespace Nh\Repositories\CampaignSmsIncomings;
use Nh\Repositories\BaseRepository;

class DbCampaignSmsIncomingRepository extends BaseRepository implements CampaignSmsIncomingRepository
{
    public function __construct(CampaignSmsIncoming $campaignSmsIncoming)
    {
        $this->model = $campaignSmsIncoming;
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
        $query = array_get($params, 'q', '');

        $model = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('content', 'like', "%{$query}%")
                		 ->orWhere('phone', 'like', '%{$query}%');
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

}
