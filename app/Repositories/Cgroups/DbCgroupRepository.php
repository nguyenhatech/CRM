<?php

namespace Nh\Repositories\Cgroups;
use Nh\Repositories\BaseRepository;
use Nh\Repositories\UploadTrait;

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
        $data = array_only($params, ['name', 'avatar', 'description']);
        $data['client_id'] = getCurrentUser()->id;
        $model = $this->model->create($data);
        $cgroupAttribute = \App::make('Nh\Repositories\CgroupAttributes\CgroupAttributeRepository');

        foreach ($params['filters'] as $key => $filter) {
            if ($filter == 'age_min' || $filter == 'created_at_min') {
                $attribute = [
                    'cgroup_id' => $model->id,
                    'attribute' => $key,
                    'operation' => '>=',
                    'value'     => $filter
                ];
                $cgroupAttribute->store($attribute);
            } else if ($filter == 'age_max' || $filter == 'created_at_max') {
                $attribute = [
                    'cgroup_id' => $model->id,
                    'attribute' => $key,
                    'operation' => '<=',
                    'value'     => $filter
                ];
                $cgroupAttribute->store($attribute);
            } else {
                if ($filter) {
                    $attribute = [
                        'cgroup_id' => $model->id,
                        'attribute' => $key,
                        'operation' => '=',
                        'value'     => $filter
                    ];
                    $cgroupAttribute->store($attribute);
                }
            }
        }
        return $this->getById($model->id);
    }

    public function update($id, $data)
    {
        $record = $this->getById($id);
        $record->fill($data)->save();
        $cgroupAttribute = \App::make('Nh\Repositories\CgroupAttributes\CgroupAttributeRepository');
        foreach ($record->attributes as $key => $oldFilter) {
            $newValue = array_get($data['filters'], $oldFilter->attribute, '');
            if ($newValue !== $oldFilter->value) {
                $newFilter = $oldFilter->toArray();
                $newFilter['value'] = $newValue;
                $cgroupAttribute->update(
                    $newFilter['id'],
                    $newFilter = array_only($newFilter, ['attribute', 'operation', 'value'])
                );
            }
        }
        return $this->getById($id);
    }

    public function getCustomers($id)
    {
        $cgroup = $this->getById($id);
        if ($cgroup) {
            $params = [];
            foreach ($cgroup->attributes->all() as $filter) {
                if ($filter->attribute == 'age_min' || $filter->attribute == 'age_max') {
                    array_push($params, ['attribute' => 'dob', 'operation' => $filter->operation, 'value' => Carbon::now()->subYears($filter->value)->toDateString()]);
                } else if ($filter->attribute == 'created_at_min' || $filter->attribute == 'created_at_max') {
                    $time = '';
                    if ($filter->operation == '>=' || $filter->operation == '<') {
                        $time = ' 00:00:00';
                    } else $time = ' 23:59:59';
                    array_push($params, ['attribute' => 'created_at', 'operation' => $filter->operation, 'value' => $filter->value . $time]);
                } else {
                    array_push($params, ['attribute' => $filter->attribute, 'operation' => $filter->operation, 'value' => $filter->value]);
                }
            }
            $customer = \App::make('Nh\Repositories\Customers\CustomerRepository');
            return $customer->getByGroup($params);
        }
        return false;
    }

}
