# 云途物流 SDK

[![Total Downloads](https://poser.pugx.org/laradocs/yunexpress/d/total.svg)](https://packagist.org/packages/laradocs/yunexpress)
[![Latest Stable Version](https://poser.pugx.org/laradocs/yunexpress/v/stable.svg)](https://packagist.org/packages/laradocs/yunexpress)
[![Latest Unstable Version](https://poser.pugx.org/laradocs/yunexpress/v/unstable.svg)](https://packagist.org/packages/laradocs/yunexpress)
[![License](https://poser.pugx.org/laradocs/yunexpress/license.svg)](https://packagist.org/packages/laradocs/yunexpress)

基于云途物流 API 的组件包。

## 安装

PHP 版本：^7.3|^8.0

直接在您的项目路径中运行以下命令：

```bash
composer require laradocs/yunexpress
```

## 用法

### 查询国家简码

```php
use Laradocs\YunExpress\Client;

$factroy = new Client();
$factory->getCountry();
```

### 查询运输方式

```php
use Laradocs\YunExpress\Client;

$factroy = new Client();
// @param ?string $countryCode 国家简码(非必填)
$factroy->getShippingMethods($countryCode);
```

### 查询货品类型
```php
use Laradocs\YunExpress\Client;

$factroy = new Client();
$factroy->getGoodsType();
```

### 查询价格

```php
use Laradocs\YunExpress\Client;

$factroy = new Client();
// @param string $countryCode 国家简码
// @param float $weight 包裹重量：单位 kg(支持 3 位小数)
// @param int $packageType 包裹长度：单位 cm(非必填，默认为 1)
// @param int $length 包裹宽度：单位 cm(非必填，默认为 1)
// @param int $width 包裹高度：单位 cm(非必填，默认为 1)
// @param int $height 包裹类型：1-带电|0-普货(非必填，默认为 1)
$factroy->getPriceTrial($countryCode, $weight, $packageType, $length, $width, $height);
```

### 查询跟踪号

```php
use Laradocs\YunExpress\Client;

$factroy = new Client();
// @param string $customerOrderNumber 客户订单号，多个以逗号分开
// 例：YT2222222222,YT11111111111
$factroy->getTrackingNumber($customerOrderNumber);
```

### 查询发件人信息

```php
use Laradocs\YunExpress\Client;

$factroy = new Client();
// @param string $orderNumber 查询号码：运单号|订单号|跟踪号
$factroy->getSender($orderNumber);
```

### 运单申请

```php
use Laradocs\YunExpress\Client;

$factroy = new Client();
// 支持批量申请，一次最多 10 条
// 非必填数据请查看文档
// @param array $attributes
// [
//     [
//         "CustomerOrderNumber" => "2022021008567562", // 客户订单号，不能重复
//         "ShippingMethodCode" => "BKPHR", // 运输方式代码
//         "PackageCount" => 1, // 运单包裹的件数，必须大于 0 的整数
//         "Weight" => 0.2, // 预估包裹总重量，单位 kg,最多 3 位小数
//         "Receiver" => [
//             "CountryCode" => "US", // 收件人所在国家，填写国际通用标准2位简码，可通过国家查询服务查询
//             "FirstName" => "Test First Name", // 收件人姓
//             "Street" => "Test Street", // 收件人详细地址
//             "City" => "Test City", // 收件人所在城市
//             "Zip" => "12345", // 收件人邮编
//         ],
//         "Parcels" => [
//             [
//                 "EName": "Test Product", // 包裹申报名称(英文)
//                 "CName": "测试商品", // 包裹申报名称(中文)
//                 "Quantity": 1, // 申报数量
//                 "UnitPrice": 19.99, // 申报价格(单价)
//                 "UnitWeight": 0.2, // 申报重量(单重)
//                 "CurrencyCode": "USD", // 申报币种
//             ]
//         ]
//     ]
//     .
//     .
//     .
// ]
$factroy->createOrder($attributes);
```

### 查询运单

```php
use Laradocs\YunExpress\Client;

$factroy = new Client();
// @param string $orderNumber 物流系统运单号：客户订单|跟踪号
$factroy->getOrder($orderNumber);
```

### 修改订单预报重量

```php
use Laradocs\YunExpress\Client;

$factroy = new Client();
// @param string $orderNumber 订单号
// @param float $weight 修改重量
$factroy->updateWeight($orderNumber, $weight);
```

### 订单删除

```php
use Laradocs\YunExpress\Client;

$factroy = new Client();
// @param int $orderType 单号类型：1-云途单号|2-客户订单号|3-跟踪号
// @param string $orderNumber 单号
$factroy->delete($orderType, $orderNumber);
```

### 订单拦截

```php
use Laradocs\YunExpress\Client;

// @param string $token Token：加密后的字符串
$factroy = new Client($token);
// @param int $orderType 单号类型：1-云途单号|2-客户订单号|3-跟踪号
// @param string $orderNumber 单号
// @param string $remark 拦截原因
$factroy->intercept($orderType, $orderNumber, $remark);
```

### 标签打印

```php
use Laradocs\YunExpress\Client;

// @param string $token Token：加密后的字符串
$factroy = new Client($token);
// @param array $orderNumbers 物流系统运单号：客户订单|跟踪号
$factroy->labelPrint($orderNumbers);
```

### 查询物流运费明细

```php
use Laradocs\YunExpress\Client;

// @param string $token Token：加密后的字符串
$factroy = new Client($token);
// @param string $wayBillNumber 运单号
$factroy->getShippingFeeDetail($wayBillNumber);
```

### 用户注册

```php
use Laradocs\YunExpress\Client;

// @param string $token Token：加密后的字符串
$factroy = new Client($token);
// @param string $username 用户名
// @param string $password 密码
// @param string $contact 联系人
// @param string $mobile 联系人电话
// @param string $telephone 联系人电话
// @param string $name 客户名称|公司名称
// @param string $email 邮箱
// @param string $address 详细地址
// @param int $platForm 平台 ID(通途平台--2)
$factroy->register($username, $password, $contact, $mobile, $telephone, $name, $email, $address, $platForm);
```

### 查询物流轨迹信息

#### 根据轨迹订阅查询轨迹

```php
use Laradocs\YunExpress\Client;

// @param string $token Token：加密后的字符串
$factroy = new Client($token);
// @param string $orderNumber 物流系统运单号：客户订单|跟踪号
$factroy->getTrackInfo($orderNumber);
```

#### 查询全程轨迹

```php
use Laradocs\YunExpress\Client;

// @param string $token Token：加密后的字符串
$factroy = new Client($token);
// @param string $orderNumber 物流系统运单号：客户订单|跟踪号
$factroy->getTrackAllInfo($orderNumber);
```

### 查询末端派送商

```php
use Laradocs\YunExpress\Client;

// @param string $token Token：加密后的字符串
$factroy = new Client($token);
// @param array $orderNumbers 查询号码：运单号|订单号|跟踪号
$factroy->getCarrier($orderNumbers);
```

### IOSS号备案

```php
use Laradocs\YunExpress\Client;

// @param string $token Token：加密后的字符串
$factroy = new Client($token);
// @param int $iossType Ioss类型：0-个人|1-平台
// @param string|null $platformName 平台名称(类型为 1 时必填)
// @param string $iossNumber 2位字母加10位数字(reg: ^[a-zA-Z]{2}[0- 9]{10}$)
// @param string|null $company IOSS号注册公司名称(非必填)
// @param string|null $country 2位国家简码(非必填)
// @param string|null $street IOSS号街道地址(非必填)
// @param string|null $city IOSS号所在城市(非必填)
// @param string|null $province IOSS号所在省/州(非必填)
// @param string|null $postalCode IOSS号邮编(非必填)
// @param string|null $mobilePhone IOSS号手机号(非必填)
// @param string|null $email IOSS号电子邮箱(非必填)
$factroy->registerIoss($iossType, $platformName, $iossNumber, $company, $country, $street, $city, $province, $postalCode, $mobilePhone, $email);
```
