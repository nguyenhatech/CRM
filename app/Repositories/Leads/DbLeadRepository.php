<?php

namespace Nh\Repositories\Leads;
use Nh\Repositories\BaseRepository;

class DbLeadRepository extends BaseRepository implements LeadRepository
{
    public function __construct(Lead $lead)
    {
        $this->model = $lead;
    }

    public function getById($id)
    {
        if (!is_numeric($id)) {
            $id = strtolower($id);
            $id = convert_uuid2id($id);
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
        $customer_id = array_get($params, 'customer_id', null);
        $owner_id    = array_get($params, 'owner_id', null);
        $source  	 = array_get($params, 'source', null);

        $model = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                			->orWhere('phone', 'like', "%{$query}%")
                			->orWhere('email', 'like', "%{$query}%");
            });
        }

        if (! is_null($status)) {
            $model = $model->where('status', $status);
        }

        if (! is_null($customer_id)) {
            $model = $model->where('customer_id', $customer_id);
        }

        if (! is_null($owner_id)) {
            $model = $model->where('owner_id', $owner_id);
        }

        if (! is_null($source)) {
            $model = $model->where('source', $source);
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    public function storeOrUpdate($data) {
        $data = array_only($data, ['name', 'dob', 'gender', 'owner_id', 'customer_id', 'phone', 'email', 'address', 'city_id', 'ip', 'facebook', 'quality', 'source', 'utm_source']);
        $email = array_get($data, 'email', null);
        $phone = array_get($data, 'phone', null);
        // dd($this->checkExist($email, $phone));
        if ($model = $this->checkExist($email, $phone)) {
            $data = array_except($data, 'email');
            $model->fill($data)->save();
        } else {
            // Check for run job
            if (getCurrentUser()) {
                $data['owner_id'] = getCurrentUser()->id;
            }
            $model = $this->model->create($data);
        }

        return $this->getById($model->id);
    }

    public function checkExist($email = null, $phone = null) {
        $model = $this->model;

        if ($email && !$phone) {
            $model = $model->where('email', $email)->first();
        } else if ($phone && !$email) {
            $model = $model->where('phone', $phone)->first();
        } else if ($email && $phone) {
            $exitEmail = $model->where('email', $email)->first();
            $exitPhone = $model->where('phone', $phone)->first();
            if ($exitPhone && !$exitEmail) {
                $model = $exitPhone;
            } else {
                $model = $exitEmail;
            }
        } else {
            $model = null;
        }
        return $model;
    }

}
