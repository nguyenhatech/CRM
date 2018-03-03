<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Cgroups\CgroupRepository;
use Nh\Repositories\Customers\CustomerRepository;
use Nh\Http\Transformers\CgroupTransformer;

class CgroupController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $cgroup;
    protected $customer;

    protected $validationRules = [
        'name'          => 'required|max:193',
        'description'   => 'max:193'
    ];

    protected $validationMessages = [
        'name.required'         =>  'Vui lòng nhập tên nhóm',
        'name.max'              =>  'Tên nhóm quá dài',
        'description.max'       =>  'Mô tả quá dài'
    ];

    public function __construct(CgroupRepository $cgroup, CgroupTransformer $transformer, CustomerRepository $customer)
    {
    	$this->cgroup = $cgroup;
        $this->customer = $customer;
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

            $data = $this->getResource()->store($request->all());
            $customers = [];
            foreach ($request->customers as $key => $customer) {
                $customers[$key] = convert_uuid2id($customer);
            }
            $data->customers()->attach($customers);

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

    public function addCustomer(Request $request, $id)
    {
        $this->validationRules = [
            'name'            => 'required|min:5|max:255',
            'email'           => 'nullable|required_without_all:phone|email|max:255',
            'phone'           => 'nullable|required_without_all:email|digits_between:8,12',
            'home_phone'      => 'nullable|digits_between:8,12',
            'company_phone'   => 'nullable|digits_between:8,12',
            'website'         => 'nullable|url',
            'dob'             => 'nullable|date_format:Y-m-d',
            'job'             => 'max:255',
            'address'         => 'max:255',
            'company_address' => 'max:255'
        ];
        $this->validationMessages = [
            'name.required'              => 'Tên không được để trống',
            'name.min'                   => 'Tên cần lớn hơn :min kí tự',
            'name.max'                   => 'Tên cần nhỏ hơn :max kí tự',
            'email.required_without_all' => 'Email hoặc số điện thoại không được để trống',
            'email.email'                => 'Email không đúng định dạng',
            'email.max'                  => 'Email cần nhỏ hơn :max kí tự',
            'phone.required_without_all' => 'Số điện thoại hoặc email không được để trống',
            'phone.digits_between'       => 'Số điện thoại cần nằm trong khoảng :min đến :max số',
            'home_phone.digits_between'  => 'Số điện thoại bàn cần nằm trong khoảng :min đến :max số',
            'home_phone.company_phone'   => 'Số điện thoại cơ quan cần nằm trong khoảng :min đến :max số',
            'website.url'                => 'Website không đúng định dạng',
            'dob.date_format'            => 'Ngày sinh không đúng định dạng Y-m-d',
            'job'                        => 'Nghề nghiệp cần nhỏ hơn :max kí tự',
            'address'                    => 'Địa chỉ cần nhỏ hơn :max kí tự',
            'company_address'            => 'Địa chỉ cơ quan cần nhỏ hơn :max kí tự',
        ];

        DB::beginTransaction();

        try {
            $customerId = array_get($request->all(), 'customer_id', 0);
            if ($customerId) {
                $customerId = convert_uuid2id($customerId);
            } else {
                $this->validate($request, $this->validationRules, $this->validationMessages);
                $customer = $this->customer->storeOrUpdate($request->all());
                $customerId = $customer->id;
            }

            $group = $this->getResource()->getById($id);
            if ($group) {
                $group->customers()->attach($customerId);
            }
            DB::commit();
            return $this->infoResponse([
                    'Thêm khách hàng vào nhóm thành công!'
                ]);
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

    public function removeCustomer(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $customerId = array_get($request->all(), 'customer_id', 0);
            $customerId = convert_uuid2id($customerId);
            $group = $this->getResource()->getById($id);
            if ($group) {
                $group->customers()->detach($customerId);
            }
            DB::commit();
            return $this->infoResponse([
                    'Xóa khách hàng khỏi nhóm thành công!'
                ]);
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
            $this->getResource()->delete($id);

            DB::commit();
            return $this->deleteResponse();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
