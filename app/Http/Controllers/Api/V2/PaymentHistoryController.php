<?php

namespace Nh\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
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
        'phone'          => 'required|digits_between:8,12',
        'description'    => 'required|min:5',
        'total_amount'   => 'numeric',
        'total_point'    => 'numeric',
    ];

    protected $validationMessages = [
        'email.required_without_all' => 'Email hoặc số điện thoại không được để trống',
        'email.email'                => 'Email không đúng định dạng',
        'email.max'                  => 'Email cần nhỏ hơn :max kí tự',
        'phone.required_without_all' => 'Số điện thoại hoặc email không được để trống',
        'phone.digits_between'       => 'Số điện thoại cần nằm trong khoảng :min đến :max số',
        'total_amount.numeric'       => 'Số tiền giao dịch không đúng định dạng',
        'total_point.numeric'        => 'Số điểm thưởng giao dịch không đúng định dạng',
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
