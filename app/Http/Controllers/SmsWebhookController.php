<?php

namespace Nh\Http\Controllers;

use Illuminate\Http\Request;
use Nh\Http\Controllers\Controller;
use Nh\Http\Controllers\Api\ResponseHandler;
use Illuminate\Support\Facades\Log;
use Nh\Repositories\CampaignSmsIncomings\CampaignSmsIncomingRepository;

class SmsWebhookController extends Controller
{
    use ResponseHandler;

    protected $smsIncoming;

    public function __construct(CampaignSmsIncomingRepository $smsIncoming)
    {
    	$this->smsIncoming = $smsIncoming;
    }

    public function incomingSMS(Request $request)
    {
    	if ($request->secret == env('SPEEDSMS_WEBHOOK_SECRET')) {
    		$params = array_only($request->all(), ['phone', 'content']);
    		if ($params['phone'] && $params['content']) {
    			$this->smsIncoming->store($params);
    		}
    	}
    	return $this->infoResponse([]);
    }
}
