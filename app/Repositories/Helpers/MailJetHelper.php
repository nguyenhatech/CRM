<?php

namespace Nh\Repositories\Helpers;
use \Mailjet\Resources;

class MailJetHelper {

    private $sender;
    private $mailer;
    private $revicers;
    private $subject;
    private $content;

    function __construct()
    {
        $apikey = getenv('MAIL_USERNAME');
        $apisecret = getenv('MAIL_PASSWORD');
        $this->mailer = new \Mailjet\Client($apikey, $apisecret, true,['version' => 'v3.1']);

        $this->sender = [
            'Email' => config('mail.from')['address'],
            'Name' => config('mail.from')['name']
        ];
    }

    public function sent()
    {
        $body = [
            'Messages' => [
                [
                    'From' => $this->sender,
                    'To' => $this->revicers,
                    'Subject' => $this->subject,
                    'HTMLPart' => $this->content
                ]
            ]
        ];

        $response = $this->mailer->post(Resources::$Email, ['body' => $body]);

        return $response;
    }

    public function content($content)
    {
        $this->content = $content;
        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function revicer($revicers)
    {
        if (is_array($revicers)) {
            foreach ($revicers as $key => $revicer) {
                $this->revicers[] = [
                    'Email' => $revicer
                ];
            }
        } else {
            $this->revicers[] = [
                'Email' => $revicers
            ];
        }

        return $this;
    }

}
