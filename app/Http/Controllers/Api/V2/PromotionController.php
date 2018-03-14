<?php

namespace Nh\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Promotions\PromotionRepository;
use Nh\Repositories\Promotions\Promotion;
use Nh\Http\Transformers\PromotionTransformer;
use Nh\Http\Controllers\Controller;

class PromotionController extends Controller
{
    use TransformerTrait, RestfulHandler;

    protected $promotion;

    protected $validationRules = [
        'client_id'         => 'required|exists:users,id',
        'code'              => 'required|max:50|unique:promotions,code',
        'type'              => 'required|numeric',
        'amount'            => 'required|numeric|min:0',
        'amount_segment'    => 'nullable|numeric|min:0',
        'amount_max'        => 'nullable|numeric|min:0',
        'quantity'          => 'nullable|numeric|min:0',
        'quantity_per_user' => 'nullable|numeric|min:0',
        'date_start'        => 'required|date_format:Y-m-d H:i:s',
        'date_end'          => 'required|date_format:Y-m-d H:i:s',
        'status'            => 'nullable|numeric',
        'description'       => 'max:191',
        'title'             => 'required'
    ];

    protected $validationMessages = [
        'client_id.required'        => 'Vui lòng nhập mã Client ID',
        'client_id.exists'          => 'Mã Client ID không tồn tại trên hệ thống',

        'code.required'             => 'Vui lòng nhập mã giảm giá',
        'code.max'                  => 'Mã giảm giá có chiều dài tối đa là 50 kí tự',
        'code.unique'               => 'Mã giảm giá này đã tồn tại trên hệ thống',

        'type.required'             => 'Vui lòng nhập kiểu giảm giá',
        'type.numeric'              => 'Kiểu giảm giá phải là kiểu số',

        'amount.required'           => 'Vui lòng nhập số lượng giảm giá',
        'amount.numeric'            => 'Số tiền hoặc phần trăm giảm giá phải là kiểu số',
        'amount.min'                => 'Số tiền hoặc phần trăm giảm giá tối thiểu là 0',

        'amount_max.required'       => 'Vui lòng nhập số tiền tối đa được giảm',
        'amount_max.numeric'        => 'Số tiền tối đa được giảm phải là kiểu số',
        'amount_max.min'            => 'Số tiền tối đa được giảm tối thiểu là 0',

        'quantity.numeric'          => 'Số lượt giảm giá phải là kiểu số',
        'quantity.min'              => 'Số lượt giảm giá tối thiểu là 0',

        'quantity_per_user.numeric' => 'Số lượt giảm giá trên mỗi user phải là kiểu số',
        'quantity_per_user.min'     => 'Số lượt giảm giá trên mỗi user tối thiểu là 0',

        'date_start.required'       => 'Vui lòng nhập ngày bắt đầu giảm giá',
        'date_start.date_format'    => 'Ngày bắt đầu giảm giá phải theo định dạng Y-m-d H:i:s',

        'date_end.required'         => 'Vui lòng nhập ngày kết thúc giảm giá',
        'date_end.date_format'      => 'Ngày kết thúc giảm giá phải theo định dạng Y-m-d H:i:s',

        'status.numeric'            => 'Trạng thái của mã giảm giá phải là kiểu số',

        'title.required'            => 'Vui lòng nhập tiêu đề',

        'description.max'           => 'Mô tả ngắn không được quá 191 ký tự'
    ];

    public function __construct(PromotionRepository $promotion, PromotionTransformer $transformer)
    {
        $this->promotion = $promotion;
        $this->setTransformer($transformer);
    }

    public function getResource()
    {
        return $this->promotion;
    }

    public function index(Request $request)
    {
        $params = $request->all();
        $pageSize = $request->get('limit', 25);
        $sort = explode(':', $request->get('sort', 'id:1'));

        $datas = $this->getResource()->getByQuery($params, $pageSize, $sort);

        return $this->successResponse($datas);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Nếu là client_id đang đăng nhập tự động gán clien_id
            // Nếu là supperadmin thì bỏ qua
            // Đang chưa có đoạn gán Role nên tạm để lấy user đang đăng nhập

            $user = getCurrentUser();
            $request['client_id'] = $user->id;

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();
            $amount = (int) array_get($params, 'amount', null);

            $type   = array_get($params, 'type', Promotion::PERCENT);

            // Nếu kiểu là giảm theo phần trăm
            if ($type == Promotion::PERCENT) {
                if ($amount > 100) {
                    return $this->errorResponse([
                        'errors' => [
                            'amount' => [
                                'Phần trăm giảm giá không được vượt quá 100%'
                            ]
                        ]
                    ]);
                }
            }
            // Check amount_segment phải nhỏ hơn amount
            $amount_segment = (int) array_get($params, 'amount_segment', null);
            if (! is_null($amount_segment) && $amount_segment >= $amount) {
                $message = $type == Promotion::PERCENT ? 'Phần trăm' : 'Số tiền';
                return $this->errorResponse([
                    'errors' => [
                        'amount_segment' => [
                            $message . ' giảm theo chặng không thể lớn hơn theo tuyến'
                        ]
                    ]
                ]);
            }

            // Check Code of User là hợp lệ
            $data = [
                'client_id' => $user->id,
                'code' => $request['code']
            ];

            $promotion = $this->getResource()->getByQuery($data)->first();
            if (!is_null($promotion)) {
                return $this->errorResponse([
                    'errors' => [
                        'code' => [
                            'Mã code đã tồn tại trên hệ thống'
                        ]
                    ]
                ]);
            }

            // UPPERCASE mã code:
            $request['code'] = strtoupper($request['code']);

            $data = $this->getResource()->store($request->all());

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

            $this->validationRules = [
                'type'              => 'required|numeric',
                'amount'            => 'required|numeric|min:0',
                'amount_max'        => 'required|numeric|min:0',
                'quantity'          => 'nullable|numeric|min:0',
                'quantity_per_user' => 'nullable|numeric|min:0',
                'date_start'        => 'required|date_format:Y-m-d H:i:s',
                'date_end'          => 'required|date_format:Y-m-d H:i:s',
                'status'            => 'nullable|numeric',
                'title'             => 'required'
            ];

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();

            $type = array_get($params, 'type', null);

            if (! is_null($type) && $type == Promotion::PERCENT) {
                $amount = array_get($params, 'amount', null);
                if ($amount > 100) {
                    return $this->errorResponse([
                        'errors' => [
                            'name' => [
                                'Số lượng phần trăm giảm giá không được vượt quá 100%'
                            ]
                        ]
                    ]);
                }
            }

            $params = array_except($params, ['client_id', 'code']);

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

    public function active(Request $request, $id)
    {
        $data = $this->getResource()->getById($id);
        if (!$data) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();
        try {
            $params['status'] = $data->status ? 0 : 1;
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

    public function uploadImage (Request $request) {
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

}
