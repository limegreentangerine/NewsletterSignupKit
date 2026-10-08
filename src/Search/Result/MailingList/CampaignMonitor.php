<?php

namespace NewsletterSignupKit\Search\Result\MailingList;

use NewsletterSignupKit\Search\Result\MailingList\Item\CampaignMonitor as CampaignMonitorItem;

class CampaignMonitor extends AbstractMailingList
{
    protected function getItemClass(): string
    {
        return CampaignMonitorItem::class;
    }
}
