<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\PaymentHistories\PaymentHistoryRepository;
use Nh\Http\Transformers\PaymentHistoryTransformer;

class PaymentHistoryController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $paymentHistory;

    protected $validationRules = [
        'email'          => 'required_without_all:phone|email|max:255',
        'phone'          => 'required_without_all:email|digits_between:8,12',
        'description'    => 'required|min:5',
        'total_amount'   => 'required|numeric',
        'type'           => 'required|in:0,1',
        'status'         => 'nullable|in:0,1,2',
        'booking_id'     => 'required'
    ];

    protected $validationMessages = [
        // 'name.required'              => 'Tên khách hàng không được để trống',

        'email.required_without_all' => 'Email hoặc số điện thoại không được để trống',
        'email.email'                => 'Email không đúng định dạng',
        'email.max'                  => 'Email cần nhỏ hơn :max kí tự',

        'phone.required_without_all' => 'Số điện thoại hoặc email không được để trống',
        'phone.digits_between'       => 'Số điện thoại cần nằm trong khoảng :min đến :max số',

        'description.required'       => 'Nội dung lịch sử thanh toán không được để trống',

        'total_amount.required'      => 'Tổng tiền thanh toán không được để trống',
        'total_amount.numeric'       => 'Tổng tiền thanh toán phải là kiểu số',

        'type.required'              => 'Kiểu thanh toán không được để trống',
        'type.in'                    => 'Kiểu thanh toán chỉ nhận giá trị 0,1',

        'status.required'            => 'Trạng thái thanh toán không được để trống',
        'status.in'                  => 'Trạng thái thanh toán chỉ nhận giá trị 0,1,2',

        'booking_id.required'        => 'Mã booking_id không được để trống'
    ];

    public function __construct(PaymentHistoryRepository $paymentHistory, PaymentHistoryTransformer $transformer)
    {
        $this->paymentHistory = $paymentHistory;
        $this->setTransformer($transformer);
        $this->checkPermission('payment_history');
    }

    public function getResource()
    {
        return $this->paymentHistory;
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

            $params = $request->only(['name', 'phone', 'description', 'total_amount', 'type', 'booking_id', 'details', 'email', 'status']);
            $params['flag'] = false;
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


    public function updatePaymentHistory(Request $request)
    {
        \DB::beginTransaction();

        try {

            $this->validationRules = [
                'booking_id' => 'required',
                'status'     => 'required|in:0,1,2'
            ];

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->only(['booking_id', 'status']);

            $model = $this->getResource()->updatePaymentHistory($params);

            if ($model->error) {
                return $this->errorResponse($model);
            }

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

    public function update(Request $request, $id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }
        $this->validationRules = [];
        \DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $model = $this->getResource()->update($id, $request->all());

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
