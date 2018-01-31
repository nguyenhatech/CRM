<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;
use Nh\Http\Controllers\Api\V1\ApiController;

use Nh\Repositories\EmailTemplates\EmailTemplateRepository;
use Nh\Http\Transformers\EmailTemplateTransformer;

class EmailTemplateController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $emailTemplate;

    protected $validationRules = [
        'name'      => 'required|min:5|max:255|unique:email_templates,name,',
        'template'  => 'required',
        'client_id' => 'required|exists:users,id',
    ];

    protected $validationMessages = [
        'client_id.required' => 'Vui lòng nhập mã Client ID',
        'client_id.exists'   => 'Mã Client ID không tồn tại trên hệ thống',
        'name.required'      => 'Tên không được để trống',
        'name.min'           => 'Tên cần lớn hơn :min kí tự',
        'name.max'           => 'Tên cần nhỏ hơn :max kí tự',
        'name.unique'        => 'Tên đã tồn tại, vui lòng nhập tên khác',
        'template.required'  => 'Mẫu email không được để trống',
    ];

    public function __construct(EmailTemplateRepository $emailTemplate, EmailTemplateTransformer $transformer)
    {
        $this->emailTemplate = $emailTemplate;
        $this->setTransformer($transformer);
    }

    public function getResource()
    {
        return $this->emailTemplate;
    }

    public function index(Request $request)
    {
        $pageSize = $request->get('limit', 25);
        $sort = $request->get('sort', 'created_at:-1');

        $params = $request->all();
        $params['client_id'] = convert_uuid2id($params['client_id']);

        $models = $this->getResource()->getByQuery($params, $pageSize, explode(':', $sort));
        return $this->successResponse($models);
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();

        try {

            $user = getCurrentUser();
            $request['client_id'] = $user->id;

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $data = $this->getResource()->store($request->all());

            \DB::commit();
            return $this->successResponse($data);
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

    public function update(Request $request, $id)
    {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        \DB::beginTransaction();

        try {
            $this->validationRules['name'] .= $id;
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $data = $request->all();
            $data = array_except($data, ['client_id', 'name', 'template']);
            $model = $this->getResource()->update($id, $data);

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

    public function upload (Request $request) {
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

            return $this->getResource()->upload($image, false);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        }
    }

}
