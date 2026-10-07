<?php

namespace NewsletterSignupKit\Config;

class MailchimpConfig extends Env
{
    public const SETTINGS = [
        'api_key' => ['MAILCHIMP_API_KEY', 'API Key', true, true],
        'server_prefix' => ['MAILCHIMP_SERVER_PREFIX', 'Server Prefix', false, true],
    ];

    public function getApiKey(): string
    {
        return static::get('MAILCHIMP_API_KEY');
    }

    public function getServerPrefix(): string
    {
        return static::get('MAILCHIMP_SERVER_PREFIX');
    }

    public function isConfigured(): bool
    {
        return $this->getApiKey() !== '' && $this->getServerPrefix() !== '';
    }
}
