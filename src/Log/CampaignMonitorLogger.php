<?php

namespace NewsletterSignupKit\Log;

use ClassKit\Log\Logger;

class CampaignMonitorLogger extends Logger
{
    public function __construct()
    {
        parent::__construct('campaign_monitor');
    }
}
