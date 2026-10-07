<?php

namespace NewsletterSignupKit\Search\ItemList\MailingList;

use NewsletterSignupKit\Entity\Mailchimp\MailchimpList;

class Mailchimp extends AbstractMailingList
{
    protected function getProviderKey(): string
    {
        return 'mailchimp';
    }

    protected function getEntityClass(): string
    {
        return MailchimpList::class;
    }
}
