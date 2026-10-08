<?php

namespace NewsletterSignupKit\Express\Controller\Mailchimp;

use NewsletterSignupKit\Express\Controller\AbstractFormController;
use NewsletterSignupKit\Express\Entry\Notifier\Notification\MailchimpSubscriptionNotification;

class FormController extends AbstractFormController
{
    protected function getSubscriptionNotificationClass(): string
    {
        return MailchimpSubscriptionNotification::class;
    }
}
