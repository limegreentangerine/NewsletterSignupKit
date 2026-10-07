<?php

namespace NewsletterSignupKit\Tests\Api;

use PHPUnit\Framework\TestCase;
use MailchimpMarketing\ApiClient;
use NewsletterSignupKit\Api\Mailchimp;
use NewsletterSignupKit\Api\ApiInterface;
use NewsletterSignupKit\Config\MailchimpConfig;

class MailchimpTest extends TestCase
{
    private function config(string $apiKey = 'key-us9', string $server = 'us9'): MailchimpConfig
    {
        return new class($apiKey, $server) extends MailchimpConfig {
            public function __construct(private string $key, private string $server) {}

            public function getApiKey(): string
            {
                return $this->key;
            }

            public function getServerPrefix(): string
            {
                return $this->server;
            }
        };
    }

    /**
     * Build a Mailchimp whose SDK client records calls instead of hitting the network.
     *
     * @return array{0: Mailchimp, 1: \ArrayObject} the API and the recorded [name, args] calls
     */
    private function recordingApi(): array
    {
        $calls = new \ArrayObject();
        $recorder = new class($calls) {
            public function __construct(private \ArrayObject $calls) {}

            public function __call(string $name, array $args)
            {
                $this->calls[] = [$name, $args];

                return ['called' => $name];
            }
        };

        $client = new ApiClient();
        $client->lists = $recorder;
        $client->batches = $recorder;

        $api = new Mailchimp($this->config());
        (function () use ($client) {
            $this->client = $client;
        })->call($api);

        return [$api, $calls];
    }

    public function testImplementsApiInterface(): void
    {
        $this->assertInstanceOf(ApiInterface::class, new Mailchimp($this->config()));
    }

    public function testClientIsConfiguredFromConfig(): void
    {
        $api = new Mailchimp($this->config('secret-us9', 'us9'));
        $client = (fn() => $this->client)->call($api);

        $this->assertSame('secret-us9', $client->getPassword());
        $this->assertStringContainsString('us9', $client->getHost());
    }

    public function testGetListsForwardsNamedParams(): void
    {
        [$api, $calls] = $this->recordingApi();

        $api->getLists(['count' => 50, 'offset' => 10]);

        $this->assertSame([['getAllLists', ['count' => 50, 'offset' => 10]]], $calls->getArrayCopy());
    }

    public function testGetListPassesIdThenParams(): void
    {
        [$api, $calls] = $this->recordingApi();

        $api->getList('abc', ['fields' => 'id']);

        $this->assertSame([['getList', ['abc', 'fields' => 'id']]], $calls->getArrayCopy());
    }

    public function testAddMemberHashesLowercasedTrimmedEmail(): void
    {
        [$api, $calls] = $this->recordingApi();

        $api->addMember('list1', '  Jane@Example.COM ', ['status' => 'subscribed'], true);

        $this->assertSame(
            [['setListMember', ['list1', md5('jane@example.com'), ['status' => 'subscribed'], true]]],
            $calls->getArrayCopy(),
        );
    }

    public function testAddSubscriberDelegatesToAddMemberAndReturnsResponse(): void
    {
        [$api, $calls] = $this->recordingApi();

        $result = $api->addSubscriber('list1', 'a@b.co', ['status' => 'pending']);

        $this->assertSame(['called' => 'setListMember'], $result);
        $this->assertSame(
            [['setListMember', ['list1', md5('a@b.co'), ['status' => 'pending'], false]]],
            $calls->getArrayCopy(),
        );
    }

    public function testBatchOperations(): void
    {
        [$api, $calls] = $this->recordingApi();

        $api->runBulkOperation(['operations' => []]);
        $api->getBatchStatus('batch9');

        $this->assertSame(
            [['create', [['operations' => []]]], ['get', ['batch9']]],
            $calls->getArrayCopy(),
        );
    }
}
