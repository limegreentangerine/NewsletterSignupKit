<?php

namespace NewsletterSignupKit\Entity;

use Doctrine\ORM\Mapping as ORM;
use ClassKit\Entity\Core\UpdatedGuidEntity;

/**
 * Mailing lists from any newsletter provider (single table inheritance).
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="nsLists",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="provider_list", columns={"provider", "listId"})},
 *      options={"comment": "Mailing Lists available from newsletter providers"}
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="provider", type="string", length=32)
 * @ORM\DiscriminatorMap({
 *      "mailchimp" = "NewsletterSignupKit\Entity\Mailchimp\MailchimpList",
 *      "campaignmonitor" = "NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorList"
 * })
 */
abstract class AbstractList extends UpdatedGuidEntity
{
    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"comment": "List name"})
     */
    protected ?string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"comment": "List ID"})
     */
    protected ?string $listId;

    /**
     * @ORM\Column(type="integer", length=11, nullable=false, options={"comment": "Show this list forms", "default" : 0})
     */
    protected int $showInForms = 0;

    /**
     * Get By List ID
     *
     * Must be called on a provider subclass (e.g. MailchimpList::getByListID()),
     * because the same list ID may exist under more than one provider.
     *
     * @param string $id
     *
     * @return static|false
     */
    public static function getByListID(string $id)
    {
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            throw new \LogicException('Call getByListID() on a provider list class, or use getAllByListID().');
        }

        $em = \ORM::entityManager();
        $repository = $em->getRepository(static::class);

        $entity = $repository->findOneBy(['listId' => $id]);

        if ($entity && $entity->getID() > 0) {
            return $entity;
        }

        return false;
    }

    /**
     * Get the lists with any of these list IDs in a single query, keyed by list ID.
     *
     * Must be called on a provider subclass, like getByListID().
     *
     * @param string[] $ids
     *
     * @return array<string, static>
     */
    public static function getByListIDs(array $ids): array
    {
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            throw new \LogicException('Call getByListIDs() on a provider list class.');
        }

        if ($ids === []) {
            return [];
        }

        $em = \ORM::entityManager();
        $lists = [];

        foreach ($em->getRepository(static::class)->findBy(['listId' => array_values($ids)]) as $entity) {
            $lists[$entity->getListId()] = $entity;
        }

        return $lists;
    }

    /**
     * Get the lists with any of these primary keys in a single query, keyed by ID.
     *
     * @param string[] $ids
     *
     * @return array<string, static>
     */
    public static function getAllByIDs(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $em = \ORM::entityManager();
        $lists = [];

        foreach ($em->getRepository(static::class)->findBy(['id' => array_values($ids)]) as $entity) {
            $lists[$entity->getID()] = $entity;
        }

        return $lists;
    }

    /**
     * Get every list (across providers when called on the abstract class) with this list ID
     *
     * @param string $id
     *
     * @return static[]
     */
    public static function getAllByListID(string $id)
    {
        $em = \ORM::entityManager();

        return $em->getRepository(static::class)->findBy(['listId' => $id]);
    }

    /**
     * Get the provider's display name (shown in the Provider search column)
     */
    abstract public function getProvider(): string;

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of listId
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * Set the value of listId
     *
     * @return self
     */
    public function setListId($listId)
    {
        $this->listId = $listId;

        return $this;
    }

    /**
     * Get the value of showInForms
     */
    public function getShowInForms()
    {
        return $this->showInForms;
    }

    /**
     * Set the value of showInForms
     *
     * @return self
     */
    public function setShowInForms($showInForms)
    {
        $this->showInForms = $showInForms;

        return $this;
    }

    /**
     * Get the value of showInForms as a string
     *
     * Returns 'Yes' if showInForms is set to 1, otherwise returns 'No'
     *
     * @return string
     */
    public function getShowInFormsString()
    {
        return ($this->showInForms > 0) ? 'Yes' : 'No';
    }
}
