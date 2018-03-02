<?php

namespace Nh\Listeners;

use Nh\Events\InfoCustomer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use GuzzleHttp\Client as Requester;
use GuzzleHttp\Exception\RequestException;
use Nh\Repositories\Webhooks\Webhook;
use Nh\Repositories\Webhooks\WebhookRepository;
use Laravel\Passport\Client;

class SendCustomerToMerchant
{
    protected $requester;
    protected $webhook;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Requester $requester, WebhookRepository $webhook)
    {
        $this->requester = $requester;
        $this->webhook = $webhook;
    }

    /**
     * Handle the event.
     *
     * @param  InfoCustomer  $event
     * @return void
     */
    public function handle(InfoCustomer $event)
    {
        $customer = $event->customer;
        $clients = $customer->client;

        foreach ($clients as $key => $client) {
            $oauth_client = Client::where('user_id', $client->id)->first();
            $dataToDispatch = [
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'uuid'         => $customer->uuid,
                    'client_id'    => $client->id,
                    'name'         => $customer->name,
                    'phone'        => $customer->phone,
                    'email'        => $customer->email,
                    'address'      => $customer->address,
                    'level'        => $customer->level,
                    'avatar'       => $customer->avatar,
                    'avatar_path'  => $customer->getAvatar(),
                    'webhook_type' => Webhook::WH_CUSTOMER,
                    'updated_at'   => $customer->updated_at->format('Y-m-d H:i:s'),
                    'created_at'   => $customer->created_at->format('Y-m-d H:i:s')
                ]
            ];

            $webhook = $this->webhook->getByEvent(Webhook::WH_CUSTOMER, $client->id);

            if ($webhook)
            {
                $token = generateWebhookToken($dataToDispatch, $oauth_client->secret);
                try {
                    $rs = $this->requester->request('POST', $webhook->endpoint, [
                        'headers' => [
                            'X-CRM-Hmac-SHA256' => $token
                        ],
                        'json' => $dataToDispatch
                    ]);
                } catch (RequestException $e) {

                }
            }
        }
    }
}
