<?php

namespace NewsletterSignupKit\Command;

use NewsletterSignupKit\Entity\AbstractList;
use NewsletterSignupKit\Log\CampaignMonitorLogger;
use NewsletterSignupKit\Config\{CampaignMonitorConfig, Env};
use NewsletterSignupKit\Api\CampaignMonitor as CampaignMonitorApi;
use NewsletterSignupKit\Entity\CampaignMonitor\{CampaignMonitorList, MailingClient};

/**
 * @extends AbstractGetListsCommandHandler<CampaignMonitorList>
 */
class GetCampaignMonitorListsCommandHandler extends AbstractGetListsCommandHandler
{
    protected CampaignMonitorConfig $config;

    public function __construct()
    {
        $this->config = new CampaignMonitorConfig();
    }

    public function __invoke(GetCampaignMonitorListsCommand $command)
    {
        return $this->importLists();
    }

    protected function getListClass(): string
    {
        return CampaignMonitorList::class;
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

    protected function fetchLists(): iterable
    {
        $api = new CampaignMonitorApi($this->config);

        $clients = [];
        foreach ($this->entityManager->getRepository(MailingClient::class)->findAll() as $existing) {
            $clients[$existing->getClientId()] = $existing;
        }

        foreach ($this->unwrap($api->getClients()) as $clientData) {
            $this->output->write(t('Getting mailing lists for client: %s', $clientData->Name));

            $client = $clients[$clientData->ClientID] ?? new MailingClient();
            $clients[$clientData->ClientID] = $client;
            $client->setClientId($clientData->ClientID);
            $client->setName($clientData->Name);
            $this->entityManager->persist($client);

            foreach ($this->unwrap($api->getLists($clientData->ClientID)) as $list) {
                yield [
                    'id' => $list->ListID,
                    'name' => $list->Name,
                    'client' => $client,
                ];
            }
        }
    }

    /**
     * @param CampaignMonitorList $list
     */
    protected function hydrateList(AbstractList $list, array $data): void
    {
        $list->setClient($data['client']);
    }

    /**
     * @param \CS_REST_Wrapper_Result $result
     */
    protected function unwrap($result): array
    {
        if (!$result->was_successful()) {
            throw new \RuntimeException(t('Campaign Monitor API request failed (HTTP %s).', $result->http_status_code));
        }

        return $result->response;
    }
}
