<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Customers\CustomerRepository;
use Nh\Http\Transformers\CustomerTransformer;

class CustomerController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $customer;

    protected $validationRules = [
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

    protected $validationMessages = [
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

    public function __construct(CustomerRepository $customer, CustomerTransformer $transformer)
    {
        $this->customer = $customer;
        $this->setTransformer($transformer);
    }

    public function getResource()
    {
        return $this->customer;
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
        \DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $data = $this->getResource()->storeOrUpdate($request->all());

            \DB::commit();
            return $this->successResponse($data);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            \DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        \DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $data = $request->all();
            $data = array_except($data, ['email', 'level']);
            $model = $this->getResource()->update($id, $data);

            \DB::commit();
            return $this->successResponse($model);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            \DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

}
