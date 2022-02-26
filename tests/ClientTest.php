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

    public function testGetShippingMethods()
    {
        $factory = Mockery::mock(Client::class . '[factory]', ['xxx']);
        $factory->shouldReceive('factory')->andReturn($this->factory());
        $data = $factory->getShippingMethods();
        $this->assertNotEmpty($data);
        $this->assertSame('ZDZXR', $data[0]['Code']);
    }

    public function testGetGoodsType()
    {
        $factory = Mockery::mock(Client::class . '[factory]', ['xxx']);
        $factory->shouldReceive('factory')->andReturn($this->factory());
        $data = $factory->getGoodsType();
        $this->assertNotEmpty($data);
        $this->assertSame(2, $data[0]['Id']);
    }

    protected function factory()
    {
        $factory = Mockery::mock(Guzzle::class);
        $factory->shouldReceive('get')->withAnyArgs()->andReturnUsing(function ($url) {
            if (str_contains($url, 'GetCountry')) {
                $body = file_get_contents(__DIR__ . '/get_country.json');
            }
            if (str_contains($url, 'GetShippingMethods')) {
                $body = file_get_contents(__DIR__ . '/get_shipping_methods.json');
            }
            if (str_contains($url, 'GetGoodsType')) {
                $body = file_get_contents(__DIR__ . '/get_goods_type.json');
            }

            return new Response(200, [], $body);
        });

        return $factory;
    }
}
