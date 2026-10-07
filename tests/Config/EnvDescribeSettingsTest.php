<?php

namespace NewsletterSignupKit\Tests\Config;

use PHPUnit\Framework\TestCase;
use NewsletterSignupKit\Config\Env;

class EnvDescribeSettingsTest extends TestCase
{
    public function testDescribesPopulatedSecretAndMissingSettings(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($file, "NSK_TEST_SECRET=hunter2\nNSK_TEST_PLAIN=\"us1\"\n");

        try {
            $config = new class($file) extends Env {
                public const SETTINGS = [
                    'secret' => ['NSK_TEST_SECRET', 'Secret', true, true],
                    'plain' => ['NSK_TEST_PLAIN', 'Plain', false, true],
                    'missing' => ['NSK_TEST_MISSING', 'Missing', false, false],
                ];

                protected static ?array $fileValues = null;

                protected static string $path = '';

                public function __construct(string $path)
                {
                    static::$path = $path;
                }

                protected static function envPath(): string
                {
                    return static::$path;
                }

                public function isConfigured(): bool
                {
                    return true;
                }
            };

            $settings = $config->describeSettings();

            $this->assertTrue($settings['secret']['populated']);
            $this->assertSame('••••••••', $settings['secret']['display']);
            $this->assertStringNotContainsString('hunter2', json_encode($settings));
            $this->assertSame('us1', $settings['plain']['display']);
            $this->assertSame('NSK_TEST_PLAIN', $settings['plain']['env']);
            $this->assertFalse($settings['missing']['populated']);
            $this->assertSame('', $settings['missing']['display']);
            $this->assertFalse($settings['missing']['required']);
        } finally {
            unlink($file);
        }
    }
}
