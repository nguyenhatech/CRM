<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
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
        'total_amount'   => 'numeric',
        'total_point'    => 'numeric',
        'promotion_code' => 'max:191|exists:promotions,code'
    ];

    protected $validationMessages = [
        'email.required_without_all' => 'Email hoặc số điện thoại không được để trống',
        'email.email'                => 'Email không đúng định dạng',
        'email.max'                  => 'Email cần nhỏ hơn :max kí tự',
        'phone.required_without_all' => 'Số điện thoại hoặc email không được để trống',
        'phone.digits_between'       => 'Số điện thoại cần nằm trong khoảng :min đến :max số',
        'total_amount.numeric'       => 'Số tiền giao dịch không đúng định dạng',
        'total_point.numeric'        => 'Số điểm thưởng giao dịch không đúng định dạng',
        'promotion_code.max'         => 'Mã khuyến mãi có chiều dài tối đa 191 kí tự',
        'promotion_code.exists'      => 'Mã khuyến mãi không tồn tại trên hệ thống'
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
