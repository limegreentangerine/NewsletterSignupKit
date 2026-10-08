<?php

namespace Concrete\Package\NewsletterSignupKit\Controller\Search;

use Concrete\Core\Search\StickyRequest;
use NewsletterSignupKit\Provider\Registry;
use Concrete\Core\Controller\AbstractController;

/**
 * Search controller for the dashboard mailing lists page, for one provider at a time.
 */
class MailingLists extends AbstractController
{
    private string $provider;

    private ?StickyRequest $stickyRequest = null;

    private $searchList;

    private $searchResult;

    public function __construct(string $provider = 'mailchimp')
    {
        parent::__construct();
        $this->provider = Registry::has($provider) ? $provider : 'mailchimp';
    }

    protected function getSearchList()
    {
        if ($this->searchList === null) {
            $this->searchList = $this->app->make(Registry::itemListClass($this->provider), [$this->getStickyRequest()]);
        }

        return $this->searchList;
    }

    public function getStickyRequest(): StickyRequest
    {
        if ($this->stickyRequest === null) {
            $this->stickyRequest = new StickyRequest('nsk.list.' . $this->provider);
        }

        return $this->stickyRequest;
    }

    public function getAllowedPaginationSizes(): array
    {
        return [10, 20, 50, 100];
    }

    public function getDefaultPaginationSize(): int
    {
        return $this->getAllowedPaginationSizes()[1];
    }

    public function search(bool $reset = false): void
    {
        $stickyRequest = $this->getStickyRequest();
        $searchList = $this->getSearchList();

        if ($reset) {
            $stickyRequest->resetSearchRequest();
        }

        $columnSetClass = Registry::columnSetClass($this->provider);
        $columnSet = new $columnSetClass();

        if (!$searchList->getActiveSortColumn()) {
            $sortColumn = $columnSet->getDefaultSortColumn();
            $searchList->sanitizedSortBy($sortColumn->getColumnKey(), $sortColumn->getColumnDefaultSortDirection());
        }

        $req = $stickyRequest->getSearchRequest();

        $name = $req['name'] ?? null;
        if (is_string($name) && $name !== '') {
            $searchList->filterByName($name);
        }

        $paginationSize = (int) ($req['num_results'] ?? 0);
        if (!in_array($paginationSize, $this->getAllowedPaginationSizes(), true)) {
            $paginationSize = $this->getDefaultPaginationSize();
        }

        $searchList->setItemsPerPage($paginationSize);

        $resultClass = Registry::resultClass($this->provider);
        $this->searchResult = new $resultClass(
            $columnSet,
            $searchList,
            $this->app->make('url/manager')->resolve(['dashboard/newsletter_signup/lists/'])->setQuery(['provider' => $this->provider]),
        );
    }

    /**
     * Get the search result (once the search() method has been called).
     */
    public function getSearchResultObject()
    {
        return $this->searchResult;
    }
}
