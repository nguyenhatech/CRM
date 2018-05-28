<?php

namespace Nh\Repositories\Helpers;
use \Mailjet\Resources;

class MailJetHelper {

    private $sender;
    private $mailer;
    private $revicers;
    private $subject;
    private $campaign;
    private $contentHtml;
    private $contentText;

    function __construct()
    {
        $apikey = getenv('MAIL_USERNAME');
        $apisecret = getenv('MAIL_PASSWORD');
        $this->mailer = new \Mailjet\Client($apikey, $apisecret, true,['version' => 'v3']);

        $this->sender = [
            'Email' => config('mail.from')['address'],
            'Name' => config('mail.from')['name']
        ];
    }

    /**
     * Mẫu gửi email v3.1
     * @return [type] response
     */
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

    /**
     * Mẫu gửi email v3 group into campaign
     * @return [type] [description]
     */
    public function sendAsCampaign()
    {
        $body = [
            'FromEmail'              => config('mail.from')['address'],
            'FromName'               => config('mail.from')['name'],
            'Subject'                => $this->subject,
            'Html-part'              => $this->contentHtml,
            'Recipients'             => $this->revicers,
            'Mj-campaign'            => $this->campaign ? $this->campaign : 'Campaign_' . time(),
            'Mj-deduplicatecampaign' => true
        ];

        $response = $this->mailer->post(Resources::$Email, ['body' => $body]);

        return $response;
    }

    public function content($contentHtml)
    {
        $this->contentHtml = $contentHtml;
        return $this;
    }

    public function contentTextPlain($content)
    {
        $this->contentText = $contentText;
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

    public function campaign($campaign)
    {
        $this->campaign = formatToTextSimple($campaign);
        return $this;
    }

    public function getMessageInfo($messageId)
    {
        $response = $this->mailer->get(Resources::$Messageinformation, ['id' => $messageId]);
        return $response;
    }

    public function getCampaignInfo($campaignId)
    {
        $response = $this->mailer->get(Resources::$Campaign, ['id' => $campaignId]);
        return $response;
    }

    public function getCampaignMessage($campaignId)
    {
        $filters = [
          'CampaignId' => $campaignId
        ];
        $response = $this->mailer->get(Resources::$Message, ['filters' => $filters]);
        return $response;
    }

}
