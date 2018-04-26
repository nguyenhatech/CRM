<?php

namespace Nh\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\PaymentHistories\PaymentHistoryRepository;
use Nh\Http\Transformers\PaymentHistoryTransformer;

use Nh\Http\Controllers\Controller;

class PaymentHistoryController extends Controller
{
    use TransformerTrait, RestfulHandler;

    protected $paymentHistory;

    protected $validationRules = [
        'name'           => 'required',
        'phone'          => 'required|digits_between:8,12',
        'description'    => 'required|min:5',
        'total_amount'   => 'required|numeric',
        'type'           => 'required|in:0,1',
        'status'         => 'nullable|in:0,1,2',
        'booking_id'     => 'required'
    ];

    protected $validationMessages = [
        'name.required'         => 'Tên khách hàng không được để trống',
        
        'phone.required'        => 'Số điện thoại không được để trống',
        'phone.digits_between'  => 'Số điện thoại cần nằm trong khoảng :min đến :max số',
        
        'description.required'  => 'Nội dung lịch sử thanh toán không được để trống',
        
        'total_amount.required' => 'Tổng tiền thanh toán không được để trống',
        'total_amount.numeric'  => 'Tổng tiền thanh toán phải là kiểu số',
        
        'type.required'         => 'Kiểu thanh toán không được để trống',
        'type.in'               => 'Kiểu thanh toán chỉ nhận giá trị 0,1',
        
        'status.required'       => 'Trạng thái thanh toán không được để trống',
        'status.in'             => 'Trạng thái thanh toán chỉ nhận giá trị 0,1,2',
        
        'booking_id.required'   => 'Mã booking_id không được để trống'
    ];

    public function __construct(PaymentHistoryRepository $paymentHistory, PaymentHistoryTransformer $transformer)
    {
        $this->paymentHistory = $paymentHistory;
        $this->setTransformer($transformer);
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

            $params = $request->only(['name', 'phone', 'description', 'total_amount', 'type', 'booking_id', 'details']);

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

    /**
     * Xóa mềm lịch sử thanh toán với những booking bị hủy để có thể lấy KM vào lần sau
     * @return [type] [description]
     */
    public function softDelete(Request $request)
    {
        \DB::beginTransaction();

        try {
            $this->validationRules = [
                'booking_id' => 'required|min:5'
            ];
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $data = $this->getResource()->softDelete($request['booking_id']);

            if ($data->error) {
                return $this->errorResponse($data);
            }
            
            \DB::commit();
            return $this->infoResponse($data);
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
