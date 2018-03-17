<?php

namespace Nh\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Promotions\PromotionRepository;
use Nh\Repositories\Promotions\Promotion;
use Nh\Http\Controllers\Controller;

class CheckPromotionController extends Controller
{
    use TransformerTrait, RestfulHandler;

    protected $promotion;

    protected $validationRules = [
        'code'         => 'required|max:50',
        'ticket_money' => 'required|numeric|min:1000',
        'type'         => 'required|in:1,2',
        'target_type'  => 'required|in:0,1,2,3',
        'email'        => 'nullable|max:50',
        'phone'        => 'required|digits_between:8,12'
    ];

    protected $validationMessages = [
        'code.required'         => 'Vui lòng nhập mã Code cần kiểm tra',
        'code.max'              => 'Mã code có chiều dài tối đa chỉ 50 kí tự',
        'ticket_money.required' => 'Vui lòng nhập tổng số tiền đơn hàng',
        'ticket_money.numeric'  => 'Số tiền đơn hàng phải là kiểu số',
        'ticket_money.min'      => 'Số tiền đơn hàng tối thiểu là 1000 đồng',
        'type.required'         => 'Hình thức khách đi không thể bỏ trống',
        'type.in'               => 'Hình thức khách đi chỉ có thể là theo tuyến hoặc chặng',
        'target_type.required'  => 'Hạng xe không thể để trống',
        'target_type.in'        => 'Hạng xe không hợp lệ',
        'phone.required'        => 'Vui lòng nhập mã Phone'
    ];

    public function __construct(PromotionRepository $promotion)
    {
        $this->promotion = $promotion;
    }

    public function getResource()
    {
        return $this->promotion;
    }

    public function check(Request $request)
    {
        DB::beginTransaction();

        \Log::info('ERP ' .getCurrentUser()->name . ' : ' . json_encode($request->all()));
        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $data = $this->getResource()->check($request->all());

            if ($data->error) {
                return $this->errorResponse($data);
            }

            DB::commit();
            return $this->infoResponse($data);
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

}
