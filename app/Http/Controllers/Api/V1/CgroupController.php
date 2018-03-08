<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Cgroups\CgroupRepository;
use Nh\Repositories\CgroupAttributes\CgroupAttributeRepository;
use Nh\Repositories\Customers\CustomerRepository;
use Nh\Http\Transformers\CgroupTransformer;

class CgroupController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $cgroup;
    protected $customer;
    protected $cgroupAttribute;

    protected $validationRules = [
        'name'          => 'required|max:193',
        'description'   => 'max:193'
    ];

    protected $validationMessages = [
        'name.required'         =>  'Vui lòng nhập tên nhóm',
        'name.max'              =>  'Tên nhóm quá dài',
        'description.max'       =>  'Mô tả quá dài'
    ];

    public function __construct(CgroupRepository $cgroup, CgroupTransformer $transformer, CustomerRepository $customer, CgroupAttributeRepository $cgroupAttribute)
    {
    	$this->cgroup = $cgroup;
        $this->customer = $customer;
        $this->cgroupAttribute = $cgroupAttribute;
    	$this->setTransformer($transformer);
        $this->checkPermission('cgroup');
    }

    public function getResource()
    {
    	return $this->cgroup;
    }

    public function index(Request $request)
    {
        $pageSize = $request->get('limit', 25);
        $sort = $request->get('sort', 'created_at:-1');

        $models = $this->getResource()->getByQuery($request->all(), $pageSize, explode(':', $sort));
        return $this->successResponse($models);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();
            $data = $this->getResource()->store($params);

            DB::commit();
            return $this->successResponse($data);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $params = $request->all();
            $model = $this->getResource()->update($id, $params);

            DB::commit();
            return $this->successResponse($model);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getCustomers($id)
    {
        $cgroup = $this->getResource()->getById($id);
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
            $customers = $this->customer->getByGroup($params);
            return $this->successResponse($customers);
        } else {
            return $this->notFoundResponse();
        }
    }

    public function uploadAvatar (Request $request) {
        try {
            $this->validate($request, [
                'files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'file' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120'
            ], [
                'files.*.image'    => 'File upload không đúng định dạng',
                'files.*.mimes'    => 'File upload phải là 1 trong các định dạng: :values',
                'files.*.max'      => 'File upload không thể vượt quá :max KB',
                'file.image'    => 'File upload không đúng định dạng',
                'file.mimes'    => 'File upload phải là 1 trong các định dạng: :values',
                'file.max'      => 'File upload không thể vượt quá :max KB',
            ]);
            if ($request->file('file')) {
                $image = $request->file('file');
            } else {
                $image = $request->file('files')[0];
            }
            return $this->getResource()->upload($image);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        $data = $this->getResource()->getById($id);
        if (!$data) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();

        try {
            $data->customers()->detach();
            $data->attributes()->delete();
            $this->getResource()->delete($id);

            DB::commit();
            return $this->deleteResponse();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
