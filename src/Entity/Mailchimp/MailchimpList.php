<?php

namespace NewsletterSignupKit\Entity\Mailchimp;

use Doctrine\ORM\Mapping as ORM;
use NewsletterSignupKit\Entity\AbstractList;

/**
 * @ORM\Entity
 */
class MailchimpList extends AbstractList
{
    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true, options={"comment": "List Web ID"})
     */
    protected ?string $listWebId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"comment": "List Visibility"})
     */
    protected ?string $visibility;

    public function getProvider(): string
    {
        return t('Mailchimp');
    }

    /**
     * Get By List Web ID
     *
     * @param string $id
     *
     * @return self|false
     */
    public static function getByListWebID(string $id)
    {
        $em = \ORM::entityManager();
        $repository = $em->getRepository(static::class);

        $entity = $repository->findOneBy(['listWebId' => $id]);

        if ($entity && $entity->getID() > 0) {
            return $entity;
        }

        return false;
    }

    /**
     * Get the value of listWebId
     */
    public function getListWebId()
    {
        return $this->listWebId;
    }

    /**
     * Set the value of listWebId
     *
     * @return self
     */
    public function setListWebId(?string $listWebId)
    {
        $this->listWebId = $listWebId;

        return $this;
    }

    /**
     * Get the value of visibility
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set the value of visibility
     *
     * @return self
     */
    public function setVisibility(?string $visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }
}
