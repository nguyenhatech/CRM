<?php

namespace Nh\Repositories\CampaignEmails;
use Nh\Repositories\BaseRepository;

class DbCampaignEmailRepository extends BaseRepository implements CampaignEmailRepository
{
    public function __construct(CampaignEmail $campaignEmail)
    {
        $this->model = $campaignEmail;
    }

}
