<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Permissions\PermissionRepository;
use Nh\Http\Transformers\PermissionTransformer;

class PermissionController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $permission;

    protected $validationRules = [
        'name'         => 'required|unique:permissions,name,',
        'display_name' => 'required',
        'prefix'       => 'array',
        'prefix.*'     => 'required|in:',
        // 'roles'   => 'array',
        // 'roles.*' => 'required|exists:roles,id',
    ];

    protected $validationMessages = [
        'name.required'     => 'Vui lòng nhập tên',
        'name.unique'       => 'Tên đã tồn tại, vui lòng nhập tên khác',
        'prefix.array'      => 'Prefix không đúng định dạng',
        'prefix.*.required' => 'Vui lòng chọn prefix',
        'prefix.*.exists'   => 'Không tồn tại prefix này',
    ];

    public function __construct(PermissionRepository $permission, PermissionTransformer $transformer)
    {
        $this->permission = $permission;
        $this->setTransformer($transformer);
        $permission = app()->make(\Nh\Repositories\Permissions\Permission::class);
        $this->validationRules['prefix.*'] .= implode(",", array_keys($permission->getPrefixs()));
        // $this->checkPermission('permission');
        // $this->middleware("ability:superadmin,permission.index")->only(['index', 'getByRole']);
    }

    public function getResource()
    {
        return $this->permission;
    }

    public function index(Request $request) {
        $pageSize = $request->get('limit', 25);
        $sort = $request->get('sort', 'created_at:-1');

        $models = $this->getResource()->getByQuery($request->all(), $pageSize, explode(':', $sort));
        return $this->successResponse($models);
    }

    public function update(Request $request, $id) {
        DB::beginTransaction();

        try {
            $this->validationRules['name'] .= $id . ',id';
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();
            $model = $this->getResource()->update($id, $params);

            DB::commit();
            return $this->successResponse($model);
        }
        catch (\Illuminate\Validation\ValidationException $validationException) {
            DB::rollback();
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        }
        catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getByRole(Request $request)
    {
        $pageSize = $request->get('limit', -1);
        $sort = $request->get('sort', 'created_at:-1');

        $models = $this->getResource()->getByQuery($request->all(), $pageSize, explode(':', $sort));
        $models = $this->transform($models);

        $permissions = [];
        $permissions['data'] = collect($models['data'])->groupBy(function ($per) {
            $name = array_get(explode(".", $per['name']), 0, '');
            return $name;
        })->toArray();

        return $this->successResponse($permissions, false);
    }

}
