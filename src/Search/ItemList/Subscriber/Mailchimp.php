<?php

namespace NewsletterSignupKit\Search\ItemList\Subscriber;

use NewsletterSignupKit\Entity\Mailchimp\MailchimpSubscriber;

class Mailchimp extends AbstractSubscriber
{
    protected function getProviderKey(): string
    {
        return 'mailchimp';
    }

    protected function getEntityClass(): string
    {
        return MailchimpSubscriber::class;
    }
}
