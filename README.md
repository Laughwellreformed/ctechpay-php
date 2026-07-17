# CtechPay PHP SDK

Official PHP SDK for CtechPay payments.

Use this package from your server. Your CtechPay service token must never be exposed in browser JavaScript or mobile apps.

## Install

```bash
composer require ctechpay/ctechpay-php
```

For local development before publishing:

```bash
composer config repositories.ctechpay path ./sdks/php
composer require ctechpay/ctechpay-php:@dev
```

## Hosted Payment Page

```php
use CtechPay\CtechPay;

$ctechpay = CtechPay::client('YOUR_SERVICE_TOKEN');

$payment = $ctechpay->hostedPayments()->create([
    'amount' => 100,
    'customer_reference' => 'INV-1001',
    'customer_message' => 'Invoice payment',
    'customer_name' => 'Jane Doe',
    'customer_email' => 'jane@example.com',
    'redirectUrl' => 'https://example.com/payments/success',
    'cancelUrl' => 'https://example.com/payments/cancelled',
]);

header('Location: ' . $payment['data']['hosted_payment_url']);
exit;
```

When a successful hosted payment redirects back, CtechPay appends `reference` to your `redirectUrl`. Use that value to check status:

```php
$reference = $_GET['reference'];
$status = $ctechpay->cards()->status($reference);
```

For Airtel Money hosted payments, use:

```php
$status = $ctechpay->airtel()->details($_GET['reference']);
```

## Airtel Money Direct API

```php
$payment = $ctechpay->airtel()->pay([
    'amount' => 100,
    'phone' => '0999123456',
    'customer_reference' => 'INV-1001',
]);

$transactionId = $payment['data']['transaction']['id'];
$status = $ctechpay->airtel()->status($transactionId);
```

## Card Hosted Bank Page

```php
$order = $ctechpay->cards()->createPaymentPage([
    'amount' => 100,
    'merchantAttributes' => true,
    'redirectUrl' => 'https://example.com/payments/success',
    'cancelUrl' => 'https://example.com/payments/cancelled',
]);

header('Location: ' . $order['payment_page_URL']);
exit;
```

This SDK intentionally does not expose direct card PAN/CVV helpers. Use the CtechPay Hosted Payment Page for card collection unless your integration is formally approved for card-data handling.
