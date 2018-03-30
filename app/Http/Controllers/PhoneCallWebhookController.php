<?php

namespace Nh\Http\Controllers;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Controller;
use Nh\Http\Controllers\Api\ResponseHandler;
use Nh\Repositories\PhoneCallHistories\PhoneCallHistoryRepository;
use Nh\Repositories\PhoneCallHistories\PhoneCallHistory;

class PhoneCallWebhookController extends Controller
{
	private $phoneCallHistory;

    public function __construct(PhoneCallHistoryRepository $phoneCallHistory)
    {
    	$this->phoneCallHistory = $phoneCallHistory;
    }

    public function index(Request $request)
    {
        \Log::info(['123csWebhook', $request]);
        $params = $request->all();
		switch ($params['action']) {
			case 'call':
				// Event call
				if (array_key_exists('data', $params)) {
					$this->phoneCallHistory->updateStatusByWebhook($params['data']);
				} else {
					// New call
					$this->phoneCallHistory->makeByCallInWebHook($params);
				}
				break;
			case 'click_to_call':
				$this->phoneCallHistory->updateStatusByWebhook($params['data']);
				break;
			case 'profile':
				break;
			case 'agent':
				break;
			case 'mapping':
				break;
			
			default:
				break;
		}
    }
}
