<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;
use Nh\Repositories\Promotions\PromotionRepository;
use Nh\Repositories\Promotions\Promotion;
use Nh\Http\Transformers\PromotionTransformer;
use Excel;

class PromotionController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $promotion;

    protected $validationRules = [
        'client_id' => 'required|exists:users,id',
        'code' => 'required|max:50',
        'type' => 'required|numeric',
        'cgroup_id' => 'nullable|exists:cgroups,uuid',
        'amount' => 'required|numeric|min:0',
        'amount_segment' => 'nullable|numeric|min:0',
        'amount_max' => 'nullable|numeric|min:0',
        'quantity' => 'nullable|numeric|min:0',
        'quantity_per_user' => 'nullable|numeric|min:0',
        'date_start' => 'required|date_format:Y-m-d H:i:s',
        'date_end' => 'required|date_format:Y-m-d H:i:s',
        'status' => 'nullable|numeric',
        'description' => 'max:191',
        'title' => 'required',
    ];

    protected $validationMessages = [
        'client_id.required' => 'Vui lòng nhập mã Client ID',
        'client_id.exists' => 'Mã Client ID không tồn tại trên hệ thống',

        'code.required' => 'Vui lòng nhập mã giảm giá',
        'code.max' => 'Mã giảm giá có chiều dài tối đa là 50 kí tự',
        // 'code.unique' => 'Mã giảm giá này đã tồn tại trên hệ thống',

        'type.required' => 'Vui lòng nhập kiểu giảm giá',
        'type.numeric' => 'Kiểu giảm giá phải là kiểu số',

        'target_type.required' => 'Vui lòng nhập kiểu giảm giá',
        'target_type.numeric' => 'Kiểu giảm giá phải là kiểu số',

        'cgroup_id.exists' => 'Mã nhóm khách hàng không tồn tại trên hệ thống',

        'amount.required' => 'Vui lòng nhập số lượng giảm giá',
        'amount.numeric' => 'Số tiền hoặc phần trăm giảm giá phải là kiểu số',
        'amount.min' => 'Số tiền hoặc phần trăm giảm giá tối thiểu là 0',

        'amount_max.numeric' => 'Số tiền tối đa được giảm phải là kiểu số',
        'amount_max.min' => 'Số tiền tối đa được giảm tối thiểu là 0',

        'quantity.numeric' => 'Số lượt giảm giá phải là kiểu số',
        'quantity.min' => 'Số lượt giảm giá tối thiểu là 0',

        'quantity_per_user.numeric' => 'Số lượt giảm giá trên mỗi user phải là kiểu số',
        'quantity_per_user.min' => 'Số lượt giảm giá trên mỗi user tối thiểu là 0',

        'date_start.required' => 'Vui lòng nhập ngày bắt đầu giảm giá',
        'date_start.date_format' => 'Ngày bắt đầu giảm giá phải theo định dạng Y-m-d H:i:s',

        'date_end.required' => 'Vui lòng nhập ngày kết thúc giảm giá',
        'date_end.date_format' => 'Ngày kết thúc giảm giá phải theo định dạng Y-m-d H:i:s',

        'status.numeric' => 'Trạng thái của mã giảm giá phải là kiểu số',

        'title.required' => 'Vui lòng nhập tiêu đề',

        'description.max' => 'Mô tả ngắn không được quá 191 ký tự',
    ];

    public function __construct(PromotionRepository $promotion, PromotionTransformer $transformer)
    {
        $this->promotion = $promotion;
        $this->setTransformer($transformer);
        $this->checkPermission('promotion');
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
            if ($request['target_type']) {
                $request['target_type'] = implode(',', $request['target_type']);
            }

            if ($request['merchant']) {
                $request['merchants'] = implode(',', $request['merchant']);
            }

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();
            $amount = (int) array_get($params, 'amount', null);

            $type = array_get($params, 'type', Promotion::PERCENT);

            $quantity = (int) array_get($params, 'quantity', 0);
            $quantityPerUser = (int) array_get($params, 'quantity_per_user', 0);

            if($quantity != 0 && $quantity < $quantityPerUser) {
                return $this->errorResponse([
                    'errors' => [
                        'quantity' => [
                            'Tổng số lượt phải lớn hơn số lượt sử dụng',
                        ]
                    ],
                ]);
            }

            // Nếu kiểu là giảm theo phần trăm
            if ($type == Promotion::PERCENT) {
                if ($amount > 100) {
                    return $this->errorResponse([
                        'errors' => [
                            'amount' => [
                                'Phần trăm giảm giá không được vượt quá 100%',
                            ],
                        ],
                    ]);
                }
            }

            // Check amount_segment phải nhỏ hơn amount
            $amount_segment = (int) array_get($params, 'amount_segment', null);
            if (!is_null($amount_segment) && $amount_segment > $amount) {
                $message = $type == Promotion::PERCENT ? 'Phần trăm' : 'Số tiền';
                return $this->errorResponse([
                    'errors' => [
                        'amount_segment' => [
                            $message.' giảm theo chặng không thể lớn hơn theo tuyến',
                        ],
                    ],
                ]);
            }

            // UPPERCASE mã code:
            $request['code'] = strtoupper($request['code']);

            // Conver mã UUID của Cgroup về ID
            $cgroud_id = array_get($params, 'cgroup_id', null);

            if (!is_null($cgroud_id)) {
                if (\Hashids::decode($cgroud_id)[0]) {
                    $params['cgroup_id'] = \Hashids::decode($cgroud_id)[0];
                } else {
                    $params['cgroup_id'] = 0;
                }
            } else {
                $params['cgroup_id'] = 0;
            }
            $data = $this->getResource()->store($params);

            DB::commit();

            return $this->successResponse($data);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollback();

            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
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
                'type' => 'required|numeric',
                // 'target_type'       => 'required|numeric',
                'cgroup_id' => 'nullable|exists:cgroups,uuid',
                'amount' => 'required|numeric|min:0',
                'amount_max' => 'nullable|numeric|min:0',
                'quantity' => 'nullable|numeric|min:0',
                'quantity_per_user' => 'nullable|numeric|min:0',
                'date_start' => 'required|date_format:Y-m-d H:i:s',
                'date_end' => 'required|date_format:Y-m-d H:i:s',
                'status' => 'nullable|numeric',
            ];

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();
            $params['target_type'] = $params['target_type'] ? implode(',', $params['target_type']) : '';
            $params['merchants'] = $params['merchant'] ? implode(',', $params['merchant']) : '';

            $type = array_get($params, 'type', null);

            $quantity = (int) array_get($params, 'quantity', 0);
            $quantityPerUser = (int) array_get($params, 'quantity_per_user', 0);

            if($quantity != 0 && $quantity < $quantityPerUser) {
                return $this->errorResponse([
                    'errors' => [
                        'quantity' => [
                            'Tổng số lượt phải lớn hơn số lượt sử dụng',
                        ]
                    ],
                ]);
            }

            if (!is_null($type) && $type == Promotion::PERCENT) {
                $amount = array_get($params, 'amount', null);
                if ($amount > 100) {
                    return $this->errorResponse([
                        'errors' => [
                            'name' => [
                                'Số lượng phần trăm giảm giá không được vượt quá 100%',
                            ],
                        ],
                    ]);
                }
            }

            $params = array_except($params, ['client_id', 'code']);

            // Conver mã UUID của Cgroup về ID
            $cgroud_id = array_get($params, 'cgroup_id', null);

            if (!is_null($cgroud_id)) {
                if (\Hashids::decode($cgroud_id)[0]) {
                    $params['cgroup_id'] = \Hashids::decode($cgroud_id)[0];
                } else {
                    $params['cgroup_id'] = 0;
                }
            } else {
                $params['cgroup_id'] = 0;
            }

            $model = $this->getResource()->update($id, $params);

            DB::commit();

            return $this->successResponse($model);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollback();

            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
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
                'exception' => $validationException->getMessage(),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function uploadImage(Request $request)
    {
        try {
            $this->validate($request, [
                'files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'file' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            ], [
                'files.*.image' => 'File upload không đúng định dạng',
                'files.*.mimes' => 'File upload phải là 1 trong các định dạng: :values',
                'files.*.max' => 'File upload không thể vượt quá :max KB',
                'file.image' => 'File upload không đúng định dạng',
                'file.mimes' => 'File upload phải là 1 trong các định dạng: :values',
                'file.max' => 'File upload không thể vượt quá :max KB',
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
                'exception' => $validationException->getMessage(),
            ]);
        }
    }

    /**
     * Thông kê số lượt và số khách dùng mã.
     *
     * @param int $id Promotion ID
     *
     * @return [type] [description]
     */
    public function statisticQuantityUsed($id)
    {
        $statistic = $this->promotion->usedStatistic($id);
        if (!empty($statistic->all())) {
            return $this->infoResponse($statistic->first());
        }

        return $this->infoResponse([]);
    }

    /**
     * Lấy danh sách khách hàng đã dùng mã.
     *
     * @param string $value [description]
     *
     * @return [type] [description]
     */
    public function getListCustomerUsed($id, Request $request)
    {
        $customers = $this->promotion->usedCustomers($id, $request->all());
        if (!is_null($customers) && !empty($customers->all()) && !is_null($customers->first()->id)) {
            return $this->infoResponse($customers);
        }

        return $this->infoResponse([]);
    }

    /**
     * Lấy danh sách khách hàng chưa dùng mã.
     *
     * @param string $value [description]
     *
     * @return [type] [description]
     */
    public function getListCustomerNotUse($id)
    {
        $customers = $this->promotion->notUsedCustomers($id);
        if ($customers && !is_null($customers->first()->id)) {
            return $this->infoResponse($customers);
        }

        return $this->infoResponse([]);
    }

    /**
     * Thống kê sử dụng mã khuyến mại theo thời gian.
     *
     * @param string $value [description]
     *
     * @return [type] [description]
     */
    public function statisticByTime(Request $request, $id)
    {
        $promotion = $this->promotion->getById($id);
        if ($promotion) {
            $statistics = $this->promotion->statisticByTime($id);
            $days = [];
            $series = [];
            foreach ($statistics as $key => $data) {
                array_push($days, $data['date']);
                array_push($series, $data['total']);
            }

            return $this->infoResponse([
                'days' => $days,
                'series' => [['name' => 'Lượt sử dụng', 'data' => $series]],
            ]);
        }

        return $this->notFoundResponse();
    }

    /**
     * Lấy mã khuyến mãi Free.
     *
     * @return [type] [description]
     */
    public function getFree()
    {
        $data = $this->getResource()->getPromotionFree();

        return $this->successResponse($data);
    }

    /**
     * Export excel.
     *
     * @param [type] $id [description]
     *
     * @return [type] [description]
     */
    public function exportExcel(Request $request)
    {
        $uuid = $request->get('uuid');
        try {
            $promotions = $this->getResource()->usedCustomers($uuid, $request->all());

            if (!$promotions) {
                return [];
            }

            $pathToFile = Excel::create('khach_hang_su_dung_ma_khuyen_mai '.time(), function ($excel) use ($promotions) {
                $excel->sheet('sheet1', function ($sheet) use ($promotions) {
                    // Set auto size for sheet
                    $sheet->setAutoSize(true);
                    $sheet->row(
                        1,
                        [
                            '#',
                            'Họ tên',
                            'Điện thoại',
                            'Email',
                            'Số lượt dùng',
                            'Số lượt hủy',
                        ]
                    );
                    // Start at row 2
                    $rowPointer = 2;
                    foreach ($promotions as $promotion) {
                        $sheet->row(
                            $rowPointer,
                            [
                                $rowPointer - 1,
                                $promotion->name,
                                $promotion->phone,
                                $promotion->email,
                                $promotion->total_used,
                                $promotion->total_cancel,
                            ]
                        );
                        // Move on to the next row
                        ++$rowPointer;
                    }
                });
            })->store('xlsx', storage_path('/app/public/excels'), true);

            $path = "excels/{$pathToFile['file']}";

            return $this->infoResponse([
                'full' => env('APP_URL').'/storage/'.$path,
                'path' => env('APP_URL').'/storage/excels',
            ]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage(),
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function stupid(Request $request){
        \Log::info('Check promotion stupid: '. time());

        $code = $request->code;
        $data =  ($code && count($code) > 0) ? Promotion::where('number_ticket_applied', '>', 0)->where('status', Promotion::ENABLE)->whereIn('code', (array) $code)->get(['code', 'number_ticket_applied']) : []; 

        return $this->successResponse($data);
    }
}
