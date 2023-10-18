<?php

namespace Shotwn\LidioAPI;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Shotwn\LidioAPI\Builders\PaymentLinkBuilder;

/**
 * This class is used to make API calls to Lidio
 * It is configured to use .env for configuration
 * So you need to set the following in .env:
 * LIDIO_API_URL=
 * LIDIO_MERCHANT_CODE=
 * LIDIO_AUTHORIZATION_KEY=
 *
 * @method static PaymentLinkBuilder paymentLink()
 * @method public PaymentLinkBuilder paymentLink()
 */
class LidioAPI
{
    private string $apiRoot;
    private string $merchantCode;
    private string $authorizationKey;
    private string $apiPassword;
    private string $merchantKey;

    /**
     * Dynamically create and call a new instance of the class.
     * This matches magic behavior of Laravel models.
     * So instead of
     * $paymentLink = new PaymentLinkBuilder();
     * $paymentLink = $paymentLink->paymentLink();
     * You can do
     * $paymentLink = PaymentLinkBuilder::paymentLink();
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Create a new instance of the class.
     */
    function __construct()
    {
        $config = $this->getEnvConfig();
        $this->apiRoot = $config['apiRoot'];
        $this->merchantCode = $config['merchantCode'];
        $this->authorizationKey = $config['authorizationKey'];
        $this->apiPassword = $config['apiPassword'];
        $this->merchantKey = $config['merchantKey'];
    }

    /**
     * Get the config from .env
     * With checks for required values
     * @return array
     */
    private function getEnvConfig()
    {
        $envConfig = [
            "apiRoot" => env('LIDIO_API_URL'),
            "merchantCode" => env('LIDIO_MERCHANT_CODE'),
            "authorizationKey" => env('LIDIO_AUTHORIZATION_KEY'),
            "apiPassword" => env('LIDIO_API_PASSWORD'),
            "merchantKey" => env('LIDIO_MERCHANT_KEY'),
        ];

        // Make sure apiRoot is set
        if (empty($envConfig['apiRoot'])) {
            throw new \Exception('LIDIO_API_URL is not set in .env');
        }

        // Make sure apiRoot is an URL
        if (!filter_var($envConfig['apiRoot'], FILTER_VALIDATE_URL)) {
            throw new \Exception('LIDIO_API_URL is not a valid URL');
        }

        // Make sure merchantCode and authorizationKey is set
        if (empty($envConfig['merchantCode'])) {
            throw new \Exception('LIDIO_MERCHANT_CODE is not set in .env');
        }

        // Make sure merchantCode and authorizationKey is set
        if (empty($envConfig['authorizationKey'])) {
            throw new \Exception('LIDIO_AUTHORIZATION_KEY is not set in .env');
        }

        // Make sure apiPassword is set
        if (empty($envConfig['apiPassword'])) {
            throw new \Exception('LIDIO_API_PASSWORD is not set in .env');
        }

        // Make sure merchantKey is set
        if (empty($envConfig['merchantKey'])) {
            throw new \Exception('LIDIO_MERCHANT_KEY is not set in .env');
        }

        // Remove trailing slash from apiRoot
        $envConfig['apiRoot'] = rtrim($envConfig['apiRoot'], '/');

        return $envConfig;
    }

    private function getAuthenticationHeaders()
    {
        return [
            "Authorization" => $this->authorizationKey,
            "MerchantCode" => $this->merchantCode,
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];
    }

    /**
     * Used by APIPaymentNotification to verify the request
     */
    public function getApiPassword()
    {
        return $this->apiPassword;
    }

    /**
     * Used by APIPaymentNotification to verify the request
     */
    public function getMerchantKey()
    {
        return $this->merchantKey;
    }

    public function doApiCall($endpoint, $data = [], $method = 'post')
    {
        $response = Http::withHeaders(
            $this->getAuthenticationHeaders()
        )->$method($this->apiRoot . '/' . $endpoint, $data);

        if ($response->failed()) {
            throw new \Exception('Lidio API call failed: ' . $response->body());
        }

        return $response->json();
    }

    // https://developer.lidio.com/docs/api-documentation/e366acfe3a84b-create-payment-link
    private function paymentLink()
    {
        return new PaymentLinkBuilder($this);
    }

    public function handleWebhook(Request $request)
    {
        return new Repositories\LidioAPIPaymentNotification($this, $request);
    }
}
