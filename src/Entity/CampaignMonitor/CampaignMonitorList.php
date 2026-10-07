<?php

namespace NewsletterSignupKit\Entity\CampaignMonitor;

use Doctrine\ORM\Mapping as ORM;
use NewsletterSignupKit\Entity\AbstractList;

/**
 * @ORM\Entity
 */
class CampaignMonitorList extends AbstractList
{
    /**
     * @ORM\ManyToOne(targetEntity="NewsletterSignupKit\Entity\CampaignMonitor\MailingClient", inversedBy="lists")
     * @ORM\JoinColumn(name="clientId", referencedColumnName="id")
     */
    protected $client;

    /**
     * Get the value of client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the value of client
     *
     * @return self
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }
}
