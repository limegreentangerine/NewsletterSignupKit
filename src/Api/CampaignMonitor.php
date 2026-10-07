<?php

namespace NewsletterSignupKit\Api;

use CS_REST_Clients;
use CS_REST_General;
use CS_REST_Subscribers;
use NewsletterSignupKit\Config\CampaignMonitorConfig;

class CampaignMonitor implements ApiInterface
{
    protected CampaignMonitorConfig $config;

    public function __construct(?CampaignMonitorConfig $config = null)
    {
        $this->config = $config ?? new CampaignMonitorConfig();
    }

    protected function getAuth(): array
    {
        return ['api_key' => $this->config->getApiKey()];
    }

    /**
     * Get all clients on the account
     *
     * @return \CS_REST_Wrapper_Result Response is an array of {ClientID, Name}
     */
    public function getClients()
    {
        $general = new CS_REST_General($this->getAuth());

        return $general->get_clients();
    }

    /**
     * Get all mailing lists belonging to a client
     *
     * @return \CS_REST_Wrapper_Result Response is an array of {ListID, Name}
     */
    public function getLists(string $clientId)
    {
        $clients = new CS_REST_Clients($clientId, $this->getAuth());

        return $clients->get_lists();
    }

    /**
     * Add a single subscriber to a list
     *
     * @param array $args Extra subscriber fields, e.g. Name, CustomFields, ConsentToTrack, Resubscribe
     *
     * @return \CS_REST_Wrapper_Result
     */
    public function addSubscriber(string $listId, string $email, array $args = [])
    {
        $subscribers = new CS_REST_Subscribers($listId, $this->getAuth());

        return $subscribers->add(array_merge($args, [
            'EmailAddress' => $email,
            'ConsentToTrack' => $args['ConsentToTrack'] ?? 'yes',
        ]));
    }

    /**
     * Import many subscribers into a list in one request
     *
     * @param array $subscribers Each item: EmailAddress, Name, CustomFields
     *
     * @return \CS_REST_Wrapper_Result Response includes TotalNewSubscribers and FailureDetails
     */
    public function addSubscribersBulkAction(string $listId, array $subscribers, bool $resubscribe = false)
    {
        $subscribersApi = new CS_REST_Subscribers($listId, $this->getAuth());

        return $subscribersApi->import($subscribers, $resubscribe);
    }
}
