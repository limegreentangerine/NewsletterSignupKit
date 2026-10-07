<?php

namespace NewsletterSignupKit\Command;

use NewsletterSignupKit\Entity\AbstractList;
use NewsletterSignupKit\Log\MailchimpLogger;
use NewsletterSignupKit\Api\Mailchimp as MailchimpApi;
use NewsletterSignupKit\Config\{Env, MailchimpConfig};
use NewsletterSignupKit\Entity\Mailchimp\MailchimpList;

/**
 * @extends AbstractGetListsCommandHandler<MailchimpList>
 */
class GetMailchimpListsCommandHandler extends AbstractGetListsCommandHandler
{
    protected const PAGE_SIZE = 100;

    protected MailchimpConfig $config;

    public function __construct()
    {
        $this->config = new MailchimpConfig();
    }

    public function __invoke(GetMailchimpListsCommand $command)
    {
        return $this->importLists();
    }

    protected function getListClass(): string
    {
        return MailchimpList::class;
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

    protected function fetchLists(): iterable
    {
        $api = new MailchimpApi($this->config);
        $offset = 0;

        do {
            $response = $api->getLists(['count' => static::PAGE_SIZE, 'offset' => $offset]);
            $lists = $response->lists ?? [];

            foreach ($lists as $list) {
                yield [
                    'id' => $list->id,
                    'name' => $list->name,
                    'webId' => $list->web_id,
                    'visibility' => $list->visibility,
                ];
            }

            $offset += static::PAGE_SIZE;
        } while ($offset < ($response->total_items ?? 0));
    }

    /**
     * @param MailchimpList $list
     */
    protected function hydrateList(AbstractList $list, array $data): void
    {
        $list->setListWebId($data['webId']);
        $list->setVisibility($data['visibility']);
    }
}
