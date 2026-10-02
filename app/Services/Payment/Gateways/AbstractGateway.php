<?php

namespace App\Services\Payment\Gateways;

use App\Services\Payment\Contracts\PaymentGateway;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class AbstractGateway implements PaymentGateway
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('gateways.drivers.'.$this->name(), []);
    }

    protected function option(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    protected function isSandbox(): bool
    {
        return (bool) $this->option('sandbox', true);
    }

    public function isConfigured(): bool
    {
        if (! $this->option('enabled', true)) {
            return false;
        }

        foreach ($this->requiredCredentials() as $key) {
            if (blank($this->option($key))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Config keys that must be non-empty before this gateway can be used.
     *
     * @return list<string>
     */
    abstract protected function requiredCredentials(): array;

    /**
     * Shared HTTP client. Retries only connection-level failures — a gateway
     * that answered with a business error must never be replayed.
     */
    protected function http(): PendingRequest
    {
        return Http::timeout((int) config('gateways.timeout', 20))
            ->connectTimeout(10)
            ->retry(
                (int) config('gateways.retries', 2),
                250,
                fn ($exception) => $exception instanceof ConnectionException,
                throw: false,
            )
            ->acceptJson();
    }

    protected function log(string $event, array $context = []): void
    {
        Log::channel(config('logging.default'))->info("payment.{$this->name()}.{$event}", $context);
    }

    protected function logError(string $event, array $context = []): void
    {
        Log::error("payment.{$this->name()}.{$event}", $context);
    }

    /**
     * Strip credentials before anything is written to logs or gateway_data.
     */
    protected function scrub(?array $data): array
    {
        if (! $data) {
            return [];
        }

        foreach (['merchant_id', 'api_key', 'client_secret', 'password',
            'access_token', 'refresh_token', 'Authorization'] as $secret) {
            if (array_key_exists($secret, $data)) {
                $data[$secret] = '***';
            }
        }

        return $data;
    }
}
