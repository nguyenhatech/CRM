<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Settings\SettingRepository;
use Nh\Repositories\Settings\Setting;
use Nh\Http\Transformers\SettingTransformer;

class SettingController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $setting;

    protected $validationRules = [
        'special_day'                   => 'nullable|array',
        'disable_promotion_special_day' => 'nullable|in:0,1',
        'disable_sms_special_day'       => 'nullable|in:0,1',
        'levels'                        => 'nullable|array',
        'levels.normal'                 => 'nullable|numeric',
        'levels.sliver'                 => 'nullable|numeric',
        'levels.gold'                   => 'nullable|numeric',
        'levels.diamond'                => 'nullable|numeric'
    ];

    protected $validationMessages = [
        'special_day.array'                => 'Danh sách ngày đặc biệt phải là kiểu mảng',
        'disable_promotion_special_day.in' => 'Trạng thái kích hoạt phép khuyến mãi ngày đặc biệt không đúng định dạng',
        'disable_sms_special_day.in'       => 'Trạng thái kích hoạt phép gửi tin nhắn khuyến mãi ngày đặc biệt không đúng định dạng'
    ];

    public function __construct(SettingRepository $setting, SettingTransformer $transformer)
    {
        $this->setting = $setting;
        $this->setTransformer($transformer);
        $this->checkPermission('setting');
    }

    public function getResource()
    {
        return $this->setting;
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
            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->only([
               'special_day',
               'disable_promotion_special_day',
               'disable_sms_special_day',
               'levels',
               'amount_per_score'
            ]);

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
