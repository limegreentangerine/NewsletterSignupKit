<?php

namespace NewsletterSignupKit\Search\ItemList\Subscriber;

use NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorSubscriber;

class CampaignMonitor extends AbstractSubscriber
{
    protected function getProviderKey(): string
    {
        return 'campaignmonitor';
    }

    protected function getEntityClass(): string
    {
        return CampaignMonitorSubscriber::class;
    }
}
