<?php

namespace NewsletterSignupKit\Command;

use NewsletterSignupKit\Log\MailchimpLogger;
use NewsletterSignupKit\Entity\AbstractSubscriber;
use NewsletterSignupKit\Api\Mailchimp as MailchimpApi;
use NewsletterSignupKit\Config\{Env, MailchimpConfig};
use NewsletterSignupKit\Entity\Mailchimp\MailchimpSubscriber;

/**
 * @extends AbstractAddSubscribersCommandHandler<MailchimpSubscriber>
 */
class AddMailchimpSubscribersCommandHandler extends AbstractAddSubscribersCommandHandler
{
    protected MailchimpConfig $config;

    protected ?MailchimpApi $api = null;

    public function __construct()
    {
        $this->config = new MailchimpConfig();
    }

    public function __invoke(AddMailchimpSubscribersCommand $command)
    {
        return $this->importSubscribers();
    }

    protected function getSubscriberClass(): string
    {
        return MailchimpSubscriber::class;
    }

    protected function getLoggerClass(): string
    {
        return MailchimpLogger::class;
    }

    protected function getConfig(): Env
    {
        return $this->config;
    }

    protected function getMissingConfigMessage(): string
    {
        return t('Mailchimp API configuration missing.');
    }

    /**
     * @param MailchimpSubscriber $subscriber
     */
    protected function addSubscriber(AbstractSubscriber $subscriber): void
    {
        $this->api ??= new MailchimpApi($this->config);

        $args = [
            'email_address' => $subscriber->getEmail(),
            // Only applies to new members, so a previously unsubscribed address isn't resubscribed
            'status_if_new' => 'subscribed',
        ];

        $mergeFields = array_filter([
            'FNAME' => $subscriber->getFirstName(),
            'LNAME' => $subscriber->getLastName(),
        ]);

        if (count($mergeFields) > 0) {
            $args['merge_fields'] = $mergeFields;
        }

        $this->api->addSubscriber($subscriber->getListId(), $subscriber->getEmail(), $args);
    }
}
