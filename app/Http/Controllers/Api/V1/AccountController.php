<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\TransformerTrait;
use Nh\Http\Controllers\Api\ResponseHandler;

use Nh\Repositories\Users\UserRepository;
use Nh\Http\Transformers\UserTransformer;

class AccountController extends ApiController
{
    use TransformerTrait, ResponseHandler;
    protected $account;

    protected $validationRules = [
        'name'         => 'required|min:5|max:255',
        'avatar'       => 'max:255'
    ];

    protected $validationMessages = [
        'name.required'         => 'Vui lòng nhập tên',
        'name.min'              => 'Tên cần lớn hơn :min kí tự',
        'name.max'              => 'Tên cần nhỏ hơn :max kí tự',
        'avatar.max'            => 'Ảnh đại diện cần nhỏ hơn :max kí tự'
    ];

    public function __construct(UserRepository $user, UserTransformer $transformer)
    {
        $this->account = $user;
        $this->setTransformer($transformer);
        $this->checkPermission('user');
    }

    public function getResource()
    {
        return $this->account;
    }

    public function index()
    {
        return $this->successResponse(getCurrentUser());
    }

    public function changePassword(Request $request)
    {
        $this->validationRules = [
            'old_password' => 'required|min:6|max:255',
            'password'     => 'required|min:6|max:255|confirmed',
        ];

        $this->validationMessages = [
            'old_password.required' => 'Vui lòng nhập mật khẩu cũ',
            'old_password.min'      => 'Mật khẩu cũ cần lớn hơn :min kí tự',
            'old_password.max'      => 'Mật khẩu cũ cần nhỏ hơn :max kí tự',
            'password.required'     => 'Vui lòng nhập mật khẩu mới',
            'password.min'          => 'Mật khẩu mới cần lớn hơn :min kí tự',
            'password.max'          => 'Mật khẩu mới cần nhỏ hơn :max kí tự',
            'password.confirmed'    => 'Xác nhận mật khẩu mới không chính xác',
        ];

        \DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();
            $params = array_only($params, ['password', 'old_password']);

            if (!\Hash::check($params['old_password'], getCurrentUser()->password)) {
                return $this->errorResponse([
                    'errors' => ['Mật khẩu cũ không đúng!']
                ]);
            }

            $model = $this->getResource()->update(getCurrentUser()->id, $params);

            \DB::commit();
            return $this->successResponse($model);
        }
        catch (\Illuminate\Validation\ValidationException $validationException) {
            \DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        }
        catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    public function updateProfile(Request $request)
    {

        \DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();
            $params = array_only($params, ['name', 'avatar']);

            $model = $this->getResource()->update(getCurrentUser()->id, $params);

            \DB::commit();
            return $this->successResponse($model);
        }
        catch (\Illuminate\Validation\ValidationException $validationException) {
            \DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        }
        catch (\Exception $e) {
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
