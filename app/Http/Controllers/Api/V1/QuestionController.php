<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Questions\QuestionRepository;
use Nh\Http\Transformers\QuestionTransformer;

class QuestionController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $question;

    protected $validationRules = [
        'content'           => 'required',
        'likes'             => 'required|array',
        'likes.*.content'   => 'required|max:255',
        'unlikes'           => 'required|array',
        'unlikes.*.content' => 'required|max:255'
    ];

    protected $validationMessages = [
        'content.required'           => 'Vui lòng nhập nội dung câu hỏi',
        'likes.required'             => 'Vui lòng nhập điều khách có thể thích ứng với câu hỏi',
        'likes.array'                => 'Điều khách có thể thích ứng với câu hỏi phải là kiểu mảng',
        'likes.*.content.required'   => 'Nội dung điều khách thích không được bỏ trống',
        'likes.*.content.max'        => 'Nội dung điều khách thích tối đa 255 kí tự',

        'unlikes.required'           => 'Vui lòng nhập điều khách có thể không thích ứng với câu hỏi',
        'unlikes.array'              => 'Điều khách có thể không thích ứng với câu hỏi phải là kiểu mảng',
        'unlikes.*.content.required' => 'Nội dung điều khách không thích không được bỏ trống',
        'unlikes.*.content.max'      => 'Nội dung điều khách không thích tối đa 255 kí tự'
    ];

    public function __construct(QuestionRepository $question, QuestionTransformer $transformer)
    {
        $this->question = $question;
        $this->setTransformer($transformer);
        $this->checkPermission('question');
    }

    public function getResource()
    {
        return $this->question;
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

            $params = $request->only(['content', 'likes', 'unlikes']);

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
                'content'               => 'nullable',
                'status'                => 'nullable',

                'likes'                 => 'nullable|array',
                'likes.*.id'            => 'nullable|numeric|exists:answers,id',
                'likes.*.question_id'   => 'nullable|numeric|exists:questions,id',
                'likes.*.content'       => 'required|max:255',
                'likes.*.status'        => 'required',

                'unlikes'               => 'nullable|array',
                'unlikes.*.id'          => 'nullable|numeric|exists:answers,id',
                'unlikes.*.question_id' => 'nullable|numeric|exists:questions,id',
                'unlikes.*.content'     => 'required|max:255',
                'unlikes.*.status'      => 'required'
            ];

            $this->validationMessages = [
                'content.required'           => 'Vui lòng nhập nội dung câu hỏi',
                'status.required'            => 'Vui lòng nhập trạng thái câu hỏi',
                'status.numeric'             => 'Trạng thái câu hỏi phải là kiểu số',
                'likes.required'             => 'Vui lòng nhập điều khách có thể thích ứng với câu hỏi',
                'likes.array'                => 'Điều khách có thể thích ứng với câu hỏi phải là kiểu mảng',
                'likes.*.content.required'   => 'Nội dung điều khách thích không được bỏ trống',
                'likes.*.content.max'        => 'Nội dung điều khách thích tối đa 255 kí tự',
                'likes.*.status.required'    => 'Trạng thái nội dung điều khách thích không được bỏ trống',
                'likes.*.status.numeric'     => 'Trạng thái nội dung điều khách thích phải là kiểu số',

                'unlikes.required'           => 'Vui lòng nhập điều khách có thể không thích ứng với câu hỏi',
                'unlikes.array'              => 'Điều khách có thể không thích ứng với câu hỏi phải là kiểu mảng',
                'unlikes.*.content.required' => 'Nội dung điều khách không thích không được bỏ trống',
                'unlikes.*.content.max'      => 'Nội dung điều khách không thích tối đa 255 kí tự',
                'unlikes.*.status.required'  => 'Trạng thái nội dung điều khách thích không được bỏ trống',
                'unlikes.*.status.numeric'   => 'Trạng thái nội dung điều khách thích phải là kiểu số'
            ];

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->only(['content', 'status', 'likes', 'unlikes']);

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
