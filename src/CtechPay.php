<?php

namespace CtechPay;

use CtechPay\Exceptions\CtechPayException;
use CtechPay\Resources\AirtelResource;
use CtechPay\Resources\CardResource;
use CtechPay\Resources\HostedPaymentResource;

class CtechPay
{
    private string $token;
    private string $baseUrl;
    private int $timeout;
    private bool $verifySsl;
    private ?string $caBundle;

    public function __construct(string $token, array $options = [])
    {
        $this->token = $token;
        $this->baseUrl = rtrim($options['base_url'] ?? 'https://new-api.ctechpay.com', '/');
        $this->timeout = (int) ($options['timeout'] ?? 30);
        $this->verifySsl = (bool) ($options['verify_ssl'] ?? true);
        $this->caBundle = $options['ca_bundle'] ?? null;
    }

    public static function client(string $token, array $options = []): self
    {
        return new self($token, $options);
    }

    public function hostedPayments(): HostedPaymentResource
    {
        return new HostedPaymentResource($this);
    }

    public function airtel(): AirtelResource
    {
        return new AirtelResource($this);
    }

    public function cards(): CardResource
    {
        return new CardResource($this);
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, $query);
    }

    public function post(string $path, array $payload = []): array
    {
        return $this->request('POST', $path, $payload);
    }

    public function request(string $method, string $path, array $data = []): array
    {
        $method = strtoupper($method);
        $data = $this->withToken($data);
        $url = $this->baseUrl . '/' . ltrim($path, '/');

        if ($method === 'GET' && $data !== []) {
            $url .= '?' . http_build_query($data);
        }

        $curl = curl_init($url);
        $headers = ['Accept: application/json'];

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
        ]);

        if ($this->caBundle) {
            curl_setopt($curl, CURLOPT_CAINFO, $this->caBundle);
        }

        if ($method !== 'GET') {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
        }

        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($body === false) {
            throw new CtechPayException($error ?: 'CtechPay request failed.');
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new CtechPayException('CtechPay returned an invalid JSON response.', $status, $body);
        }

        if ($status >= 400) {
            $message = $decoded['message'] ?? $decoded['error'] ?? 'CtechPay request failed.';
            throw new CtechPayException($message, $status, $decoded);
        }

        return $decoded;
    }

    private function withToken(array $data): array
    {
        if (!array_key_exists('token', $data)) {
            $data['token'] = $this->token;
        }

        return $data;
    }
}
