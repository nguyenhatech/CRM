<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Promotions\PromotionRepository;
use Nh\Repositories\Promotions\Promotion;
use Nh\Http\Transformers\PromotionTransformer;

class PromotionController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $promotion;

    protected $validationRules = [
        'client_id'         => 'required|exists:users,id',
        'code'              => 'required|max:50|unique:promotions,code',
        'type'              => 'required|numeric',
        'amount'            => 'required|numeric|min:0',
        'amount_max'        => 'required|numeric|min:0',
        'quantity'          => 'nullable|numeric|min:0',
        'quantity_per_user' => 'nullable|numeric|min:0',
        'date_start'        => 'required|date_format:Y-m-d H:i:s',
        'date_end'          => 'required|date_format:Y-m-d H:i:s',
        'status'            => 'nullable|numeric'
    ];

    protected $validationMessages = [

    ];

    public function __construct(PromotionRepository $promotion, PromotionTransformer $transformer)
    {
        $this->promotion = $promotion;
        $this->setTransformer($transformer);
    }

    public function getResource()
    {
        return $this->promotion;
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

            $type   = array_get($params, 'type', null);

            if (! is_null($type) && $type == Promotion::PERCENT) {
                $amount = (int) array_get($params, 'amount', null);
                if ($amount > 100) {
                    return $this->errorResponse([
                        'errors' => 'Số lượng phần trăm giảm giá không được vượt quá 100%'
                    ]);
                }
            }

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
                'type'              => 'required|numeric',
                'amount'            => 'required|numeric|min:0',
                'amount_max'        => 'required|numeric|min:0',
                'quantity'          => 'nullable|numeric|min:0',
                'quantity_per_user' => 'nullable|numeric|min:0',
                'date_start'        => 'required|date_format:Y-m-d H:i:s',
                'date_end'          => 'required|date_format:Y-m-d H:i:s',
                'status'            => 'nullable|numeric'
            ];

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();

            $type = array_get($params, 'type', null);

            if (! is_null($type) && $type == Promotion::PERCENT) {
                $amount = array_get($params, 'amount', null);
                if ($amount > 100) {
                    return $this->errorResponse([
                        'errors' => 'Số lượng phần trăm giảm giá không được vượt quá 100%'
                    ]);
                }
            }

            $params = array_except($params, ['client_id', 'code']);

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
