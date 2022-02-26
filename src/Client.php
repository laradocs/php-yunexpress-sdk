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
     * 运单申请
     *
     * @param array $attributes
     * 用法：
     * ```php
     * createOrder([
     *     [
     *         "CustomerOrderNumber" => "2022021008567562", // 客户订单号，不能重复
     *         "ShippingMethodCode" => "BKPHR", // 运输方式代码
     *         "PackageCount" => 1, // 运单包裹的件数，必须大于 0 的整数
     *         "Weight" => 0.2, // 预估包裹总重量，单位 kg,最多 3 位小数
     *         "Receiver" => [
     *             "CountryCode" => "US", // 收件人所在国家，填写国际通用标准2位简码，可通过国家查询服务查询
     *             "FirstName" => "Test First Name", // 收件人姓
     *             "Street" => "Test Street", // 收件人详细地址
     *             "City" => "Test City", // 收件人所在城市
     *             "Zip" => "12345", // 收件人邮编
     *         ],
     *         "Parcels" => [
     *             [
     *                 "EName": "Test Product", // 包裹申报名称(英文)
     *                 "CName": "测试商品", // 包裹申报名称(中文)
     *                 "Quantity": 1, // 申报数量
     *                 "UnitPrice": 19.99, // 申报价格(单价)
     *                 "UnitWeight": 0.2, // 申报重量(单重)
     *                 "CurrencyCode": "USD", // 申报币种
     *             ]
     *         ]
     *     ]
     *     .
     *     .
     *     .
     * ]);
     * ```
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createOrder(array $attributes): array
    {
        $response = $this->factory()
            ->post('WayBill/CreateOrder', [
                'json' => $attributes,
            ]);

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
     * 修改订单预报重量
     *
     * @param string $orderNumber 订单号
     * @param float $weight 修改重量
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateWeight(string $orderNumber, float $weight): array
    {
        $response = $this->factory()
            ->post('WayBill/UpdateWeight', [
                'json' => [
                    'OrderNumber' => $orderNumber,
                    'Weight' => $weight,
                ]
            ]);

        return $this->body($response);
    }

    /**
     * 订单删除
     *
     * @param int $orderType 单号类型：1-云途单号，2-客户订单号，3-跟踪号
     * @param string $orderNumber 单号
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete(int $orderType, string $orderNumber): array
    {
        $response = $this->factory()
            ->post('WayBill/Delete', [
                'json' => [
                    'OrderType' => $orderType,
                    'OrderNumber' => $orderNumber,
                ]
            ]);

        return $this->body($response);
    }

    /**
     * 订单拦截
     *
     * @param int $orderType 单号类型：1-云途单号，2-客户订单号，3-跟踪号
     * @param string $orderNumber 单号
     * @param string $remark 拦截原因
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function intercept(int $orderType, string $orderNumber, string $remark): array
    {
        $response = $this->factory()
            ->post ( 'WayBill/Intercept', [
                'OrderType' => $orderType,
                'OrderNumber' => $orderNumber,
                'Remark' => $remark,
            ] );

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
