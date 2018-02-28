<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Roles\RoleRepository;
use Nh\Http\Transformers\RoleTransformer;

class RoleController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $role;

    protected $validationRules = [
        'name'          => 'required|unique:roles,name,',
        'display_name'  => 'required',
        'users'         => 'array',
        'users.*'       => 'required|exists:users,uuid',
        'permissions'   => 'array',
        'permissions.*' => 'required|exists:permissions,id',
    ];

    protected $validationMessages = [
        'name.required'          => 'Vui lòng nhập tên',
        'name.unique'            => 'Tên đã tồn tại, vui lòng nhập tên khác',
        'users.array'           => 'Admin không đúng định dạng',
        'users.*.required'      => 'Vui lòng chọn users',
        'users.*.exists'        => 'Không tồn tại users này',
        'permissions.array'      => 'Permission không đúng định dạng',
        'permissions.*.required' => 'Vui lòng chọn permission',
        'permissions.*.exists'   => 'Không tồn tại permission này',
    ];

    public function __construct(RoleRepository $role, RoleTransformer $transformer)
    {
        $this->role = $role;
        $this->setTransformer($transformer);
        // $this->checkPermission('role');
    }

    public function getResource()
    {
        return $this->role;
    }

    public function index(Request $request) {
        $pageSize = $request->get('limit', 25);
        $sort = $request->get('sort', 'created_at:-1');

        $models = $this->getResource()->getByQuery($request->all(), $pageSize, explode(':', $sort));
        return $this->successResponse($models);
    }

    public function update(Request $request, $id) {
        if (!$data = $this->getResource()->getById($id)) {
            return $this->notFoundResponse();
        }

        \DB::beginTransaction();

        try {
            $this->validationRules['name'] .= $id . ',id';
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();
            $model = $this->getResource()->update($id, $params);

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

}
