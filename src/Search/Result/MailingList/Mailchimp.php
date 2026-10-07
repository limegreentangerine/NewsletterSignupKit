<?php

namespace NewsletterSignupKit\Search\Result\MailingList;

use NewsletterSignupKit\Search\Result\MailingList\Item\Mailchimp as MailchimpItem;

class Mailchimp extends AbstractMailingList
{
    protected function getItemClass(): string
    {
        return MailchimpItem::class;
    }
}
