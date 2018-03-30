<?php

namespace Nh\Repositories\Helpers;
use GuzzleHttp\Client as Guzzle;

class OneTwoThreeCsHelper {
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $requester;

    public function __construct() {
        $this->requester = new Guzzle(['headers' => [
                'Accept' => 'application/json',
                'Authorization' => env('123CS_TOKEN')
            ]
        ]);
    }

    protected function getRequesterHeader() {
        return [
            'Accept' => 'application/json',
            'Authorization' => env('123CS_TOKEN')
        ];
    }

    protected function gerenateUrl($url) {
        return env('123CS_API_URL') . $url;
    }

    public function getListUser ($params) {
        try {
            $request = $this->requester->request('GET', $this->gerenateUrl('agents'), ['query' => $params])->getBody()->getContents();
            $response = json_decode($request, true);
            switch ($response['code']) {
                case 1000:
                    $data = [
                        'current_page' => $response['data']['current_page'],
                        'data' => $response['data']['agents']
                    ];
                    return $data;
                    break;
                default:
                    return [];
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            \Log::debug($clientException->getResponse()->getBody()->getContents());
            return $clientException->getResponse()->getBody()->getContents();
        }
    }

    public function getUser ($id) {
        try {
            $request = $this->requester->request('GET', $this->gerenateUrl('agent/' . $id))->getBody()->getContents();
            $response = json_decode($request, true);
            switch ($response['code']) {
                case 1000:
                    return $response['data']['agent'];
                    break;
                case 1048:
                    return $response['data']['agent'];
                    break;
                default:
                    return null;
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            \Log::debug($clientException->getResponse()->getBody()->getContents());
            return $clientException->getResponse()->getBody()->getContents();
        }
    }

    public function updateUser ($id, $params) {
        // Chưa mở api
        try {
            $request = $this->requester->request('POST', $this->gerenateUrl('agent/' . $id), ['json' => $params])->getBody()->getContents();
            $response = json_decode($request, true);
            switch ($response['code']) {
                case 1000:
                    return $response['data']['agent'];
                    break;
                case 1002:
                    return null;
                    break;
                default:
                    return null;
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            \Log::debug($clientException->getResponse()->getBody()->getContents());
            return $clientException->getResponse()->getBody()->getContents();
        }
    }

    public function deleteUser($id)
    {
        // Chưa mở api
        try {
            $request = $this->requester->request('POST', $this->gerenateUrl('agent/delete/' . $id))->getBody()->getContents();
            $response = json_decode($request, true);
            switch ($response['code']) {
                case 1000:
                    return 1;
                    break;
                case 1048:
                    return null;
                    break;
                default:
                    return null;
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            \Log::debug($clientException->getResponse()->getBody()->getContents());
            return $clientException->getResponse()->getBody()->getContents();
        }
    }

    public function getListCustomer ($params) {
        try {
            $request = $this->requester->request('GET', $this->gerenateUrl('customers'), ['query' => $params])->getBody()->getContents();
            $response = json_decode($request, true);
            switch ($response['code']) {
                case 1000:
                    $data = [
                        'current_page' => $response['data']['current_page'],
                        'data' => $response['data']['customers']
                    ];
                    return $data;
                    break;
                default:
                    return [];
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            \Log::debug($clientException->getResponse()->getBody()->getContents());
            return $clientException->getResponse()->getBody()->getContents();
        }
    }

    public function getCustomer ($id) {
        try {
            $request = $this->requester->request('GET', $this->gerenateUrl('customer/' . $id))->getBody()->getContents();
            $response = json_decode($request, true);
            switch ($response['code']) {
                case 1000:
                    return $response['data']['customer'];
                    break;
                case 1048:
                    return $response['data']['customer'];
                    break;
                default:
                    return null;
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            \Log::debug($clientException->getResponse()->getBody()->getContents());
            return $clientException->getResponse()->getBody()->getContents();
        }
    }

    public function createCustomer($params)
    {
        try {
            $request = $this->requester->request('POST', $this->gerenateUrl('customer'), ['json' => $params])->getBody()->getContents();
            $response = json_decode($request, true);
            switch ($response['code']) {
                case 1000:
                    return $this->getCustomer($response['data']['customer_id']);
                    break;
                case 1002:
                    return null;
                    break;
                default:
                    return null;
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            \Log::debug($clientException->getResponse()->getBody()->getContents());
            return $clientException->getResponse()->getBody()->getContents();
        }
    }

    public function updateCustomer ($id, $params) {
        try {
            $request = $this->requester->request('POST', $this->gerenateUrl('customer/' . $id), ['json' => $params])->getBody()->getContents();
            $response = json_decode($request, true);
            switch ($response['code']) {
                case 1000:
                    return $response['data']['customer'];
                    break;
                case 1002:
                    return null;
                    break;
                default:
                    return null;
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            \Log::debug($clientException->getResponse()->getBody()->getContents());
            return $clientException->getResponse()->getBody()->getContents();
        }
    }

    public function deleteCustomer ($id)
    {
        try {
            $request = $this->requester->request('POST', $this->gerenateUrl('agent/delete/' . $id))->getBody()->getContents();
            $response = json_decode($request, true);
            switch ($response['code']) {
                case 1000:
                    return 1;
                    break;
                case 1048:
                    return null;
                    break;
                default:
                    return null;
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            \Log::debug($clientException->getResponse()->getBody()->getContents());
            return $clientException->getResponse()->getBody()->getContents();
        }
    }

    public function clickToCall($params)
    {
        if (is_int($params['ext'])) {
            $params['ext'] = strval($params['ext']);
        }
        $params['phone'] = '0' . strval(intval($params['phone']));
        try {
            $request = $this->requester->request('POST', $this->gerenateUrl('click_to_call'), ['json' => $params])->getBody()->getContents();
            $response = json_decode($request, true);
            switch ($response['code']) {
                case 1000:
                    return ['message' => $response['message'], 'data' => $response['data']];
                    break;
                case 1002:
                    return ['message' => $response['message'], 'data' => 'Không tồn tại đầu số nhân viên hoặc tài khoản thuê bao hết tiền.'];
                    break;
                case 1004:
                    return ['message' => 'error', 'data' => 'Mã kết nối đã hết hạn'];
                    break;
                default:
                    return ['message' => 'error', 'data' => 'Có lỗi xảy ra khi kết nối với nhà cung cấp dịch vụ'];
                    break;
            }
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            \Log::debug($clientException->getResponse()->getBody()->getContents());
            return $clientException->getResponse()->getBody()->getContents();
        }
    }
}
