<?php

namespace Laradocs\YunExpress;

use GuzzleHttp\Psr7\Response;
use JsonException;
use GuzzleHttp\Client as Guzzle;
use Laradocs\YunExpress\Exceptions\TokenExpiredException;

class Client
{
    protected $baseUri = 'http://oms.api.yunexpress.com/api/';

    protected $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function factory()
    {
        $config = [
            'base_uri' => $this->baseUri,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $this->token,
            ]
        ];
        $factory = new Guzzle($config);

        return $factory;
    }

    /**
     * 查询国家简码
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCountry(): array
    {
        $response = $this->factory()
            ->get('Common/GetCountry');

        return $this->body($response);
    }

    /**
     * 查询运输方式
     *
     * @param string|null $countryCode
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getShippingMethods(?string $countryCode = null): array
    {
        if (!is_null($countryCode)) {
            $countryCode = '?CountryCode=' . $countryCode;
        }
        $response = $this->factory()
            ->get('Common/GetShippingMethods' . $countryCode);

        return $this->body($response);
    }

    /**
     * 查询货品类型
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getGoodsType()
    {
        $response = $this->factory()
            ->get('Common/GetGoodsType');

        return $this->body($response);
    }

    /**
     * 查询价格
     *
     * @param string $countryCode
     * @param float $weight
     * @param int $packageType
     * @param int|null $length
     * @param int|null $width
     * @param int|null $height
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPriceTrial(string $countryCode, float $weight, int $packageType = 1, ?int $length = null, ?int $width = null, ?int $height = null)
    {
        if (!is_null($length)) {
            $length = '&Length=' . $length;
        }
        if (!is_null($width)) {
            $width = '&Width=' . $width;
        }
        if (!is_null($height)) {
            $height = '&Height=' . $height;
        }
        $response = $this->factory()
            ->get("Freight/GetPriceTrial?CountryCode={$countryCode}&Weight={$weight}&packageType={$packageType}{$length}{$width}{$height}");

        return $this->body($response);
    }

    protected function body(Response $response): array
    {
        $body = $response->getBody();
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new TokenExpiredException();
        }
        if ($data['Code'] !== '0000') {
            throw new TokenExpiredException();
        }

        return $data ['Items'] ?? [];
    }
}
