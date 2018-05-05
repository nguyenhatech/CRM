<?php

namespace Nh\Repositories\CampaignEmails;

use Nh\Repositories\Entity;

class CampaignEmail extends Entity
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['campaign_id', 'runtime', 'email_content'];
}
