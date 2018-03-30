<?php

namespace Nh\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Nh\Http\Controllers\Api\V1\ApiController;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;

use Nh\Repositories\PhoneCallHistories\PhoneCallHistoryRepository;
use Nh\Repositories\PhoneCallHistories\PhoneCallHistory;
use Nh\Repositories\Helpers\OneTwoThreeCsHelper;

use \Firebase\JWT\JWT;

class CallCenterController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $phoneCallHistory;
    protected $api123CS;

    protected $customerRules = [
        'tenant_id' => 'required',
        'fullname'  => 'required'
    ];
    protected $customerMessages = [
        'tenant_id.required' => 'Mã nhân viên không được để trống',
        'fullname.required'  => 'Tên khách hàng không được để trống'
    ];

    public function __construct(PhoneCallHistoryRepository $phoneCallHistory, OneTwoThreeCsHelper $api123CS)
    {
    	$this->phoneCallHistory = $phoneCallHistory;
    	$this->api123CS = $api123CS;
    }

    public function getListUser(Request $request)
    {
    	$params = array_only($request->all(), ['created_at_min', 'created_at_max', 'phonecontact', 'fullname', 'limit', 'page']);
        if (array_key_exists('created_at_min', $params)) {
            $params['created_at_min'] = strtotime($params['created_at_min']);
        }
        if (array_key_exists('created_at_max', $params)) {
            $params['created_at_max'] = strtotime($params['created_at_max']);
        }

    	$reponse = $this->api123CS->getListUser($params);
    	return $this->infoResponse($reponse);
    }

    public function getUser($id)
    {
        $reponse = $this->api123CS->getUser($id);
        if (is_null($reponse)) {
            return $this->notFoundResponse();
        }
        return $this->infoResponse($reponse);
    }

    public function updateUser($id)
    {
        $reponse = $this->api123CS->updateUser($id);
        if (is_null($reponse)) {
            return $this->notFoundResponse();
        }
        return $this->infoResponse($reponse);
    }

    public function deleteUser ($id)
    {
        $reponse = $this->api123CS->deleteUser($id);
        if (is_null($reponse)) {
            return $this->notFoundResponse();
        }
        return $this->deleteResponse();
    }

    public function getListCustomer (Request $request)
    {
        $params = array_only($request->all(), ['created_at_min', 'created_at_max', 'phonecontact', 'fullname', 'agent_id', 'limit', 'page']);
        if (array_key_exists('created_at_min', $params)) {
            $params['created_at_min'] = strtotime($params['created_at_min']);
        }
        if (array_key_exists('created_at_max', $params)) {
            $params['created_at_max'] = strtotime($params['created_at_max']);
        }

        $reponse = $this->api123CS->getListCustomer($params);
        return $this->infoResponse($reponse);
    }

    public function getCustomer($id)
    {
        $reponse = $this->api123CS->getCustomer($id);
        if (is_null($reponse)) {
            return $this->notFoundResponse();
        }
        return $this->infoResponse($reponse);
    }

    public function createCustomer(Request $request)
    {
        try {
            $this->validate($request, $this->customerRules, $this->customerMessages);
            $params = $request->all();
            $reponse = $this->api123CS->createCustomer(['customer' => $params]);
            if (is_null($reponse)) {
                return $this->infoResponse([]);
            }
            return $this->infoResponse($reponse);
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

    public function updateCustomer($id, Request $request)
    {
        $params = $request->all();
        $reponse = $this->api123CS->updateCustomer($id, ['customer' => $params]);
        if (is_null($reponse)) {
            return $this->notFoundResponse();
        }
        return $this->infoResponse($reponse);
    }

    public function deleteCustomer ($id)
    {
        $reponse = $this->api123CS->deleteCustomer($id);
        if (is_null($reponse)) {
            return $this->notFoundResponse();
        }
        return $this->deleteResponse();
    }

    public function clickToCall(Request $request)
    {
        $validateRules = [
            'ext'   => 'required',
            'phone' => 'required',
            'type'  => 'required|numeric'
        ];
        $validateMessages = [
            'ext.required'   => 'Chưa có thông tin đầu số',
            'phone.required' => 'Chưa có số điện thoại',
            'type.required'  => 'Chưa chọn loại',
            'type.numeric'   => 'Loại chưa đúng định dạng'
        ];

        try {
            $this->validate($request, $validateRules, $validateMessages);
            $params = array_only($request->all(), ['ext', 'phone', 'type']);
            $params['type'] = PhoneCallHistory::CALL_DEVICE_TYPE_LIST[$params['type']]; // Gán lại type text
            if (getCurrentUser()->line) {
                $params['ext'] = getCurrentUser()->line->line;
            }
            $response = $this->api123CS->clickToCall($params);
            if ($response['message'] == 'error') {
                return $this->errorResponse([
                    'errors' => [
                        'connect' => [
                            $response['data']
                        ]
                    ]
                ]);
            }
            // Create phone call history
            $data = array_only($response['data'], ['from', 'tenantId', 'agentId', 'to', 'type', 'transaction_id', 'android_push_key', 'apple_push_key']);

            $data['tenant_id'] = array_get($data, 'tenantId', '');
            $data['agent_id']  = array_get($data, 'agentId', '');
            $data['type']      = array_search($data['type'], PhoneCallHistory::CALL_DEVICE_TYPE_LIST);
            $data['user_id']   = getCurrentUser()->id;
            $data['call_type'] = PhoneCallHistory::CALL_OUT;

            $phoneCall = $this->phoneCallHistory->store($data);

            return $this->infoResponse($phoneCall);
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

    public function getToken123CS () {
        $tenant_id = env('123CS_ID'); //ID tenants
        $secret = env('123CS_SECRET');
        $third_party_code = env('123CS_CODE');
        $token = array(
            "iss" => $third_party_code,
            "aud" => $tenant_id,
            "exp" => time() + 604800
        );
        return JWT::encode($token, $secret);
    }
}
