<?php

namespace NewsletterSignupKit\Entity;

use Doctrine\ORM\Mapping as ORM;
use ClassKit\Entity\Core\UpdatedGuidEntity;

/**
 * Subscribers to add to any newsletter provider (single table inheritance).
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="nsSubscribers",
 *      options={"comment": "Subscribers to add to newsletter providers"}
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="provider", type="string", length=32)
 * @ORM\DiscriminatorMap({
 *      "mailchimp" = "NewsletterSignupKit\Entity\Mailchimp\MailchimpSubscriber",
 *      "campaignmonitor" = "NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorSubscriber"
 * })
 */
abstract class AbstractSubscriber extends UpdatedGuidEntity
{
    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"comment": "List ID"})
     */
    protected ?string $listId;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"comment": "Email Address"})
     */
    protected string $email;

    /**
     * @ORM\Column(type="integer", length=11, nullable=false, options={"comment": "Is Subscription Processed?", "default" : 0})
     */
    protected int $processed = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"comment": "First Name"})
     */
    protected ?string $firstName = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"comment": "Last Name"})
     */
    protected ?string $lastName = null;

    protected static function assertConcrete()
    {
        if ((new \ReflectionClass(static::class))->isAbstract()) {
            throw new \LogicException('Call this finder on a provider subscriber class.');
        }
    }

    /**
     * Get By Email Address
     *
     * Must be called on a provider subclass, because the same email may be
     * queued for more than one provider.
     *
     * @param string $email
     *
     * @return static|false
     */
    public static function getByEmail(string $email)
    {
        static::assertConcrete();

        $em = \ORM::entityManager();
        $repository = $em->getRepository(static::class);

        $entity = $repository->findOneBy(['email' => $email]);

        if ($entity && $entity->getID() > 0) {
            return $entity;
        }

        return false;
    }

    /**
     * Get the subscriptions of an email address for any of these list IDs in a single query, keyed by list ID.
     *
     * @param string[] $listIds
     *
     * @return array<string, static>
     */
    public static function getByEmailAndListIDs(string $email, array $listIds): array
    {
        static::assertConcrete();

        if ($listIds === []) {
            return [];
        }

        $em = \ORM::entityManager();
        $subscribers = [];

        foreach ($em->getRepository(static::class)->findBy(['email' => $email, 'listId' => array_values($listIds)]) as $entity) {
            $subscribers[$entity->getListId()] = $entity;
        }

        return $subscribers;
    }

    /**
     * Get By Email Address and List ID
     *
     * @param string $email
     * @param string $listId
     *
     * @return static|false
     */
    public static function getByEmailAndListID(string $email, string $listId)
    {
        static::assertConcrete();

        $em = \ORM::entityManager();
        $repository = $em->getRepository(static::class);

        $entity = $repository->findOneBy(['email' => $email, 'listId' => $listId]);

        if ($entity && $entity->getID() > 0) {
            return $entity;
        }

        return false;
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
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of processed
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * Set the value of processed
     *
     * @return self
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;

        return $this;
    }

    /**
     * Get the first name of the subscriber
     *
     * @return string|null the first name of the subscriber
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the first name for the subscriber
     *
     * @param string|null $firstName the first name
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Retrieves the last name associated with the subscriber.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the last name for the subscriber
     *
     * @param string|null $lastName the last name
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * First and last name joined with a single space; empty if neither is set
     */
    public function getFullName(): string
    {
        return trim(trim((string) $this->firstName) . ' ' . trim((string) $this->lastName));
    }
}
