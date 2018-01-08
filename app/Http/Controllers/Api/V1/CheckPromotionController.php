<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Promotions\PromotionRepository;
use Nh\Repositories\Promotions\Promotion;

class CheckPromotionController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $promotion;

    protected $validationRules = [
        'code' => 'required|max:50'
    ];

    protected $validationMessages = [

    ];

    public function __construct(PromotionRepository $promotion)
    {
        $this->promotion = $promotion;
    }

    public function getResource()
    {
        return $this->promotion;
    }

    public function check(Request $request)
    {
        DB::beginTransaction();

        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $code = array_get($request->all(), 'code', '');

            $data = $this->getResource()->check($code);

            DB::commit();
            return $this->infoResponse($data);
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
