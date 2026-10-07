<?php

namespace NewsletterSignupKit\Command;

use NewsletterSignupKit\Entity\AbstractSubscriber;
use NewsletterSignupKit\Log\CampaignMonitorLogger;
use NewsletterSignupKit\Config\{CampaignMonitorConfig, Env};
use NewsletterSignupKit\Api\CampaignMonitor as CampaignMonitorApi;
use NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorSubscriber;

/**
 * @extends AbstractAddSubscribersCommandHandler<CampaignMonitorSubscriber>
 */
class AddCampaignMonitorSubscribersCommandHandler extends AbstractAddSubscribersCommandHandler
{
    protected CampaignMonitorConfig $config;

    protected ?CampaignMonitorApi $api = null;

    public function __construct()
    {
        $this->config = new CampaignMonitorConfig();
    }

    public function __invoke(AddCampaignMonitorSubscribersCommand $command)
    {
        return $this->importSubscribers();
    }

    protected function getSubscriberClass(): string
    {
        return CampaignMonitorSubscriber::class;
    }

    protected function getLoggerClass(): string
    {
        return CampaignMonitorLogger::class;
    }

    protected function getConfig(): Env
    {
        return $this->config;
    }

    protected function getMissingConfigMessage(): string
    {
        return t('Campaign Monitor API configuration missing.');
    }

    /**
     * @param CampaignMonitorSubscriber $subscriber
     */
    protected function addSubscriber(AbstractSubscriber $subscriber): void
    {
        $this->api ??= new CampaignMonitorApi($this->config);

        $args = [];

        // Campaign Monitor takes a single Name, so join first and last with a space
        $name = $subscriber->getFullName();
        if ($name !== '') {
            $args['Name'] = $name;
        }

        $result = $this->api->addSubscriber($subscriber->getListId(), $subscriber->getEmail(), $args);

        if (!$result->was_successful()) {
            $message = $result->response->Message ?? t('HTTP %s', $result->http_status_code);

            throw new \RuntimeException($message);
        }
    }
}
