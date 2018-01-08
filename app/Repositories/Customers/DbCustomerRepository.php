<?php

namespace Nh\Repositories\Customers;
use Nh\Repositories\BaseRepository;

class DbCustomerRepository extends BaseRepository implements CustomerRepository
{
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
        $query = array_get($params, 'q', '');
        $model = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    public function storeOrUpdate($data) {
        $data = array_only($data, ['name', 'email', 'phone', 'home_phone', 'company_phone', 'fax', 'sex', 'facebook_id', 'google_id', 'website', 'dob', 'job', 'address', 'company_address', 'source', 'avatar']);
        $email = array_get($data, 'email', null);
        $phone = array_get($data, 'phone', null);
        // dd($this->checkExist($email, $phone));
        if ($model = $this->checkExist($email, $phone)) {
            $data = array_except($data, 'email');
            $model->fill($data)->save();
        } else {
            $model = $this->model->create($data);
        }

        $model->client()->detach([[
            'client_id'   => getCurrentUser()->id,
            'customer_id' => $model->id
        ]]);

        $model->client()->attach([[
            'client_id'   => getCurrentUser()->id,
            'customer_id' => $model->id
        ]]);
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
        if (!getCurrentUser()->isSuperAdmin()) {
            return $record->client()->detach([[
                'client_id'   => getCurrentUser()->id,
                'customer_id' => $record->id
            ]]);
        }
        return $record->delete();
    }

}