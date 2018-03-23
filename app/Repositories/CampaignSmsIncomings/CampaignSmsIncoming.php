<?php

namespace Nh\Repositories\CampaignSmsIncomings;

use Nh\Repositories\Entity;

class CampaignSmsIncoming extends Entity
{
	protected $table = 'campaign_sms_incomings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['campaign_id', 'phone', 'content'];
}
