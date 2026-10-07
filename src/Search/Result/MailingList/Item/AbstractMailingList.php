<?php

namespace NewsletterSignupKit\Search\Result\MailingList\Item;

use URL;
use Concrete\Core\Search\Column\Set;
use Concrete\Core\Search\Result\{Item, Result};

/**
 * Shared result item for mailing lists from any provider.
 */
abstract class AbstractMailingList extends Item
{
    protected $entity;

    public function __construct(Result $result, Set $columns, $item)
    {
        parent::__construct($result, $columns, $item);
        $this->entity = $item;
    }

    public function getViewUrl()
    {
        return URL::to('/dashboard/newsletter_signup/lists/details', $this->entity->getID());
    }
}
