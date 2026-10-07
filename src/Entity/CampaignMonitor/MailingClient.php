<?php

namespace NewsletterSignupKit\Entity\CampaignMonitor;

use Doctrine\ORM\Mapping as ORM;
use ClassKit\Entity\Core\UpdatedGuidEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="cmClients",
 *      options={"comment": "Mailing List Clients from Campaign Monitor"}
 * )
 */
class MailingClient extends UpdatedGuidEntity
{
    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=false, options={"comment": "Client ID"})
     */
    protected $clientId;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"comment": "Client name"})
     */
    protected $name;

    /**
     * One product has many features. This is the inverse side.
     * @ORM\OneToMany(targetEntity="NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorList", mappedBy="client")
     */
    protected $lists;

    public function __construct()
    {
        parent::__construct();

        $this->lists = new ArrayCollection();
    }

    /**
     * Get By Client ID
     *
     * @param string $id
     *
     * @return self|false
     */
    public static function getByClientID(string $id)
    {
        $em = \ORM::entityManager();
        $repository = $em->getRepository(get_called_class());

        $entity = $repository->findOneBy(['clientId' => $id]);

        if ($entity && $entity->getID() > 0) {
            return $entity;
        }

        return false;
    }

    /**
     * Get the value of clientId
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set the value of clientId
     *
     * @return self
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

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
     * Get one product has many features. This is the inverse side.
     */
    public function getLists()
    {
        return $this->lists;
    }

    public function addList($list)
    {
        if (!$this->lists->contains($list)) {
            $this->lists->add($list);
            $list->setClient($this);
        }

        return $this;
    }

    public function removeList($list)
    {
        if ($this->lists->contains($list)) {
            $this->lists->remove($list);
            $list->setClient(null);
        }

        return $this;
    }
}
