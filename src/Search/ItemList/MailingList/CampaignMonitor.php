<?php

namespace NewsletterSignupKit\Search\ItemList\MailingList;

use NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorList;

class CampaignMonitor extends AbstractMailingList
{
    protected function getProviderKey(): string
    {
        return 'campaignmonitor';
    }

    protected function getEntityClass(): string
    {
        return CampaignMonitorList::class;
    }

    public function filterByDisplay($value = 0, $operator = '=')
    {
        if (!in_array($operator, ['=', '!=', '<>', '<', '<=', '>', '>='], true)) {
            $operator = '=';
        }

        $this->filterBy('l.showInForms', (int) $value, $operator);
    }

    /**
     * Lists available for a language: those with no language restriction, or that include it
     * (languages is stored as a comma-separated string).
     */
    public function filterByLanguage(string $language = '')
    {
        $expr = $this->query->expr();

        $this->query->andWhere($expr->or(
            $expr->isNull('l.languages'),
            'FIND_IN_SET(' . $this->query->createNamedParameter((string) $language) . ', l.languages) > 0',
        ));
    }
}
