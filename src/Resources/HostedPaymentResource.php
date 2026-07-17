<?php

namespace CtechPay\Resources;

use CtechPay\CtechPay;

class HostedPaymentResource
{
    public function __construct(private CtechPay $client)
    {
    }

    public function create(array $payload): array
    {
        return $this->client->post('/api/v1/hosted/payment', $payload);
    }
}
