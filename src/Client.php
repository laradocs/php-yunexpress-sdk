<?php

namespace Laradocs\YunExpress;

use GuzzleHttp\Psr7\Response;
use JsonException;
use GuzzleHttp\Client as Guzzle;
use Laradocs\YunExpress\Exceptions\ParamInvalidException;
use Laradocs\YunExpress\Exceptions\TokenExpiredException;

class Client
{
    protected $baseUri = 'http://oms.api.yunexpress.com/api/';

    protected $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function factory(): Guzzle
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
     * @param string|null $countryCode 国家简码，未填写国家代表查询所有运输方式
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
     * @param string $countryCode 国家简码
     * @param float $weight 包裹重量，单位 kg，支持 3 位小数
     * @param int $packageType 包裹长度，单位 cm，不带小数，不填写默认 1
     * @param int $length 包裹宽度，单位 cm，不带小数，不填写默认 1
     * @param int $width 包裹高度，单位 cm，不带小数，不填写默认 1
     * @param int $height 包裹类型，1-带电，0-普货，默认 1
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPriceTrial(string $countryCode, float $weight, int $packageType = 1, int $length = 1, int $width = 1, int $height = 1): array
    {
        $response = $this->factory()
            ->get('Freight/GetPriceTrial?CountryCode=' . $countryCode . '&Weight=' . $weight . '&Length=' . $length . '&Width=' . $width . '&Height=' . $height . '&PackageType=' . $packageType);

        return $this->body($response);
    }

    /**
     * 查询跟踪号
     *
     * @param string $customerOrderNumber 客户订单号，多个以逗号分开
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTrackingNumber(string $customerOrderNumber): array
    {
        $response = $this->factory()
            ->get('Waybill/GetTrackingNumber?CustomerOrderNumber=' . $customerOrderNumber);

        return $this->body($response);
    }

    /**
     * 查询发件人信息
     *
     * @param string $orderNumber 查询号码，可输入运单号、订单号、跟踪号
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSender(string $orderNumber): array
    {
        $response = $this->factory()
            ->get('WayBill/GetSender?OrderNumber=' . $orderNumber);

        return $this->body($response);
    }

    /**
     * 查询运单
     *
     * @param string $orderNumber 物流系统运单号，客户订单或跟踪号
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOrder(string $orderNumber): array
    {
        $response = $this->factory()
            ->get('WayBill/GetOrder?OrderNumber=' . $orderNumber);

        return $this->body($response);
    }

    /**
     * 查询物流运费明细
     *
     * @param string $wayBillNumber 运单号
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getShippingFeeDetail(string $wayBillNumber): array
    {
        $response = $this->factory()
            ->get('Freight/GetShippingFeeDetail?wayBillNumber=' . $wayBillNumber);

        return $this->body($response);
    }

    /**
     * 查询物流轨迹信息
     * 根据轨迹订阅查询轨迹
     *
     * @param string $orderNumber 物流系统运单号，客户订单或跟踪号
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTrackInfo(string $orderNumber): array
    {
        $response = $this->factory()
            ->get('Tracking/GetTrackInfo?OrderNumber=' . $orderNumber);

        return $this->body($response);
    }

    /**
     * 查询物流轨迹信息
     * 查询全程轨迹
     *
     * @param string $orderNumber 物流系统运单号，客户订单或跟踪号
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTrackAllInfo(string $orderNumber): array
    {
        $response = $this->factory()
            ->get('Tracking/GetTrackAllInfo?OrderNumber=' . $orderNumber);

        return $this->body($response);
    }

    /**
     * 查询末端派送商
     *
     * @param array $orderNumbers 查询号码，可输入运单号、订单号、跟踪号
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCarrier(array $orderNumbers): array
    {
        $response = $this->factory()
            ->post('Waybill/GetCarrier', [
                'json' => $orderNumbers,
            ]);

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
            if ($data['Code'] !== '401') {
                throw new ParamInvalidException($data['Message']);
            }
            throw new TokenExpiredException($data['Message']);
        }

        return $data['Items'] ?? $data['Item'] ?? [];
    }
}
