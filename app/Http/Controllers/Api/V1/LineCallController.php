<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\LineCalls\LineCallRepository;
use Nh\Http\Transformers\LineCallTransformer;
use DB;

class LineCallController extends ApiController
{
	use TransformerTrait, RestfulHandler;

    protected $lineCall;

    protected $validationRules = [
        'line'           => 'required|unique:line_calls',
        'phone_account'  => 'unique:line_calls',
        'email_account'  => 'unique:line_calls|nullable|email'
    ];

    protected $validationMessages = [
        'line.required'           => 'Đầu số nội bộ là bắt buộc',
        'line.unique'             => 'Đã tồn tại đầu số nội bộ',
        'phone_account.unique'    => 'Đã tồn tại số điện thoại này',
        'email_account.unique'    => 'Đã tồn tại email này',
        'email_account.email'     => 'Email chưa đúng định dạng'
    ];

    public function __construct(LineCallRepository $lineCall, LineCallTransformer $transformer)
    {
    	$this->lineCall = $lineCall;
    	$this->setTransformer($transformer);
    	$this->checkPermission('linecall');
    }

    public function getResource()
    {
        return $this->lineCall;
    }

    public function index(Request $request)
    {
        $params = $request->all();
        $pageSize = $request->get('limit', 25);
        $sort = explode(':', $request->get('sort', 'id:1'));

        $datas = $this->getResource()->getByQuery($params, $pageSize, $sort);

        return $this->successResponse($datas);
    }

    public function update(Request $request, $id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();

        try {
            $this->validationRules = [
                'line'           => 'unique:line_calls,line,' . $data->id . ',id',
                'phone_account'  => $data->phone_account ? 'unique:line_calls,phone_account,' . $data->id . ',id' : 'unique:line_calls',
                'email_account'  => $data->email_account ? 'nullable|email|unique:line_calls,email_account' . $data->id . ',id' : 'unique:line_calls|nullable|email'
            ];
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $model = $this->getResource()->update($id, $request->all());

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
}
