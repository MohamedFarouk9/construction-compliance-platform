<?php

declare(strict_types=1);

namespace App\Shared\Tests\Functional;

use App\Shared\Domain\Health\DatabaseHealthProbeInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class HealthEndpointTest extends WebTestCase
{
    public function testHealthEndpointReturnsOkWhenDatabaseIsReachable(): void
    {
        $client = static::createClient();
        static::getContainer()->set(
            DatabaseHealthProbeInterface::class,
            new class implements DatabaseHealthProbeInterface {
                public function isReachable(): bool
                {
                    return true;
                }
            },
        );

        $client->request('GET', '/health');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseHeaderSame('content-type', 'application/json');

        /** @var array{service?: string, status?: string, checks?: array{database?: array{status?: string}}, timestamp?: string} $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('buildguard-api', $payload['service'] ?? null);
        self::assertSame('ok', $payload['status'] ?? null);
        self::assertSame('up', $payload['checks']['database']['status'] ?? null);
        self::assertNotEmpty($payload['timestamp'] ?? '');
    }

    public function testHealthEndpointReturnsServiceUnavailableWhenDatabaseIsDown(): void
    {
        $client = static::createClient();
        static::getContainer()->set(
            DatabaseHealthProbeInterface::class,
            new class implements DatabaseHealthProbeInterface {
                public function isReachable(): bool
                {
                    return false;
                }
            },
        );

        $client->request('GET', '/health');

        self::assertResponseStatusCodeSame(Response::HTTP_SERVICE_UNAVAILABLE);
        self::assertResponseHeaderSame('content-type', 'application/json');

        $rawContent = (string) $client->getResponse()->getContent();
        /** @var array{service?: string, status?: string, checks?: array{database?: array{status?: string}}, timestamp?: string} $payload */
        $payload = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('buildguard-api', $payload['service'] ?? null);
        self::assertSame('degraded', $payload['status'] ?? null);
        self::assertSame('down', $payload['checks']['database']['status'] ?? null);
        self::assertArrayNotHasKey('error', $payload);
        self::assertArrayNotHasKey('exception', $payload);
        self::assertStringNotContainsStringIgnoringCase('sql', $rawContent);
        self::assertStringNotContainsStringIgnoringCase('pdo', $rawContent);
    }

    public function testHealthEndpointRejectsPostMethod(): void
    {
        $client = static::createClient();
        $client->request('POST', '/health');

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
