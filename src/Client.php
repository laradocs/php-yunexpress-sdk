<?php

namespace Laradocs\YunExpress;

use JsonException;
use GuzzleHttp\Client as Guzzle;
use Laradocs\YunExpress\Exceptions\TokenExpiredException;

class Client
{
    protected $baseUri = 'http://oms.api.yunexpress.com/api';

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
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCountry()
    {
        $response = $this->factory()
            ->get('Common/GetCountry');

        return $this->body($response);
    }

    protected function body($response)
    {
        $body = $response->getBody();
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new TokenExpiredException();
        }
        if ( $data['Code'] !== '0000' ) {
            throw new TokenExpiredException();
        }

        return $data [ 'Items' ];
    }
}
