<?php

namespace NewsletterSignupKit\Api;

use MailchimpMarketing\ApiClient;
use NewsletterSignupKit\Config\MailchimpConfig;

class Mailchimp implements ApiInterface
{
    protected ApiClient $client;
    protected MailchimpConfig $config;

    public function __construct(?MailchimpConfig $config = null)
    {
        $this->config = $config ?? new MailchimpConfig();
        $this->client = new ApiClient();
        $this->client->setConfig([
            'apiKey' => $this->config->getApiKey(),
            'server' => $this->config->getServerPrefix(),
        ]);
    }

    /**
     * Get mailing lists
     *
     * @param array $params Named arguments of getAllLists(), e.g. ['count' => 100, 'offset' => 0]
     */
    public function getLists(array $params = [])
    {
        return $this->client->lists->getAllLists(...$params);
    }

    /**
     * Get a single mailing list
     */
    public function getList(string $id, array $params = [])
    {
        return $this->client->lists->getList($id, ...$params);
    }

    /**
     * Add or update a subscriber
     */
    public function addMember(string $listId, string $email, array $args = [], bool $skipMergeValidation = false)
    {
        $subscriberHash = md5(strtolower(trim($email)));
        return $this->client->lists->setListMember($listId, $subscriberHash, $args, $skipMergeValidation);
    }

    /**
     * Subscribe a single email address to a list (see ApiInterface)
     *
     * @param array $args Passed to setListMember, e.g. status, merge_fields
     */
    public function addSubscriber(string $listId, string $email, array $args = [])
    {
        return $this->addMember($listId, $email, $args);
    }

    /**
     * Run a bulk operation
     */
    public function runBulkOperation(array $operations)
    {
        return $this->client->batches->create($operations);
    }

    /**
     * Get batch operation status
     */
    public function getBatchStatus(string $batchId)
    {
        return $this->client->batches->get($batchId);
    }
}
