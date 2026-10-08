<?php

namespace NewsletterSignupKit\Tests\Api;

use PHPUnit\Framework\TestCase;
use NewsletterSignupKit\Api\ApiInterface;
use NewsletterSignupKit\Api\CampaignMonitor;
use NewsletterSignupKit\Config\CampaignMonitorConfig;

class CampaignMonitorTest extends TestCase
{
    private function config(string $apiKey): CampaignMonitorConfig
    {
        return new class($apiKey) extends CampaignMonitorConfig {
            public function __construct(private string $key) {}

            public function getApiKey(): string
            {
                return $this->key;
            }
        };
    }

    private function authOf(CampaignMonitor $api): array
    {
        return (fn() => $this->getAuth())->call($api);
    }

    public function testImplementsApiInterface(): void
    {
        $this->assertInstanceOf(ApiInterface::class, new CampaignMonitor($this->config('k')));
    }

    public function testAuthUsesApiKeyFromInjectedConfig(): void
    {
        $api = new CampaignMonitor($this->config('abc123'));

        $this->assertSame(['api_key' => 'abc123'], $this->authOf($api));
    }

    public function testDefaultsToEnvironmentConfig(): void
    {
        $_ENV['CAMPAIGNMONITOR_API_KEY'] = 'from-env';

        try {
            $this->assertSame(['api_key' => 'from-env'], $this->authOf(new CampaignMonitor()));
        } finally {
            unset($_ENV['CAMPAIGNMONITOR_API_KEY']);
        }
    }

    public function testUnconfiguredConfigGivesEmptyKeyAndReportsNotConfigured(): void
    {
        $config = $this->config('');

        $this->assertFalse($config->isConfigured());
        $this->assertSame(['api_key' => ''], $this->authOf(new CampaignMonitor($config)));
    }

    public function testApiMethodSignaturesMatchInterface(): void
    {
        $method = new \ReflectionMethod(CampaignMonitor::class, 'addSubscriber');

        $this->assertSame(['listId', 'email', 'args'], array_map(fn($p) => $p->getName(), $method->getParameters()));
    }
}
