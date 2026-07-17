<?php

namespace CtechPay\Resources;

use CtechPay\CtechPay;

class CardResource
{
    public function __construct(private CtechPay $client)
    {
    }

    public function createPaymentPage(array $payload): array
    {
        return $this->client->post('/api/v1/orders', $payload);
    }

    public function status(string $orderReference): array
    {
        return $this->client->get('/api/v1/orders/status', ['orderRef' => $orderReference]);
    }
}
