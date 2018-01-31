<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Campaigns\CampaignRepository;
use Nh\Http\Transformers\CampaignTransformer;

class CampaignController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $campaign;

    protected $validationRules = [
        'client_id'   => 'required|numeric',
        'template_id' => 'required|numeric',
        'cgroup_id'   => 'required|numeric',
        'name'        => 'required|max:191',
        'description' => 'nullable',
        'start_date'  => 'required|date_format:Y-m-d H:i:s',
        'end_date'    => 'required|date_format:Y-m-d H:i:s',
        'status'      => 'nullable|numeric'
    ];

    protected $validationMessages = [

    ];

    public function __construct(CampaignRepository $campaign, CampaignTransformer $transformer)
    {
        $this->campaign = $campaign;
        $this->setTransformer($transformer);
    }

    public function getResource()
    {
        return $this->campaign;
    }

    public function index(Request $request)
    {
        $params = $request->all();
        $pageSize = $request->get('limit', 25);
        $sort = explode(':', $request->get('sort', 'id:1'));

        $datas = $this->getResource()->getByQuery($params, $pageSize, $sort);

        return $this->successResponse($datas);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();

            $data = $this->getResource()->store($request->all());

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
                'name'        => 'nullable|max:191',
                'description' => 'nullable',
                'start_date'  => 'nullable|date_format:Y-m-d H:i:s',
                'end_date'    => 'nullable|date_format:Y-m-d H:i:s',
                'status'      => 'nullable|numeric'
            ];

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();

            $params = array_only($params, ['name', 'description', 'start_date', 'end_date', 'status']);

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

}
