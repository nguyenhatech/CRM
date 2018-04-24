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

    public function removeCustomer($id, $customerId)
    {
        $group    = $this->getResource()->getById($id);
        $customer = $this->customer->getById($customerId);
        if (!$group || !$customer) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();
        try {
            $group->customers()->detach($customer->id);
            DB::commit();
            return $this->deleteResponse();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function addCustomer($id, $customerId)
    {
        $group    = $this->getResource()->getById($id);
        $customer = $this->customer->getById($customerId);
        if (!$group || !$customer) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();
        try {
            $group->customers()->attach($customer->id);
            DB::commit();
            return $this->infoResponse($customer);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getCustomerList($id)
    {
        $group = $this->getResource()->getById($id);
        if ($group) {
            if ($group->filter) {
                return $this->infoResponse($this->cgroup->getCustomers($group->id, 10));
            }
            return $this->infoResponse($this->customer->groupCustomer($group->id));
        }
        return $this->notFoundResponse();
    }
}
