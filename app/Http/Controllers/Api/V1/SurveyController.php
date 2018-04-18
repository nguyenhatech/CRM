<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Surveys\SurveyRepository;
use Nh\Http\Transformers\SurveyTransformer;

class SurveyController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $survey;

    protected $validationRules = [
        'title'       => 'required|max:191',
        'questions'   => 'required|array',
        'questions.*' => 'required|exists:questions,id'
    ];

    protected $validationMessages = [
        'title.required'     => 'Vui lòng nhập tên của cuộc khảo sát',
        'questions.required' => 'Vui lòng nhập danh sách câu hỏi',
        'questions.array'    => 'Danh sách câu hỏi phải là kiểu mảng',
        'questions.*'        => 'Mã câu hỏi không tồn tại trên hệ thống'
    ];

    public function __construct(SurveyRepository $survey, SurveyTransformer $transformer)
    {
        $this->survey = $survey;
        $this->setTransformer($transformer);
        $this->checkPermission('survey');
    }

    public function getResource()
    {
        return $this->survey;
    }

    public function index(Request $request)
    {
        $pageSize = $request->get('limit', 25);
        $sort = $request->get('sort', 'id:1');
        return $this->successResponse($this->getResource()->getByPaginate($pageSize, explode(':', $sort)));
    }

    public function show($id)
    {
        if ($data = $this->getResource()->getById($id)) {
            return $this->successResponse($data);
        }
        return $this->notFoundResponse();
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->only(['title', 'questions']);

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
                'title'       => 'nullable|max:191',
                'questions'   => 'nullable|array',
                'questions.*' => 'required|exists:questions,id'
            ];

            $this->validationMessages = [
                'title.required'     => 'Vui lòng nhập tên của cuộc khảo sát',
                'questions.required' => 'Vui lòng nhập danh sách câu hỏi',
                'questions.array'    => 'Danh sách câu hỏi phải là kiểu mảng',
                'questions.*'        => 'Mã câu hỏi không tồn tại trên hệ thống'
            ];

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->only(['title', 'questions']);

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
