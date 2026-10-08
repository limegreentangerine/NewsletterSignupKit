<?php

namespace NewsletterSignupKit\Search\Result\MailingList;

use ClassKit\Search\Result as SearchResult;

/**
 * Shared search result for mailing lists from any provider.
 *
 * To add a provider, extend this class and implement getItemClass().
 */
abstract class AbstractMailingList extends SearchResult
{
    /**
     * Result item class created for each row
     *
     * @return class-string<\NewsletterSignupKit\Search\Result\MailingList\Item\AbstractMailingList>
     */
    abstract protected function getItemClass(): string;

    public function getItemDetails($result)
    {
        $itemClass = $this->getItemClass();

        return new $itemClass($this, $this->listColumns, $result);
    }
}
