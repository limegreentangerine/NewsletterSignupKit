<?php

namespace NewsletterSignupKit\Express\Controller\CampaignMonitor;

use NewsletterSignupKit\Express\Controller\AbstractFormController;
use NewsletterSignupKit\Express\Entry\Notifier\Notification\CampaignMonitorSubscriptionNotification;

class FormController extends AbstractFormController
{
    protected function getSubscriptionNotificationClass(): string
    {
        return CampaignMonitorSubscriptionNotification::class;
    }
}
