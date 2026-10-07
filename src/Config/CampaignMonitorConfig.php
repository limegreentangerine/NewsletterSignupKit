<?php

namespace NewsletterSignupKit\Config;

class CampaignMonitorConfig extends Env
{
    public const SETTINGS = [
        'api_key' => ['CAMPAIGNMONITOR_API_KEY', 'API Key', true, true],
    ];

    public function getApiKey(): string
    {
        return static::get('CAMPAIGNMONITOR_API_KEY');
    }

    public function isConfigured(): bool
    {
        return $this->getApiKey() !== '';
    }
}
