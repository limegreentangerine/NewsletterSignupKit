<?php

namespace NewsletterSignupKit\Provider;

use NewsletterSignupKit\Entity\AbstractList;
use NewsletterSignupKit\Entity\Mailchimp\MailchimpList;
use NewsletterSignupKit\Entity\CampaignMonitor\CampaignMonitorList;
use NewsletterSignupKit\Config\{CampaignMonitorConfig, Env, MailchimpConfig};
use NewsletterSignupKit\Search\Result\MailingList\{CampaignMonitor as CampaignMonitorResult, Mailchimp as MailchimpResult};
use NewsletterSignupKit\Search\Column\Set\MailingList\{CampaignMonitor as CampaignMonitorColumns, Mailchimp as MailchimpColumns};
use NewsletterSignupKit\Search\ItemList\MailingList\{CampaignMonitor as CampaignMonitorItemList, Mailchimp as MailchimpItemList};

/**
 * The newsletter providers the dashboard knows about, keyed by the AbstractList discriminator value.
 *
 * To add a provider, add an entry here alongside its config, list entity and search classes.
 */
class Registry
{
    /**
     * Provider key => [label, config, list entity, item list, result, column set].
     */
    protected const PROVIDERS = [
        'mailchimp' => [
            'label' => 'Mailchimp',
            'config' => MailchimpConfig::class,
            'entity' => MailchimpList::class,
            'itemList' => MailchimpItemList::class,
            'result' => MailchimpResult::class,
            'columns' => MailchimpColumns::class,
        ],
        'campaignmonitor' => [
            'label' => 'Campaign Monitor',
            'config' => CampaignMonitorConfig::class,
            'entity' => CampaignMonitorList::class,
            'itemList' => CampaignMonitorItemList::class,
            'result' => CampaignMonitorResult::class,
            'columns' => CampaignMonitorColumns::class,
        ],
    ];

    /**
     * @return array{label: string, config: class-string, entity: class-string, itemList: class-string, result: class-string, columns: class-string}
     */
    protected static function definition(string $key): array
    {
        return self::PROVIDERS[$key] ?? throw new \InvalidArgumentException('Unknown newsletter provider: ' . $key);
    }

    /**
     * @return string[] Every provider key
     */
    public static function keys(): array
    {
        return array_keys(self::PROVIDERS);
    }

    public static function has(string $key): bool
    {
        return isset(self::PROVIDERS[$key]);
    }

    public static function label(string $key): string
    {
        return t(self::definition($key)['label']);
    }

    public static function config(string $key): Env
    {
        $class = self::definition($key)['config'];

        return new $class();
    }

    /**
     * @return class-string<\NewsletterSignupKit\Entity\AbstractList>
     */
    public static function entityClass(string $key): string
    {
        return self::definition($key)['entity'];
    }

    public static function itemListClass(string $key): string
    {
        return self::definition($key)['itemList'];
    }

    public static function resultClass(string $key): string
    {
        return self::definition($key)['result'];
    }

    public static function columnSetClass(string $key): string
    {
        return self::definition($key)['columns'];
    }

    /**
     * The provider key a list entity belongs to.
     */
    public static function keyFor(AbstractList $list): string
    {
        foreach (self::PROVIDERS as $key => $definition) {
            if ($list instanceof $definition['entity']) {
                return $key;
            }
        }

        throw new \InvalidArgumentException('No newsletter provider for ' . $list::class);
    }

    /**
     * @return string[] Keys of the providers whose .env values are all present
     */
    public static function configured(): array
    {
        return array_values(array_filter(self::keys(), fn(string $key) => self::config($key)->isConfigured()));
    }
}
