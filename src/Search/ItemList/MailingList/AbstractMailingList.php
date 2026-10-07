<?php

namespace NewsletterSignupKit\Search\ItemList\MailingList;

use ClassKit\Search\ItemList\ListTrait;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Core\Application\ApplicationAwareInterface;

/**
 * Shared item list for mailing lists from any provider (rows of the single-table nsLists).
 *
 * To add a provider, extend this class and implement getProviderKey() and getEntityClass().
 */
abstract class AbstractMailingList extends ItemList implements ApplicationAwareInterface
{
    use ApplicationAwareTrait;
    use ListTrait;

    protected $autoSortColumns = [
        'l.name',
        'l.date_created',
    ];

    /**
     * Discriminator value of the provider (AbstractList @DiscriminatorMap key)
     */
    abstract protected function getProviderKey(): string;

    /**
     * Entity class returned for each row
     *
     * @return class-string<\NewsletterSignupKit\Entity\AbstractList>
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
        $this->query->select('l.id')
            ->from('nsLists', 'l')
            ->where($this->query->expr()->eq('l.provider', $this->query->createNamedParameter($this->getProviderKey())))
        ;
    }

    public function filterByName($name)
    {
        $name = (string) $name;
        if ($name !== '') {
            $this->filterBy('l.name', $name, 'like');
        }
    }

    public function filterByShowInForms($value = 1)
    {
        $this->filterBy('l.showInForms', $value);
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        $query
            ->resetQueryParts(['groupBy', 'orderBy'])
            ->select('count(distinct l.id)')
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
