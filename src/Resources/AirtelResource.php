<?php

namespace CtechPay\Resources;

use CtechPay\CtechPay;

class AirtelResource
{
    public function __construct(private CtechPay $client)
    {
    }

    public function pay(array $payload): array
    {
        return $this->client->post('/api/v1/airtel/payment', $payload);
    }

    public function status(string $transactionId): array
    {
        return $this->client->post('/api/v1/airtel/status', ['trans_id' => $transactionId]);
    }

    public function details(string $transactionId): array
    {
        return $this->client->post('/api/v1/airtel/transaction/details', ['transaction_id' => $transactionId]);
    }

    public function reference(string $airtelMoneyId): array
    {
        return $this->client->post('/api/v1/airtel/transaction/reference', ['transaction_id' => $airtelMoneyId]);
    }
}
