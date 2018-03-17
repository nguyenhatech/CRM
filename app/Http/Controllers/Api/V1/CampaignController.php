<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\Campaigns\Campaign;

use Nh\Repositories\Cgroups\CgroupRepository;
use Nh\Repositories\Customers\CustomerRepository;
use Nh\Repositories\Campaigns\CampaignRepository;
use Nh\Http\Transformers\CampaignTransformer;

use Nh\Jobs\SendEmailCampaign;
use Nh\Jobs\SendSMSCampaign;

use Nh\Repositories\Helpers\SpeedSMSAPI;
use Illuminate\Support\Carbon;

class CampaignController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $campaign;
    protected $cgroup;
    protected $customer;

    protected $validationRules = [
        'template'    => 'required',
        'name'        => 'required|max:191',
        'description' => 'nullable',
        'status'      => 'nullable|numeric',
        'customers'   => 'array',
        'target_type' => 'required|numeric'
    ];

    protected $validationMessages = [
        'template.required'         => 'Chưa nhập mẫu email',
        'customers.array'           => 'Danh sách khách hàng chưa đúng định dạng.',
        'name.required'             => 'Chưa nhập tên',
        'name.max'                  => 'Tên không được quá 191 kí tự',
        'status.numeric'            => 'Trạng thái sai định dạng',
        'target_type.required'      => 'Chưa chọn loại mục tiêu',
        'target_type.numeric'       => 'Chọn đối tượng mục tiêu chưa đúng'
    ];

    public function __construct(CampaignRepository $campaign, CgroupRepository $cgroup, CustomerRepository $customer, CampaignTransformer $transformer)
    {
        $this->campaign = $campaign;
        $this->cgroup = $cgroup;
        $this->customer = $customer;
        $this->setTransformer($transformer);
        $this->checkPermission('campaign');
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
            // TH chọn theo nhóm
            if (array_key_exists('cgroup_id', $params)) {
                $params['cgroup_id'] = convert_uuid2id($params['cgroup_id']);
            }
            // TH chọn khách thủ công. Convert id để sync
            if (array_key_exists('customers', $params) && $params['target_type'] == Campaign::MANUAL_TARGET) {
                $customers = [];
                foreach ($params['customers'] as $uuid) {
                    array_push($customers, convert_uuid2id($uuid));
                }
                $params['customers'] = $customers;
            }
            // TH chọn khách bằng filters. Tạo group nếu có filters
            if (array_key_exists('filters', $params) && $params['target_type'] == Campaign::FILTER_TARGET) {
                $cgroupParams = ['name' => 'Chiến dịch ' . $params['name']];
                $cgroupParams['filters'] = $params['filters'];
                $cgroup = $this->cgroup->store($cgroupParams);
                $params['cgroup_id'] = $cgroup->id;
            }

            $data = $this->getResource()->store($params);
            // Nếu setup thời gian chạy thì tạo job send email
            if (array_key_exists('runtime', $params) && !is_null($params['runtime'])) {
                $time = Carbon::parse($params['runtime']);
                $time = $time->timestamp - time();
                $this->sendEmail($data->id, $time);
            }

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
        $data = $this->getResource()->getById($id);
        if (!$data) {
            return $this->notFoundResponse();
        }

        DB::beginTransaction();

        try {

            $this->validate($request, $this->validationRules, $this->validationMessages);

            $params = $request->all();

            $params = array_only($params, ['name', 'description', 'status', 'cgroup_id', 'template', 'sms_template', 'target_type', 'period', 'customers', 'filters', 'sms_id', 'email_id']);
            if (array_key_exists('cgroup_id', $params)) {
                $params['cgroup_id'] = convert_uuid2id($params['cgroup_id']);
            }
            // TH chọn khách hàng thủ công
            if (array_key_exists('customers', $params) && $params['target_type'] == Campaign::MANUAL_TARGET) {
                $customers = [];
                foreach ($params['customers'] as $uuid) {
                    array_push($customers, convert_uuid2id($uuid));
                }
                $params['customers'] = $customers;
            }
            // TH chọn khách bằng filter
            if (array_key_exists('filters', $params) && $params['target_type'] == Campaign::FILTER_TARGET) {
                $cgroupParams['filters'] = $params['filters'];
                $cgroup = $this->cgroup->update($data->cgroup_id, $cgroupParams);
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

    /**
     * Lấy danh sách khách hàng của campaign
     * @param  int          campaign_id
     * @return response     [description]
     */
    public function showCustomers($id)
    {
        $campaign = $this->campaign->getById($id);

        if ($campaign) {
            $customers = [];
            if ($campaign->target_type == Campaign::GROUP_TARGET || $campaign->target_type == Campaign::FILTER_TARGET) {
                $customers = $this->cgroup->getCustomers($campaign->cgroup_id, 5);
            } else {
                $customers = $campaign->customers;
            }
            return $this->infoResponse($customers);
        } else {
            return $this->notFoundResponse();
        }
    }

    /**
     * Đặt lệnh gửi email
     * @param  integer  $id   
     * @param  integer  $time Số giây
     * @return [type]        [description]
     */
    public function sendEmail($id, $time = 1)
    {
        $campaign = $this->campaign->getById($id);

        if ($campaign) {
            $customers = [];

            if ($campaign->target_type == Campaign::GROUP_TARGET || $campaign->target_type == Campaign::FILTER_TARGET) {
                $customers = $this->cgroup->getCustomers($campaign->cgroup_id);
            } else {
                $customers = $campaign->customers;
            }
            if (count($customers->toArray()) == 0) {
                return $this->errorResponse(['errors' => ['customers' => ['Tập khách hàng rỗng!']]]);
            }
            
            try {
                $job = new SendEmailCampaign($campaign, $customers);
                dispatch($job)->delay(now()->addSeconds($time))->onQueue(env('APP_NAME'));
            } catch (\Exception $e) {
                throw $e;
            }
            return $this->infoResponse([]);
        } else {
            return $this->notFoundResponse();
        }
    }

    /**
     * Đặt lệnh gửi SMS
     * @param  integer  $id   
     * @return [type]        [description]
     */
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
                // Lấy danh sách khách hàng theo loại mục tiêu
                if ($campaign->target_type == Campaign::GROUP_TARGET || $campaign->target_type == Campaign::FILTER_TARGET) {
                    $customers = $this->cgroup->getCustomers($campaign->cgroup_id);
                } else {
                    $customers = $campaign->customers;
                }
                // Kiểm tra tính khả dung của tập khách hàng và tài khoản SMS
                $totalCustomer = count($customers->toArray());
                if ($totalCustomer == 0) {
                    return $this->errorResponse(['errors' => ['customers' => ['Tập khách hàng rỗng!']]]);
                } else {
                    $smsApi = new SpeedSMSAPI();
                    $smsAccountInfo = $smsApi->getUserInfo();
                    if ($smsAccountInfo['status'] == 'success'
                        && $smsAccountInfo['data']['balance'] < $totalCustomer * 500) {
                        return $this->errorResponse(['errors' => ['balance' => ['Tài khoản SMS không đủ tiền!']]]);
                    }
                }

                try {
                    $job = new SendSMSCampaign($campaign, $customers, $request->content);
                    dispatch($job)->delay(now()->addSeconds(1))->onQueue(env('APP_NAME'));
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

    /**
     * Thống kê tỷ lệ gửi email
     * @param  campaignId
     * @return [type]     [description]
     */
    public function statisticEmail($id)
    {
        $campaign = $this->campaign->getById($id);
        if ($campaign) {
            if (is_null($campaign->email_id)) {
                return $this->infoResponse([]);
            }
            $mailer = new \Nh\Repositories\Helpers\MailJetHelper();
            $message = $mailer->getMessageInfo($campaign->email_id);
            $listMessage = $mailer->getCampaignMessage($message->getData()[0]['CampaignID']);
            if ($listMessage->success()) {
                return $this->infoResponse($listMessage->getData());
            }
        }
        return $this->notFoundResponse();
    }

    /**
     * Thống kê tỷ lệ gửi SMS
     * @param  campaignId
     * @return [type]     [description]
     */
    public function statisticSMS($id)
    {
        $campaign = $this->campaign->getById($id);
        if ($campaign) {
            if (is_null($campaign->sms_id)) {
                return $this->infoResponse([]);
            }
            $smsApi = new SpeedSMSAPI();
            $smsReport = $smsApi->getSMSStatus($campaign->sms_id);
            if ($smsReport['status'] == 'success') {
                return $this->infoResponse($smsReport['data']);
            } else {
                return $this->infoResponse([]);
            }
        }
        return $this->notFoundResponse();
    }

    /**
     * Lấy danh sách khách hàng phù hợp với thuộc tính
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function previewCustomers(Request $request)
    {
        $params = [];
        $filters = array_only($request->all(), ['age_min', 'age_max', 'created_at_min', 'created_at_max', 'level', 'city_id', 'job']);
        foreach ($filters as $key => $filter) {
            if (!is_null($filter)) {
                switch ($key) {
                    case 'age_min':
                        array_push($params, ['attribute' => 'dob', 'operation' => '<=', 'value' => Carbon::now()->subYears($filter)->toDateString()]);
                        break;
                    case 'age_max':
                        array_push($params, ['attribute' => 'dob', 'operation' => '>=', 'value' => Carbon::now()->subYears($filter)->toDateString()]);
                        break;
                    case 'created_at_min':
                        array_push($params, ['attribute' => 'created_at', 'operation' => '>=', 'value' => $filter . ' 00:00:00']);
                        break;
                    case 'created_at_max':
                        array_push($params, ['attribute' => 'created_at', 'operation' => '<=', 'value' => $filter . ' 23:59:59']);
                        break;
                    default:
                        array_push($params, ['attribute' => $key, 'operation' => '=', 'value' => $filter]);
                        break;
                }
            }
        }
        return $this->infoResponse($this->customer->getByGroup($params, 10));
    }

}
