<?php

namespace NewsletterSignupKit\Search\Column\Set\MailingList;

use Concrete\Core\Search\Column\{Column, Set};

class CampaignMonitor extends Set
{
    public function __construct()
    {
        $this->addColumn(new Column(
            'l.name',
            t('Name'),
            'getName',
            true,
        ));

        $this->addColumn(new Column(
            'l.showInForms',
            t('Show in Forms'),
            'getShowInFormsString',
            false,
        ));

        $this->addColumn(new Column(
            'l.date_created',
            t('Date Created'),
            'getDateCreatedString',
            true,
        ));

        $defaultSortColumn = $this->getColumnByKey('l.name');
        $this->setDefaultSortColumn($defaultSortColumn, 'asc');
    }
}
