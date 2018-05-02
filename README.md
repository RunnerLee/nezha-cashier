# nazha-cashier

### 思路

如何对接第三方支付? 最直接的方式, 直接用第三方 SDK, 或是自己对接第三方 API 实现. 

其中无论是使用支付渠道提供的 SDK, 还是使用开源的SDK, 基本都需要先了解每个支付渠道的接口调用参数, 通知参数. 虽然在调用方式上能做到 "优雅", 但是在拼装参数和接收通知上, 依旧是不尽人意.

那能否把拼装参数这个最后的问题解决掉呢?

首先我们想下抽象一下支付流程:

![](/charge.png)

在完整的支付流程中, 有两种类型的动作:
- 请求. 包括拼装支付请求参数, 调用支付渠道下单, 查询支付结果, 查询付款结果等
- 通知. 包括支付结果通知, 支付关闭通知, 退款进度通知等

基本每个支付渠道的流程都是这样的, 难点在于构建请求的参数和处理响应以及通知的内容.

那么将请求, 请求的响应, 以及通知都抽象为表单, 则分别对应为:

- 请求表单. `request form`
- 响应表单. `response form`
- 通知表单. `notification form`

以请求支付为例, 用同一个订单记录, 分别调用支付宝跟微信的请求支付参数:

*订单*
```php
<?php
$order = [
    'id' => '123456789',
    'subject' => 'testing',
    'description' => 'testing description',
    'return_url' => 'https://charge.return.url',
    'amount' => '0.01',
    'currency' => 'CNY',
];
```

*微信扫码支付*
```php
<?php
$parameters = [
    'body' => $order['subject'],
    'out_trade_no' => $order['id'],
    'fee_type' => $order['currency'],
    'total_fee' => intval($order['amount'] * 100),
    'trade_type' => 'NATIVE',
    'notify_url' => 'https://charge.notify.url',
    'detail' => $order['subject'],
    'appid' => 'xxx',
    'mch_id' => 'xxx',
    'nonce_str' => uniqid(),
    'sign_type' => 'MD5',
    'sign' => 'xxx',
];

$response = request('POST', 'https://api.mch.weixin.qq.com/pay/unifiedorder', generateXml($parameters));

$res = parseXml($response);

$chargeUrl = $res['code_url'];
```

*支付宝PC网站支付*
```php
<?php
$parameters = [
    'app_id' => 'xxxx',
    'method' => 'alipay.trade.page.pay',
    'format' => 'JSON',
    'return_url' => 'https://charge.return.url',
    'charset' => 'utf8',
    'sign_type' => 'RSA2',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0',
    'notify_url' => 'https://charge.notify.url',
    'biz_content' => json_encode([
        'out_trade_no' => $order['id'],
        'total_amount' => $order['amount'],
        'subject' => $order['subject'],
        'body' => $order['description'],
        'product_code' => 'FAST_INSTANT_TRADE_PAY',    
    ]),
];
```

到这里, 在获取到 `$order` 的情况下, 可以想象一下最简单的调用支付的方式:
```php
<?php
charge($order, 'alipay_web');   // 调用支付宝PC网站支付
charge($order, 'wechat_qr'); // 调用微信扫码支付
```

那么在这个扩展包中, 提供的支付调用方式也是如此简单:
```php
<?php
use Runner\NezhaCashier\Cashier;

$config = [
    'app_id' => 'xxxx',
    'private_key' => '',
    'public_key' => '',
];

$cashier = new Cashier('alipay_web', $config);

$response = $cashier->charge([
    'order_id' => '151627101400000071',
    'subject' => 'testing',
    'amount' => '0.01',
    'currency' => 'CNY',
    'description' => 'testing description',
    'return_url' => 'https://www.baidu.com',
    'notify_url' => 'https://www.baidu.com',
    'expired_at' => '2018-01-23 19:00:00',
]);


echo $response->get('charge_url');
```

我们定义好了每个请求动作使用的表单及其字段, 请求响应的表单及其字段, 以及每个通知的表单及其字段. 将表单传递给网关, 由网关完成拼装参数, 解析响应.

在使用过程中, 只需要修改调用的网关即可, 例如上述案例中, 只需要将 `alipay_web` 修改为 `alipay_wap` 即可完成接入支付宝手机网站支付.


### Usage
```php
<?php

use Runner\NezhaCashier\Cashier;

$config = [
    'app_id' => 'xxxx',
    'private_key' => '',
    'public_key' => '',
];

$cashier = new Cashier('alipay_web', $config);

// 请求支付
$chargeResponseForm = $cashier->charge([
    'order_id' => '151627101400000071',
    'subject' => 'testing',
    'amount' => '0.01',
    'currency' => 'CNY',
    'description' => 'testing description',
    'return_url' => 'https://www.baidu.com',
    'notify_url' => 'https://www.baidu.com',
    'expired_at' => '2018-01-23 19:00:00',
]);
echo $response->get('charge_url');

// 查询支付
$queryResponseForm = $cashier->query([
    'order_id' => '151627101400000071',
]);

// 获取支付通知
$chargeNotifyForm = $cashier->notify('charge');

// 返回成功
echo $cashier->success();

// 返回失败
echo $cashier->fail();
```

### 表单及字段说明

#### ChargeRequestForm
| 字段名 | 是否必须 | 字段说明 | 备注 |
| --- | --- | --- | --- |
| order_id | 是 | 订单号 |  |
| subject | 是 | 订单标题 |  |
| amount | 是 | 订单金额 | 注意部分支付渠道有金额上线限制 |
| currency | 是 | 订单货币 | 注意支付渠道支付 |
| description | 是 | 订单简述 | 支付渠道会有不同的长度限制 |
| user_ip | 否 | 用户IP |  |
| return_url | 否 | 回调地址 | web类型的支付渠道必须填 |
| show_url | 否 | 展示地址 |  |
| body | 否 | 订单详细说明 | 这个参数我应该删掉 |
| expired_at | 否 | 过期时间 | unix 时间戳 |
| created_at | 否 | 创建时间 | unix 时间戳, 想不到吧, 连这个鬼都要?? |