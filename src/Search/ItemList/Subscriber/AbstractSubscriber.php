<?php

namespace NewsletterSignupKit\Search\ItemList\Subscriber;

use ClassKit\Search\ItemList\ListTrait;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Core\Application\ApplicationAwareInterface;

/**
 * Shared item list for subscribers from any provider (rows of the single-table nsSubscribers).
 *
 * To add a provider, extend this class and implement getProviderKey() and getEntityClass().
 */
abstract class AbstractSubscriber extends ItemList implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;
    use ListTrait;

    protected $autoSortColumns = [
        's.email',
        's.date_created',
    ];

    /**
     * Discriminator value of the provider (AbstractSubscriber @DiscriminatorMap key)
     */
    abstract protected function getProviderKey(): string;

    /**
     * Entity class returned for each row
     *
     * @return class-string<\NewsletterSignupKit\Entity\AbstractSubscriber>
     */
    abstract protected function getEntityClass(): string;

    protected function createPaginationObject()
    {
        $query = $this->deliverQueryObject();
        $adapter = new \Pagerfanta\Doctrine\DBAL\QueryAdapter(
            $query,
            function () {
                return $this->getTotalResults();
            },
        );
        return new Pagination($this, $adapter);
    }

    public function createQuery()
    {
        $this->query->select('s.id')
            ->from('nsSubscribers', 's')
            ->where($this->query->expr()->eq('s.provider', $this->query->createNamedParameter($this->getProviderKey())))
        ;
    }

    public function filterByProcessedState($value = 0, $operator = '=')
    {
        if (!in_array($operator, ['=', '!=', '<>', '<', '<=', '>', '>='], true)) {
            $operator = '=';
        }

        $this->filterBy('s.processed', (int) $value, $operator);
    }

    /**
     * Subscribers created more than three months ago.
     */
    public function filterByExpired()
    {
        $cutoff = new \DateTime();
        $cutoff->sub(new \DateInterval('P3M'));

        $this->filterBy('s.date_created', $cutoff->format('Y-m-d H:i:s'), '<');
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        $query
            ->resetQueryParts(['groupBy', 'orderBy'])
            ->select('count(distinct s.id)')
            ->setMaxResults(1);
        $result = $query->execute()->fetchOne();

        return (int) $result;
    }

    public function getResult($queryRow)
    {
        $entityClass = $this->getEntityClass();

        return $entityClass::getByID($queryRow['id']);
    }
}
