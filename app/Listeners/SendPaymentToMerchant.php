<?php

namespace Nh\Listeners;

use Nh\Events\PaymentSuccess;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use GuzzleHttp\Client as Requester;
use GuzzleHttp\Exception\RequestException;
use Nh\Repositories\Webhooks\Webhook;
use Nh\Repositories\Webhooks\WebhookRepository;
use Laravel\Passport\Client;

class SendPaymentToMerchant
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
     * @param  PaymentSuccess  $event
     * @return void
     */
    public function handle(PaymentSuccess $event)
    {
        $paymentHistory = $event->paymentHistory;
        $client_id  = $paymentHistory->client->id;
        $oauth_client = Client::where('user_id', $client_id)->first();

        $dataToDispatch = [
            'code' => 200,
            'status' => 'success',
            'data' => [
                'uuid'         => $paymentHistory->uuid,
                'customer_id'  => $paymentHistory->customer->uuid,
                'description'  => $paymentHistory->description,
                'total_amount' => $paymentHistory->total_amount,
                'total_point'  => $paymentHistory->total_point,
                'name'         => $paymentHistory->customer->name,
                'phone'        => $paymentHistory->customer->phone,
                'email'        => $paymentHistory->customer->email,
                'status'       => $paymentHistory->status,
                'webhook_type' => Webhook::WH_TRANSACTION,
                'payment_at'   => isset($paymentHistory->payment_at) ? $paymentHistory->payment_at->format('Y-m-d') : '',
            ]
        ];

        $webhook = $this->webhook->getByEvent(Webhook::WH_TRANSACTION, $client_id);

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
