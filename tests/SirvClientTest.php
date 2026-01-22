<?php

declare(strict_types=1);

namespace Sirv\Tests;

use PHPUnit\Framework\TestCase;
use Sirv\SirvClient;
use Sirv\HttpClient;
use Sirv\Resources\Account;
use Sirv\Resources\Files;
use Sirv\Resources\Stats;
use Sirv\Resources\User;

class SirvClientTest extends TestCase
{
    private SirvClient $client;

    protected function setUp(): void
    {
        $this->client = new SirvClient('test-client-id', 'test-client-secret');
    }

    public function testClientCanBeInstantiated(): void
    {
        $this->assertInstanceOf(SirvClient::class, $this->client);
    }

    public function testClientHasCorrectVersion(): void
    {
        $this->assertEquals('1.0.0', SirvClient::VERSION);
    }

    public function testClientHasCorrectApiVersion(): void
    {
        $this->assertEquals('v2', SirvClient::API_VERSION);
    }

    public function testAccountResourceIsReturned(): void
    {
        $account = $this->client->account();
        $this->assertInstanceOf(Account::class, $account);
    }

    public function testFilesResourceIsReturned(): void
    {
        $files = $this->client->files();
        $this->assertInstanceOf(Files::class, $files);
    }

    public function testStatsResourceIsReturned(): void
    {
        $stats = $this->client->stats();
        $this->assertInstanceOf(Stats::class, $stats);
    }

    public function testUserResourceIsReturned(): void
    {
        $user = $this->client->user();
        $this->assertInstanceOf(User::class, $user);
    }

    public function testResourcesAreCached(): void
    {
        $account1 = $this->client->account();
        $account2 = $this->client->account();
        $this->assertSame($account1, $account2);
    }

    public function testMagicGetterReturnsResources(): void
    {
        $this->assertInstanceOf(Account::class, $this->client->account);
        $this->assertInstanceOf(Files::class, $this->client->files);
        $this->assertInstanceOf(Stats::class, $this->client->stats);
        $this->assertInstanceOf(User::class, $this->client->user);
    }

    public function testMagicGetterReturnsNullForUnknownProperty(): void
    {
        $this->assertNull($this->client->unknown);
    }

    public function testHttpClientIsAccessible(): void
    {
        $httpClient = $this->client->getHttpClient();
        $this->assertInstanceOf(HttpClient::class, $httpClient);
    }

    public function testCustomTimeoutCanBeSet(): void
    {
        $client = new SirvClient('id', 'secret', 120);
        $this->assertInstanceOf(SirvClient::class, $client);
    }

    public function testCustomGuzzleOptionsCanBeSet(): void
    {
        $client = new SirvClient('id', 'secret', 30, [
            'timeout' => 60,
        ]);
        $this->assertInstanceOf(SirvClient::class, $client);
    }
}
