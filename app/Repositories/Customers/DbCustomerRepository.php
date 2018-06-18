<?php

namespace Nh\Repositories\Customers;
use Nh\Repositories\BaseRepository;
use Nh\Repositories\UploadTrait;

class DbCustomerRepository extends BaseRepository implements CustomerRepository
{
    use UploadTrait;

    public function __construct(Customer $customer)
    {
        $this->model = $customer;
    }

    /**
     * Lấy thông tin 1 bản ghi xác định bởi ID
     *
     * @param  integer $id ID bản ghi
     * @return Eloquent
     */

    public function getById($id)
    {
        $id = convert_uuid2id($id);
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
        $query      = array_get($params, 'q', '');
        $group_id   = array_get($params, 'group_id', '');
        $level      = array_get($params, 'level', '');
        $startDate  = array_get($params, 'start_date', null);
        $endDate    = array_get($params, 'end_date', null);
        $model      = $this->model;

        if (!empty($sorting) && array_key_exists(1, $sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($group_id != '') {
            $model = $model->whereHas('groups', function ($model) use ($group_id) {
                $model->where('uuid', $group_id);
            });
        }

        if ($level != '') {
            $model = $model->where('level', $level);
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            });
        }

        if ($startDate) {
            $model = $model->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $model = $model->where('created_at', '<=', $endDate);
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    /**
     * Lấy tất cả khách hàng theo filter của group
     *
     * @param  array $filters Mảng các điều kiện where
     * @return Illuminate\Pagination\Paginator
     */
    public function getByGroup($filters, $size = -1, $sorting = [])
    {
        $model = $this->model;

        // Join payment
        $model = $model->leftJoin('payment_histories', function($join) {
            $join->on('payment_histories.customer_id', '=', 'customers.id')->whereIn('payment_histories.status', Customer::PAYMENT_STATATUS);
        });
        $model->selectRaw('customers.uuid, customers.name, customers.phone, customers.email, customers.id, customers.created_at, customers.job, customers.city_id, customers.level, customers.dob, sum(payment_histories.total_point) as point');
        $model = $model->groupBy('customers.uuid', 'customers.name', 'customers.phone', 'customers.email', 'customers.uuid', 'customers.id', 'customers.created_at', 'customers.job', 'customers.city_id', 'customers.level', 'customers.city_id', 'customers.dob');

        foreach ($filters as $key => $filter) {
            $model = $model->where('customers.' . $filter['attribute'], $filter['operation'], $filter['value']);
        }
        // Sort trường hợp lấy giới hạn
        if (!empty($sorting)) {
            if ($sorting[0] == 'score') {
                $model = $model->orderBy('point', $sorting[1] > 0 ? 'ASC' : 'DESC');
            } else {
                $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
            }
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    public function groupCustomer($groupId, $size = 10)
    {
        $model = $this->model->whereHas('groups', function ($model) use ($groupId) {
            $model = $model->where('id', $groupId);
        });

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    public function storeOrUpdate($data) {
        // return $data;
        $data = array_only($data, ['name', 'email', 'phone', 'home_phone', 'company_phone', 'fax', 'sex', 'facebook_id', 'google_id', 'website', 'dob', 'job', 'address', 'company_address', 'source', 'avatar', 'city_id', 'client_id', 'tags']);
        $email          = array_get($data, 'email', null);
        $phone          = array_get($data, 'phone', null);
        $data['name']   = array_get($data, 'name', $phone);
        if ($model = $this->checkExist($email, $phone)) {
            $data = array_except($data, 'phone');
            $model->fill($data)->save();
        } else {
            $model = $this->model->create($data);
        }

        // Check for run job
        if (getCurrentUser()) {
            $data['client_id'] = getCurrentUser()->id;
        }

        $model->client()->detach([[
            'client_id'   => $data['client_id'],
            'customer_id' => $model->id
        ]]);

        $model->client()->attach([[
            'client_id'   => $data['client_id'],
            'customer_id' => $model->id
        ]]);

        // Add tags customers
        $this->syncTags($model, $data);

        event(new \Nh\Events\InfoCustomer($model));

        return $this->getById($model->id);
    }

    public function update($id, $data)
    {
        $customer = $this->getById($id);

        $customer->fill($data)->save();

        // Add tags customers
        $this->syncTags($customer, $data);

        return $customer;
    }

    /**
     * [syncTags description]
     * @param  [type] $model [description]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function syncTags($model, $data)
    {
        $tags = array_get($data, 'tags', []);

        $model->tags()->sync($tags);
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
        if (!getCurrentUser()->isAdmin()) {
            return $record->client()->detach([[
                'client_id'   => getCurrentUser()->id,
                'customer_id' => $record->id
            ]]);
        }
        return $record->delete();
    }

    /**
     * Lấy dữ liệu export excel
     *
     * @param  integer $size Số bản ghi mặc định 25
     * @param  array $sorting Sắp xếp
     * @return Illuminate\Pagination\Paginator
     */
    public function exportExcel($params, $size = -1, $sorting = [])
    {
        $groups   = array_get($params, 'groups', '');
        $levels   = array_get($params, 'levels', '');
        $fields   = array_get($params, 'fields', '');
        $model    = $this->model->select($fields);

        if (!empty($sorting) && array_key_exists(1, $sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if (!empty($groups)) {
            $model = $model->whereHas('groups', function ($model) use ($groups) {
                $model->whereIn('uuid', $groups);
            });
        }

        if (!empty($levels)) {
            $model = $model->whereIn('level', $levels);
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }
}
