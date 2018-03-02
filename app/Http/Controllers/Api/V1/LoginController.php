<?php

namespace Nh\Http\Controllers\Api\V1;

use Nh\Repositories\Users\UserRepository;
use Nh\Http\Controllers\Api\RestfulHandler;
use Nh\Http\Controllers\Api\TransformerTrait;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends ApiController
{
    use TransformerTrait, RestfulHandler;

    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    protected $validationRules = [
        'username' => 'required|email|max:255',
        'password' => 'required|min:6|max:255',
    ];

    protected $validationMessages = [
        'username.required' => 'Vui lòng nhập email',
        'username.email'    => 'Email không đúng định dạng',
        'username.max'      => 'Email cần nhỏ hơn :max kí tự',
        'password.required' => 'Vui lòng nhập mật khẩu',
        'password.min'      => 'Mật khẩu cần lớn hơn :min kí tự',
        'password.max'      => 'Mật khẩu cần nhỏ hơn :max kí tự',
    ];

    public function login(Request $request)
    {
        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $params = $request->all();
            $raw_password = $params['password'];
            $params['password'] = bcrypt($params['password']);
            if (Auth::attempt(['email' => $params['username'], 'password' => $raw_password])) {
                if (!Auth::user()->isActive()) {
                    Auth::logout();
                    return response()->json([
                        'message' => 'Tài khoản chưa được xác thực',
                        'status' => 'error',
                        'code' => 401
                    ], 401);
                }

                // Issue token
                $guzzle = new Guzzle;
                $url = env('APP_URL') . '/oauth/token';

                $options = [
                    'json' => [
                        'grant_type'    => 'password',
                        'client_id'     => env('CLIENT_ID', 0),
                        'client_secret' => env('CLIENT_SECRET', ''),
                        'username'      => $params['username'],
                        'password'      => $raw_password,
                    ]
                ];

                $result = $guzzle->request('POST', $url, $options)->getBody()->getContents();
                $result = json_decode($result, true);

                return $this->successResponse($result, false);
            }
            return $this->errorResponse([
                'errors' => ['Thông tin đăng nhập không chính xác.']
            ]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            // DO NOT REMOVE THIS
            // $errorMsg = $clientException->getMessage();
            // $errorMsg = explode("\n", $errorMsg);
            // $errorMsg = json_decode($errorMsg[1], true);
            // return $this->errorResponse([
            //     'errors' => [$errorMsg['message']]
            // ], $clientException->getCode());

            return $this->errorResponse([
                'errors' => ['Tài khoản developer chưa được xác thực.']
            ], $clientException->getCode());
        }
    }

    public function loginForDeveloper(Request $request)
    {
        try {
            $this->validationRules['client_id'] = 'required';
            $this->validationRules['client_secret'] = 'required';

            $this->validationMessages['client_id.required'] = 'Vui lòng nhập client_id';
            $this->validationMessages['client_secret.required'] = 'Vui lòng nhập client_secret';

            $this->validate($request, $this->validationRules, $this->validationMessages);
            $params = $request->all();

            $raw_password = $params['password'];
            $params['password'] = bcrypt($params['password']);
            if (Auth::attempt(['email' => $params['username'], 'password' => $raw_password])) {
                if (!Auth::user()->isActive()) {
                    Auth::logout();
                    return response()->json([
                        'message' => 'Tài khoản chưa được xác thực',
                        'status' => 'error',
                        'code' => 401
                    ], 401);
                }

                // Issue token
                $guzzle = new Guzzle;
                $url = env('APP_URL') . '/oauth/token';

                $options = [
                    'json' => [
                        'grant_type'    => 'password',
                        'client_id'     => $params['client_id'],
                        'client_secret' => $params['client_secret'],
                        'username'      => $params['username'],
                        'password'      => $raw_password,
                    ]
                ];

                $result = $guzzle->request('POST', $url, $options)->getBody()->getContents();
                $result = json_decode($result, true);

                return $this->successResponse($result, false);
            }
            return $this->errorResponse([
                'errors' => ['Thông tin đăng nhập không chính xác.']
            ]);
        } catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            // DO NOT REMOVE THIS
            // $errorMsg = $clientException->getMessage();
            // $errorMsg = explode("\n", $errorMsg);
            // $errorMsg = json_decode($errorMsg[1], true);
            // return $this->errorResponse([
            //     'errors' => [$errorMsg['message']]
            // ], $clientException->getCode());

            return $this->errorResponse([
                'errors' => ['Tài khoản developer chưa được xác thực.']
            ], $clientException->getCode());
        }
    }
}
