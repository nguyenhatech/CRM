<?php

namespace Nh\Repositories\CampaignSms;

use Nh\Repositories\Entity;

class CampaignSms extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['campaign_id', 'sms_id', 'total', 'success', 'fail', 'done'];
}
