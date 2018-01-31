<?php

namespace Goship\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Laravel\Passport\ClientRepository;
use Goship\Hocs\CarrierConstant;
use Goship\Hocs\Webhooks\WebhookRepository;
use Goship\Http\Controllers\Api\ResponseHandler;

class DeveloperController extends ApiController
{
    use ResponseHandler;

    protected $validationRules = [
        'event'    => 'required',
        'endpoint' => 'required|url'
    ];

    protected $validationMessages = [
        'event.required'    => 'Vui lòng chọn event',
        'endpoint.required' => 'endpoint là bắt buộc',
        'endpoint.url'      => 'endpoint không đúng định dạng'
    ];

    /**
     * Get client authen info
     * @return [type] [description]
     */
    public function getClient(ClientRepository $client)
    {
        $loggedUser = \Auth::user();
        $clients = $client->forUser($loggedUser->id);
        return $this->successResponse(['data' => [
                'id' => $clients->count() > 0 ? $clients[0]->id : '',
                'secret' => $clients->count() > 0 ? $clients[0]->secret : '',
            ]], false);

    }

    /**
     * Generate client authen info
     * @param  ClientRepository $client [description]
     * @return [type]                   [description]
     */
    public function generate(ClientRepository $client)
    {
        $loggedUser = \Auth::user();
        $clients = $client->forUser($loggedUser->id);
        // dd($client->createPasswordGrantClient($loggedUser->id, $loggedUser->name, ''));
        if ($clients->count() == 0)
        {
            $_client = $client->createPasswordGrantClient($loggedUser->id, $loggedUser->name, '');
            return $this->successResponse(['data' => $_client->toArray()], false);
        }
    }

    /**
     * Add webhook
     * @param Request $request [description]
     */
    public function add(Request $request, WebhookRepository $wh)
    {
        try {
            $this->validate($request, $this->validationRules, $this->validationMessages);
            $wh->store([
                'client_id' => \Auth::user()->id,
                'event_id' => $request->get('event'),
                'event' => $wh->model->event_list[$request->get('event')],
                'endpoint' => $request->get('endpoint'),
            ]);
            return $this->successResponse(['data' => ['Add subscription successful.']], false);
        }
        catch (\Illuminate\Validation\ValidationException $validationException) {
            return $this->errorResponse([
                'errors' => $validationException->validator->errors(),
                'exception' => $validationException->getMessage()
            ]);
        }
    }

    /**
     * Get list webhook
     * @return [type] [description]
     */
    public function getWebhooks(WebhookRepository $wh)
    {
        return $this->successResponse(['data' => $wh->getByClient()], false);
    }

    /**
     * Remove a webhook
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deleteWebhook($id, WebhookRepository $wh)
    {
        if ($wh->delete($id))
        {
            return $this->deleteResponse();
        }
        return $this->errorResponse(['data' => ['Delete subscription fail.']], false);
    }

    /**
     * [getWebhookEvent description]
     * @return [type] [description]
     */
    public function getWebhookEvent(WebhookRepository $wh)
    {
        return $this->successResponse(['data' => $wh->getEvents()], false);
    }
}
