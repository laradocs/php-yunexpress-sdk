<?php

namespace Laradocs\YunExpress\Tests;

use GuzzleHttp\Psr7\Response;
use Laradocs\YunExpress\Client;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as Guzzle;
use Mockery;

class ClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetCountry()
    {
        $factory = Mockery::mock(Client::class . '[factory]', ['xxx']);
        $factory->shouldReceive('factory')->andReturn($this->factory());
        $data = $factory->getCountry();
        $this->assertNotEmpty($data);
        $this->assertSame('AD', $data[0]['CountryCode']);
    }

    protected function factory()
    {
        $factory = Mockery::mock(Guzzle::class);
        $factory->shouldReceive('get')->withAnyArgs()->andReturnUsing(function ($url) {
            if (str_contains($url, 'GetCountry')) {
                $body = file_get_contents(__DIR__ . '/get_country.json');
            }

            return new Response(200, [], $body);
        });

        return $factory;
    }
}
