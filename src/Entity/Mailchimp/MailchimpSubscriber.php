<?php

namespace NewsletterSignupKit\Entity\Mailchimp;

use Doctrine\ORM\Mapping as ORM;
use NewsletterSignupKit\Entity\AbstractSubscriber;

/**
 * @ORM\Entity
 */
class MailchimpSubscriber extends AbstractSubscriber
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"comment": "First Name"})
     */
    protected ?string $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"comment": "Last Name"})
     */
    protected ?string $lastName;

    /**
     * Get the first name of the subscriber
     *
     * @return string the first name of the subscriber
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the first name for the subscriber
     *
     * @param string $firstName the first name
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
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the last name for the subscriber
     *
     * @param string $lastName the last name
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }
}
