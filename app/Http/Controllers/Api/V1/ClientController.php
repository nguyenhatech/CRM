<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Users\UserRepository;
use Nh\Http\Transformers\UserTransformer;

class ClientController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $client;

    protected $validationRules = [
        'name'     => 'required|min:5|max:255',
        'email'    => 'required|email|unique:users,email',
        'avatar'   => 'max:255',
        'password' => 'required|min:6|confirmed',
        'status'   => 'boolean',
        'roles'    => 'array',
        'roles.*'  => 'required|exists:roles,id'
    ];

    protected $validationMessages = [
        'name.required'        => 'Vui lòng nhập tên chủ shop',
        'name.min'             => 'Tên chủ shop cần lớn hơn :min kí tự',
        'name.max'             => 'Tên chủ shop cần nhỏ hơn :max kí tự',
        'email.required'       => 'Vui lòng nhập email',
        'email.email'          => 'Email không đúng định dạng',
        'email.unique'         => 'Email đã tồn tại, vui lòng nhập email khác',
        'avatar.max'           => 'Ảnh đại diện cần nhỏ hơn :max kí tự',
        'password.required'    => 'Vui lòng nhập mật khẩu',
        'password.min'         => 'Mật khẩu cần lớn hơn :min kí tự',
        'password.confirmed'   => 'Mật khẩu xác nhận không khớp',
        'status.boolean'       => 'Trạng thái không đúng định dạng',
        'roles.array'          => 'Dữ liệu nhóm quyền không đúng định dạng',
        'roles.*.required'     => 'Vui lòng chọn nhóm quyền',
        'roles.*.exists'       => 'Không tồn tại nhóm quyền này trên hệ thống',
    ];

    public function __construct(UserRepository $user, UserTransformer $transformer)
    {
        $this->client = $user;
        $this->setTransformer($transformer);
        $this->checkPermission('user');
    }

    public function getResource()
    {
        return $this->client;
    }

    public function index(Request $request)
    {
        $pageSize = $request->get('limit', 25);
        $sort = $request->get('sort', 'created_at:-1');

        $models = $this->getResource()->getByQuery($request->all(), $pageSize, explode(':', $sort));
        return $this->successResponse($models);
    }

}
