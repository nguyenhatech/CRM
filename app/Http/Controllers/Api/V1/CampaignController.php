<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Campaigns\Campaign;

use Nh\Repositories\Campaigns\CampaignRepository;
use Nh\Http\Transformers\CampaignTransformer;

use Nh\Jobs\SendEmailCampaign;
use Nh\Jobs\SendSMSCampaign;

class CampaignController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $campaign;

    protected $validationRules = [
        'template'    => 'required',
        'cgroup_id'   => 'nullable|required_without_all:customers',
        'name'        => 'required|max:191',
        'description' => 'nullable',
        'status'      => 'nullable|numeric',
        'customers'   => 'nullable|required_without_all:cgroup_id|array',
        'target_type' => 'required|numeric'
    ];

    protected $validationMessages = [
        'template.required'         => 'Chưa nhập mẫu email',
        'cgroup_id.required'        => 'Chưa chọn nhóm khách hàng',
        'customers.required'        => 'Chưa chọn khách hàng',
        'customers.array'           => 'Danh sách khách hàng chưa đúng định dạng.',
        'name.required'             => 'Chưa nhập tên',
        'name.max'                  => 'Tên không được quá 191 kí tự',
        'status.numeric'            => 'Trạng thái sai định dạng',
        'target_type.required'      => 'Chưa chọn loại mục tiêu',
        'target_type.numeric'       => 'Chọn đối tượng mục tiêu chưa đúng'
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
            $params['client_id'] = getCurrentUser()->id;

            if (array_key_exists('cgroup_id', $params)) {
                $params['cgroup_id'] = convert_uuid2id($params['cgroup_id']);
            }
            if (array_key_exists('template_id', $params)) {
                $params['template_id'] = convert_uuid2id($params['template_id']);
            }
            if (array_key_exists('customers', $params)) {
                $customers = [];
                foreach ($params['customers'] as $uuid) {
                    array_push($customers, convert_uuid2id($uuid));
                }
                $params['customers'] = $customers;
            }

            $data = $this->getResource()->store($params);

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

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();

            $params = array_only($params, ['name', 'description', 'start_date', 'end_date', 'status', 'cgroup_id', 'template_id', 'template', 'sms_template', 'target_type', 'period', 'customers']);
            if (array_key_exists('template_id', $params)) {
                $params['template_id'] = convert_uuid2id($params['template_id']);
            }
            if (array_key_exists('cgroup_id', $params)) {
                $params['cgroup_id'] = convert_uuid2id($params['cgroup_id']);
            }
            if (array_key_exists('template_id', $params)) {
                $params['template_id'] = convert_uuid2id($params['template_id']);
            }
            if (array_key_exists('customers', $params)) {
                $customers = [];
                foreach ($params['customers'] as $uuid) {
                    array_push($customers, convert_uuid2id($uuid));
                }
                $params['customers'] = $customers;
            }

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

    public function sendEmail($id)
    {
        $campaign = $this->campaign->getById($id);

        if ($campaign) {
            $customers = [];

            if ($campaign->target_type == Campaign::GROUP_TARGET) {
                $customers = $campaign->cgroup->customers;
            } else {
                $customers = $campaign->customers;
            }
            try {
                $job = new SendEmailCampaign($campaign, $customers);
                dispatch($job)->delay(now()->addSeconds(1));
            } catch (\Exception $e) {
                throw $e;
            }
            return $this->infoResponse([]);
        } else {
            return $this->notFoundResponse();
        }
    }

    public function sendSMS(Request $request, $id)
    {
        try {
            $this->validate(
                $request, 
                ['content'          => 'required'], 
                ['content.required' => 'Nội dung tin nhắn không được để trống']
            );
            $campaign = $this->campaign->getById($id);

            if ($campaign) {
                $customers = [];
                if ($campaign->target_type == Campaign::GROUP_TARGET) {
                    $customers = $campaign->cgroup->customers;
                } else {
                    $customers = $campaign->customers;
                }
                try {
                    $job = new SendSMSCampaign($campaign, $customers, $request->content);
                    dispatch($job)->delay(now()->addSeconds(1));
                } catch (\Exception $e) {
                    throw $e;
                }
                return $this->infoResponse([]);
            } else {
                return $this->notFoundResponse();
            }
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

}
