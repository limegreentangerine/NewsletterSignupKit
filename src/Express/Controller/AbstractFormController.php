<?php

namespace NewsletterSignupKit\Express\Controller;

use Concrete\Core\Express\Controller\StandardController;
use Concrete\Core\Express\Entry\Notifier\{NotificationProviderInterface, StandardNotifier};

abstract class AbstractFormController extends StandardController
{
    abstract protected function getSubscriptionNotificationClass(): string;

    public function getNotifier(?NotificationProviderInterface $provider = null)
    {
        /**
         * @var $notifier StandardNotifier
         */
        $notifier = $this->app->make(StandardNotifier::class);
        if ($provider) {
            foreach ($provider->getNotifications() as $notification) {
                $notifier->getNotificationList()->addNotification($notification);
            }
        }
        $notificationClass = $this->getSubscriptionNotificationClass();
        $notifier->getNotificationList()->addNotification(new $notificationClass($this->app));
        return $notifier;
    }
}
