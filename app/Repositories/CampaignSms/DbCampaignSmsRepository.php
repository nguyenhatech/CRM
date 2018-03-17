<?php

namespace Nh\Repositories\CampaignSms;
use Nh\Repositories\BaseRepository;

class DbCampaignSmsRepository extends BaseRepository implements CampaignSmsRepository
{
    public function __construct(CampaignSms $campaignSms)
    {
        $this->model = $campaignSms;
    }

}
