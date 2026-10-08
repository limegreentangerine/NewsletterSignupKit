<?php

namespace NewsletterSignupKit\Search\Pagination;

use Pagerfanta\Pagerfanta;
use Concrete\Core\Search\Pagination\Pagination;
use NewsletterSignupKit\Search\ItemList\MailingList\AbstractMailingList;

/**
 * Pagination that hydrates the current page of mailing lists in one query instead of one per row.
 */
class MailingListPagination extends Pagination
{
    public function getCurrentPageResults()
    {
        /** @var AbstractMailingList $list */
        $list = $this->list;

        $list->debugStart();
        $rows = Pagerfanta::getCurrentPageResults();
        $list->debugStop();

        return $list->hydrate($rows);
    }
}
