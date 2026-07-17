# CtechPay PHP SDK

Official PHP SDK for integrating CtechPay payments in PHP and Laravel applications.

Use this package from your server. Your CtechPay service token must never be exposed in browser JavaScript, mobile apps, or public repositories.

## Installation

```bash
composer require ctechpay/ctechpay-php
```

## Basic Usage

```php
use CtechPay\CtechPay;

$ctechpay = CtechPay::client('YOUR_SERVICE_TOKEN');
```

You can also configure the API base URL and request timeout:

```php
$ctechpay = CtechPay::client('YOUR_SERVICE_TOKEN', [
    'base_url' => 'https://new-api.ctechpay.com',
    'timeout' => 30,
]);
```

## Hosted Payment Page

Hosted checkout is the recommended integration for most merchants. CtechPay gives you a secure payment page where the customer can choose Airtel Money or card.

```php
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

### Hosted Redirect Reference

When a hosted payment completes successfully, CtechPay redirects the customer to your `redirectUrl` with a `reference` query parameter.

```text
https://example.com/payments/success?reference=TRANSACTION_OR_ORDER_REFERENCE
```

Use the `reference` to check the final payment status:

```php
$reference = $_GET['reference'];
```

For card payments, the reference is the card order reference:

```php
$status = $ctechpay->cards()->status($reference);
```

For Airtel Money payments, the reference is the Airtel transaction ID:

```php
$details = $ctechpay->airtel()->details($reference);
```

## Airtel Money

### Initiate Payment

```php
$payment = $ctechpay->airtel()->pay([
    'amount' => 100,
    'phone' => '0999123456',
    'customer_reference' => 'INV-1001',
    'customer_message' => 'Invoice payment',
]);

$transactionId = $payment['data']['transaction']['id'];
```

### Check Airtel Status

Use the transaction ID returned when initiating payment.

```php
$status = $ctechpay->airtel()->status($transactionId);
```

### Get Airtel Transaction Details

```php
$details = $ctechpay->airtel()->details($transactionId);
```

### Find CtechPay Transaction By Airtel Money ID

```php
$reference = $ctechpay->airtel()->reference('AIRTEL_MONEY_ID');
```

## Card Hosted Bank Page

This creates the Standard Bank hosted card checkout page.

```php
$order = $ctechpay->cards()->createPaymentPage([
    'amount' => 100,
    'merchantAttributes' => true,
    'redirectUrl' => 'https://example.com/payments/success',
    'cancelUrl' => 'https://example.com/payments/cancelled',
    'customer_reference' => 'INV-1001',
    'customer_message' => 'Invoice payment',
]);

header('Location: ' . $order['payment_page_URL']);
exit;
```

### Check Card Order Status

```php
$status = $ctechpay->cards()->status($order['order_reference']);
```

## Laravel Example

```php
use CtechPay\CtechPay;

$ctechpay = CtechPay::client(config('services.ctechpay.token'));

$payment = $ctechpay->hostedPayments()->create([
    'amount' => 100,
    'customer_reference' => 'ORDER-1001',
    'redirectUrl' => route('payments.success'),
    'cancelUrl' => route('payments.cancelled'),
]);

return redirect($payment['data']['hosted_payment_url']);
```

In `config/services.php`:

```php
'ctechpay' => [
    'token' => env('CTECHPAY_TOKEN'),
],
```

## Error Handling

The SDK throws `CtechPay\Exceptions\CtechPayException` for failed requests.

```php
use CtechPay\Exceptions\CtechPayException;

try {
    $payment = $ctechpay->hostedPayments()->create([
        'amount' => 100,
    ]);
} catch (CtechPayException $e) {
    echo $e->getMessage();
    print_r($e->response);
}
```

## Security Notes

This SDK intentionally does not expose direct card PAN/CVV helpers. Use the CtechPay Hosted Payment Page for card collection unless your integration is formally approved for card-data handling.

Do not disable SSL verification in production.
