<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Feedback\FeedbackRepository;
use Nh\Http\Transformers\FeedbackTransformer;

class FeedbackController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $feedback;

    protected $validationRules = [
        'customer_id' => 'required|exists:customers,id',
        'survey_id'   => 'required|exists:surveys,id',
        'type'        => 'required|numeric',
        'title'       => 'required|max:255',
        'answers'     => 'required|array',
        'answers.*'   => 'required|exists:answers,id',
        'note'        => 'nullable|max:255'
    ];

    protected $validationMessages = [
        'customer_id.required' => 'Mã khách hàng không được để thống',
        'customer_id.exists'   => 'Kháchh hàng không tồn tại trên hệ thống',
        'survey_id.required'   => 'Mã khảo sát không được để thống',
        'survey_id.exists'     => 'Mã khảo sát không tồn tại trên hệ thống',
        'type.required'        => 'Loại đầu vào phản hồi không được để trống',
        'type.numeric'         => 'Loại đầu vào phản hồi phải là kiểu số',
        'title.required'       => 'Tiêu đề của đánh giá không được để trống',
        'answers.required'     => 'Vui lòng nhập câu trả lời',
        'answers.array'        => 'Danh sách câu trả lời phải là kiểu mảng',
        'answers.*'            => 'Câu trả lời không tồn tại trên hệ thống'
    ];

    public function __construct(FeedbackRepository $feedback, FeedbackTransformer $transformer)
    {
        $this->feedback = $feedback;
        $this->setTransformer($transformer);
        $this->checkPermission('feedback');
    }

    public function getResource()
    {
        return $this->feedback;
    }

    public function index(Request $request)
    {
        $pageSize = $request->get('limit', 25);
        $sort = $request->get('sort', 'id:1');
        return $this->successResponse($this->getResource()->getByPaginate($pageSize, explode(':', $sort)));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->only(['customer_id','survey_id','type','title','note','status', 'answers']);

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
            $this->validationRules = [
                'customer_id' => 'nullable|exists:customers,id',
                'survey_id'   => 'nullable|exists:surveys,id',
                'type'        => 'nullable|numeric',
                'title'       => 'nullable|max:255',
                'answers'     => 'nullable|array',
                'note'        => 'nullable|max:255',
                'answers.*'   => 'required|exists:answers,id'
            ];

            $this->validate($request, $this->validationRules, $this->validationMessages);

            // Check type != 1 thì cho phép sửa
            if ($data->type === 1) {
                return $this->errorResponse([
                    'errors' => [
                        'type' => [
                            'Phản hồi của khách không thể sửa'
                        ]
                    ]
                ]);
            }
            // Chỉ cho phép cập nhật
            $params = $request->only(['title','note','status']);

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

    public function destroy($id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();

        try {
            $this->getResource()->delete($id);

            DB::commit();
            return $this->deleteResponse();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
