<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Users\UserRepository;
use Nh\Http\Transformers\UserTransformer;

class UserController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $user;

    protected $validationRules = [
        'name'         => 'required|min:5|max:255',
        'email'        => 'required|email|unique:users,email',
        'phone'        => 'nullable|digits_between:8,12|unique:users,phone',
        'status'       => 'boolean',
        'roles'        => 'array',
        'roles.*'      => 'required|exists:roles,id'
    ];

    protected $validationMessages = [
        'name.required'         => 'Vui lòng nhập tên',
        'name.min'              => 'Tên cần lớn hơn :min kí tự',
        'name.max'              => 'Tên cần nhỏ hơn :max kí tự',
        'email.required'        => 'Vui lòng nhập email',
        'email.email'           => 'Email không đúng định dạng',
        'email.unique'          => 'Email đã tồn tại, vui lòng nhập email khác',
        'phone.required'        => 'Vui lòng nhập số điện thoại',
        'phone.digits_between'  => 'Số điện thoại cần nằm trong khoảng :min đến :max số',
        'phone.unique'          => 'Số điện thoại đã tồn tại trên hệ thống',
        'avatar.max'            => 'Ảnh đại diện cần nhỏ hơn :max kí tự',
        'password.required'     => 'Vui lòng nhập mật khẩu',
        'password.min'          => 'Mật khẩu cần lớn hơn :min kí tự',
        'status.boolean'        => 'Trạng thái không đúng định dạng',
        'roles.array'           => 'Dữ liệu nhóm quyền không đúng định dạng',
        'roles.*.required'      => 'Vui lòng chọn nhóm quyền',
        'roles.*.exists'        => 'Không tồn tại nhóm quyền này trên hệ thống'
    ];

    public function __construct(UserRepository $user, UserTransformer $transformer)
    {
        $this->user = $user;
        $this->setTransformer($transformer);
        // $this->checkPermission('user');
    }

    public function getResource()
    {
        return $this->user;
    }

    public function index(Request $request)
    {
        $pageSize = $request->get('limit', 25);
        $sort = $request->get('sort', 'created_at:-1');

        $models = $this->getResource()->getByQuery($request->all(), $pageSize, explode(':', $sort));
        return $this->successResponse($models);
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();

        $request['client_id'] = getCurrentUser()->id;

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $data = $request->all();
            $data['password'] = bcrypt($data['password']);
            $data = $this->getResource()->store($data);

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

        $this->validationRules = array_except($this->validationRules, ['email']);
        $this->validationRules['phone'] = 'nullable|digits_between:8,12';

        \DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $data = $request->all();
            $data = array_only($data, ['name', 'phone', 'status', 'avatar', 'roles']);
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

    public function active($id)
    {
        $model = $this->getResource()->getById($id);
        if (!$model) {
            return $this->notFoundResponse();
        }

        \DB::beginTransaction();

        try {
            $params['status'] = $model->status ? 0 : 1;
            $model = $this->getResource()->update($id, $params);

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

    public function resetPassword (Request $request, $id) {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        $this->validationRules = [
            'password'  => 'required|min:6'
        ];
        $this->validationMessages = [
            'password.required'     => 'Vui lòng nhập mật khẩu',
            'password.min'          => 'Mật khẩu cần lớn hơn :min kí tự'
        ];

        \DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $data = $request->all();
            $data['password'] = bcrypt($data['password']);
            $data = array_only($data, ['password']);
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

    public function uploadAvatar (Request $request) {
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
