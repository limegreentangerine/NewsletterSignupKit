<?php

namespace NewsletterSignupKit\Express\Entry\Notifier\Notification;

use Core;
use Concrete\Core\Entity\Express\Entry;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Core\Express\Entry\Notifier\NotificationInterface;

abstract class AbstractSubscriptionNotification implements NotificationInterface
{
    /**
     * The EntityManager instance.
     *
     * @var \Doctrine\ORM\EntityManagerInterface|null
     */
    protected $entityManager;

    /**
     * The attribute type handle of the list attribute (e.g. 'mailchimp_list').
     *
     * @return string
     */
    abstract protected function getListAttributeHandle();

    /**
     * The fully qualified subscriber entity class. It must provide the static
     * getByEmailAndListID() and the setEmail/setListId/setProcessed setters.
     *
     * @return string
     */
    abstract protected function getSubscriberClass();

    public function notify(Entry $entry, $type)
    {
        $email = null;
        $listIds = [];

        foreach ($entry->getAttributes() as $attr) {
            if ($attr->getAttributeTypeObject()->getAttributeTypeHandle() == $this->getListAttributeHandle()) {
                $value = json_decode($attr->getValue());

                if (is_array($value)) {
                    foreach ($value as $listId) {
                        $listIds[] = $listId;
                    }
                }
            } else {
                if (filter_var($attr->__toString(), FILTER_VALIDATE_EMAIL)) {
                    $email = $attr->__toString();
                }
            }
        }

        if ($email !== null && count($listIds) > 0) {
            $this->entityManager = Core::make(EntityManagerInterface::class);

            $subscriberClass = $this->getSubscriberClass();

            foreach ($listIds as $listId) {
                $sub = $subscriberClass::getByEmailAndListID($email, $listId);

                if ($sub === false || $sub->getID() < 1) {
                    $sub = new $subscriberClass();
                    $sub->setEmail($email);
                    $sub->setListId($listId);
                    $sub->setProcessed(0);

                    $this->entityManager->persist($sub);
                }
            }

            $this->entityManager->flush();
        }
    }
}
