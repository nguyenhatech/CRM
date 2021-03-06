<?php

namespace Nh\Repositories\Cgroups;
use Nh\Repositories\BaseRepository;
use Nh\Repositories\UploadTrait;
use Illuminate\Support\Carbon;

class DbCgroupRepository extends BaseRepository implements CgroupRepository
{
	use UploadTrait;

    public function __construct(Cgroup $cgroup)
    {
        $this->model = $cgroup;
    }

    /**
     * Lấy thông tin 1 bản ghi xác định bởi ID
     *
     * @param  integer $id ID bản ghi
     * @return Eloquent
     */
    public function getById($id)
    {
        if (!is_numeric($id)) {
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
        $query = array_get($params, 'q', '');
        $model = $this->model;

        if (!empty($sorting)) {
            $model = $model->orderBy($sorting[0], $sorting[1] > 0 ? 'ASC' : 'DESC');
        }

        if ($query != '') {
            $model = $model->where(function($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%")
                	->orWhere('uuid', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        return $size < 0 ? $model->get() : $model->paginate($size);
    }

    public function store($params) {
        $data = array_only($params, ['name', 'avatar', 'description', 'client_id', 'filter']);
        if (getCurrentUser()) {
            $data['client_id'] = getCurrentUser()->id;
        }
        $model = $this->model->create($data);
        $cgroupAttribute = \App::make('Nh\Repositories\CgroupAttributes\CgroupAttributeRepository');

        // Lưu filter
        if (array_key_exists('filters', $params)) {
            $filters = array_only($params['filters'], ['level', 'city_id', 'job', 'age_min', 'age_max', 'created_at_min', 'created_at_max', 'score_min', 'score_max']);
            foreach ($filters as $key => $filter) {
                $attribute = [
                    'cgroup_id' => $model->id,
                    'attribute' => $key,
                    'operation' => '=',
                    'value'     => $filter
                ];
                $cgroupAttribute->store($attribute);
            }
        }

        // Lưu danh sách khách hàng
        // Loại 1: lấy theo filter
        // Loại 2: Theo danh sách đã add
        if ($params['method_input_type'] == 1) {
            // Nếu group là loại biến động thì không lưu danh sách
            $filterGroup = array_get($params, 'filter', false);
            if (!$filterGroup) {
                $limitNumber = array_get($params['filters'], 'limit_number', -1);
                $sorting = array_get($params['filters'], 'limit_sort', []);
                $customerList = $this->getCustomers($model->id, $limitNumber, $sorting);
                $customers  = array_pluck($customerList, 'id');
                $this->syncCustomers($model, $customers);
            }
        } else {
            $customers = [];
            foreach ($params['customers'] as $key => $uuid) {
                array_push($customers, convert_uuid2id($uuid));
            }
            $this->syncCustomers($model, $customers);
        }

        return $this->getById($model->id);
    }

    public function update($id, $data)
    {
        $record = $this->getById($id);
        $record->fill($data)->save();
        // Tạm thời ẩn đi update filter
        // $cgroupAttribute = \App::make('Nh\Repositories\CgroupAttributes\CgroupAttributeRepository');
        // foreach ($record->attributes as $key => $oldFilter) {
        //     $newValue = array_get($data['filters'], $oldFilter->attribute, '');
        //     if ($newValue !== $oldFilter->value) {
        //         $newFilter = $oldFilter->toArray();
        //         $newFilter['value'] = $newValue;
        //         $cgroupAttribute->update(
        //             $newFilter['id'],
        //             $newFilter = array_only($newFilter, ['attribute', 'operation', 'value'])
        //         );
        //     }
        // }
        return $this->getById($id);
    }

    /**
     * Lấy danh sách khách hàng theo filter của group
     * @param  [type]  $id   [description]
     * @param  integer $size [description]
     * @return [type]        [description]
     */
    public function getCustomers($id, $size = -1, $sorting = [])
    {
        $cgroup = $this->getById($id);
        if ($cgroup) {
            $params = [];
            foreach ($cgroup->attributes->all() as $filter) {
                if (!is_null($filter->value) && $filter->attribute != 'page') {
                    switch ($filter->attribute) {
                        case 'age_min':
                            array_push($params, ['attribute' => 'dob', 'operation' => '<=', 'value' => Carbon::now()->subYears($filter->value)->toDateString()]);
                            break;
                        case 'age_max':
                            array_push($params, ['attribute' => 'dob', 'operation' => '>=', 'value' => Carbon::now()->subYears($filter->value)->toDateString()]);
                            break;
                        case 'score_min':
                            array_push($params, ['attribute' => 'point', 'operation' => '>=', 'value' => $filter->value]);
                            break;
                        case 'score_max':
                            array_push($params, ['attribute' => 'point', 'operation' => '<=', 'value' => $filter->value]);
                            break;
                        case 'created_at_min':
                            array_push($params, ['attribute' => 'created_at', 'operation' => '>=', 'value' => $filter->value . ' 00:00:00']);
                            break;
                        case 'created_at_max':
                            array_push($params, ['attribute' => 'created_at', 'operation' => '<=', 'value' => $filter->value . ' 23:59:59']);
                            break;
                        default:
                            array_push($params, ['attribute' => $filter->attribute, 'operation' => $filter->operation, 'value' => $filter->value]);
                            break;
                    }
                }
            }
            $customer = \App::make('Nh\Repositories\Customers\CustomerRepository');
            return $customer->getByGroup($params, $size, $sorting);
        }
        return [];
    }

    /**
     * Đồng bộ relation
     * @param  [type] $model     [description]
     * @param  [type] $customers [description]
     * @return [type]            [description]
     */
    public function syncCustomers($model, $customers)
    {
        return $model->customers()->sync($customers);
    }

}
