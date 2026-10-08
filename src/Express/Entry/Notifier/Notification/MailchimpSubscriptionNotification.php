<?php

namespace NewsletterSignupKit\Express\Entry\Notifier\Notification;

use NewsletterSignupKit\Entity\Mailchimp\MailchimpSubscriber;

class MailchimpSubscriptionNotification extends AbstractSubscriptionNotification
{
    protected function getListAttributeHandle()
    {
        return 'mailchimp_list';
    }

    protected function getSubscriberClass()
    {
        return MailchimpSubscriber::class;
    }
}
