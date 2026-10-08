<?php

namespace NewsletterSignupKit\Express\Entry\Notifier\Notification;

use NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorSubscriber;

class CampaignMonitorSubscriptionNotification extends AbstractSubscriptionNotification
{
    protected function getListAttributeHandle()
    {
        return 'campaign_monitor_list';
    }

    protected function getSubscriberClass()
    {
        return CampaignMonitorSubscriber::class;
    }
}
