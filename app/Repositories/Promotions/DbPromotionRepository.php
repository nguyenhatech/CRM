<?php

namespace Nh\Repositories\Promotions;
use Nh\Repositories\BaseRepository;
use Carbon\Carbon;

class DbPromotionRepository extends BaseRepository implements PromotionRepository
{
    public function __construct(Promotion $promotion)
    {
        $this->model = $promotion;
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
        $client_id      = array_get($params, 'client_id', null);
        $code           = array_get($params, 'code', null);
        $query          = array_get($params, 'q', '');
        $status         = array_get($params, 'status', null);
        $expired_status = array_get($params, 'expired_status', null);
        $now            = Carbon::now();
        $model = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if (!is_null($status)) {
            $status = (int) $status;
            $model  = $model->where('status', $status);
        }

        if (!is_null($code)) {
            $model  = $model->where('code', $code);
        }

        if (!is_null($client_id)) {
            $model = $model->where('client_id', $client_id);
        }

        if (! is_null($expired_status)) {
            if ($expired_status) {
                $model = $model->where('date_end', '<', $now);
            }else {
                $model = $model->where('date_end', '>', $now);
            }
        }

        if (!empty($query)) {
            $query = '%' . $query . '%';
            $model = $model->where(function ($q) use ($query) {
                $q->where('code','LIKE', $query);
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

}
