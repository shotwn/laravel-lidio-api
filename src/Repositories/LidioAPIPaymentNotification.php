<?php

namespace Shotwn\LidioAPI\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Shotwn\LidioAPI\LidioAPI;

/**
 * Class LidioAPIPaymentNotification
 *
 * @property-read string $action Payment, Postauth, Cancel, Refund, PartialPayment, HostedPrePayment
 * @property-read string $paymentResult Success, Refused, Cancelled, UserAuthError, UnexpectedState, NewProcess, WaitingForApproval
 * > NewProcess & WaitingForApproval are not final states so they will set successful() to false.
 * > Links notifications are most likely not using these states. But in case they do, you need to handle them manually.
 * @property-read array $processInfo Contains information about the payment process. See API docs for more info.
 * @property-read array $customerInfo Contains information about the customer. See API docs for more info.
 * @property-read array $basketItems Contains information about the basket items. See API docs for more info.
 * @property-read array $paymentList Contains information about the payment. See API docs for more info.
 * @property-read bool $signaturesVerified True if signatures are verified, set via `verifySignatures()`
 * @property-read bool $requestedAmountVerified True if requested amount is verified, set via `verifyRequestedAmount()`
 * @property-read bool $processedAmountVerified True if processed amount is verified, set via `verifyProcessedAmount()`
 * @property-read bool $_companyInfo Contains information about the company. Used for signature verification.
 * > This is not serialized or exposed from this class. You need to read it from the request.
 *
 */
class LidioAPIPaymentNotification
{
    // https://developer.lidio.com/docs/api-documentation/5c8773cc5ce77-fc-payment-notification
    private string $action;
    private array $_companyInfo; // Never serialize this
    private string $paymentResult;
    private array $processInfo;
    private array $customerInfo;
    private array $basketItems;
    private array $paymentList;

    private Request $_request; // Never serialize this
    private LidioAPI $_lidioAPI; // Never serialize this
    private bool $signaturesVerified = false;
    private bool $requestedAmountVerified = false;
    private bool $processedAmountVerified = false;
    private bool $merchantCustomFieldVerified = false;

    public function __construct(LidioAPI $lidioAPI, Request $request)
    {
        $paymentNotificationBody = $request->all();

        // Will use credentials from LidioAPI to verify signature
        $this->_lidioAPI = $lidioAPI;

        // Needed for signature verification
        // request() Facade could've been used here but I wanted to keep it explicit
        $this->_request = $request;

        // Throw an exception if body doesn't contain all the required fields
        if (
            empty($paymentNotificationBody['action']) ||
            empty($paymentNotificationBody['companyInfo']) ||
            empty($paymentNotificationBody['paymentResult']) ||
            empty($paymentNotificationBody['processInfo']) ||
            empty($paymentNotificationBody['customerInfo']) ||
            empty($paymentNotificationBody['basketItems']) ||
            empty($paymentNotificationBody['paymentList'])
        ) {
            throw new \Exception('LidioAPIPaymentNotification: Missing required fields in body');
        }

        $this->action = $paymentNotificationBody['action'];
        $this->_companyInfo = $paymentNotificationBody['companyInfo'];
        $this->paymentResult = $paymentNotificationBody['paymentResult'];
        $this->processInfo = $paymentNotificationBody['processInfo'];
        $this->customerInfo = $paymentNotificationBody['customerInfo'];
        $this->basketItems = $paymentNotificationBody['basketItems'];
        $this->paymentList = $paymentNotificationBody['paymentList'];
    }

    // Readonly except for constructor
    public function __get($name)
    {
        // Hide the names starting with an underscore
        if (strpos($name, '_') === 0) {
            return null;
        }

        // Direct getters for paymentResult->orderId, paymentResult->merchantProcessId and paymentResult->merchantCustomField
        if ($name === 'orderId') {
            return $this->processInfo['orderId'] ?? null;
        }

        if ($name === 'merchantProcessId') {
            return $this->processInfo['merchantProcessId'] ?? null;
        }

        if ($name === 'merchantCustomField') {
            return $this->processInfo['merchantCustomField'] ?? null;
        }

        // Default getter
        return $this->$name;
    }

    /**
     * Verify signature of the request
     *
     * @return bool True if signature is valid, false otherwise
     */
    public function verifySignatures(): bool
    {
        /*
        header->parametershash = SHA256EncodeAndConverToBase64String(RawRequestBody + ApiPassword)
        verify against this.
        */

        $apiPassword = $this->_lidioAPI->getApiPassword();
        $rawRequestBody = $this->_request->getContent();

        // Request body can come with 2 spaces at the end or other whitespace characters
        // Trim them to make sure the signature is valid
        $rawRequestBody = trim($rawRequestBody);

        if (empty($rawRequestBody)) {
            return false;
        }

        if (empty($apiPassword)) {
            return false;
        }

        $sha256 = hash('sha256', $rawRequestBody . $apiPassword, true);
        $base64 = base64_encode($sha256);

        $headerParametershash = $this->_request->header('parametershash');
        if (empty($headerParametershash)) {
            return false;
        }

        // Check if the signature is valid
        if ($headerParametershash !== $base64) {
            return false;
        }

        /**
         * Check if companyInfo->merchantKet is the same as the one in .env
         */
        $envMerchantKey = $this->_lidioAPI->getMerchantKey();
        $companyInfoMerchantKey = $this->_companyInfo['merchantKey'] ?? null;

        if (empty($envMerchantKey) || empty($companyInfoMerchantKey)) {
            return false;
        }

        // Check if the merchant keys are the same
        if ($envMerchantKey === $companyInfoMerchantKey) {
            $this->signaturesVerified = true;
            return true;
        }

        return false;
    }

    /**
     * Verify if given requested amount is the same as the one in the notification
     */
    public function verifyRequestedAmount(float $requestedAmount): bool
    {
        $requestedAmountFromResponse = $this->processInfo['totalAmountRequested'] ?? null;

        if (empty($requestedAmountFromResponse)) {
            return false;
        }

        // Float comparison. Since API can return a string, we need to convert it to float.
        // And since floats are not precise, we need to compare them with a small delta.
        // Precision of 0.00001 should be enough for our use case.
        if (abs($requestedAmountFromResponse - $requestedAmount) > 0.00001) {
            return false;
        }

        $this->requestedAmountVerified = true;
        return true;
    }

    /**
     * Verify if given processed amount is the same as the one in the notification
     */
    public function verifyProcessedAmount(float $processedAmount): bool
    {
        $processedAmountFromResponse = $this->processInfo['totalAmountProcessed'] ?? null;

        if (empty($processedAmountFromResponse)) {
            return false;
        }

        // Float comparison. See verifyRequestedAmount() for more info.
        if (abs($processedAmountFromResponse - $processedAmount) > 0.00001) {
            return false;
        }

        $this->processedAmountVerified = true;
        return true;
    }

    /**
     * Verify if given merchantCustomField is the same as the one in the notification
     * > This is useful when using merchantCustomField as a unique verification token
     *
     * @param string $MerchantCustomField
     */
    public function verifyMerchantCustomField(string $merchantCustomField): bool
    {
        $merchantCustomFieldFromResponse = $this->merchantCustomField ?? null;

        if (empty($merchantCustomFieldFromResponse)) {
            return false;
        }

        if ($merchantCustomFieldFromResponse !== $merchantCustomField) {
            return false;
        }

        $this->merchantCustomFieldVerified = true;
        return true;
    }

    /**
     * Returns true if payment was successful, false otherwise
     */
    public function successful(): bool
    {
        return $this->paymentResult === 'Success';
    }

    /**
     * Returns false if payment was successful, otherwise returns the paymentResult (error message)
     */
    public function failed(): bool|string
    {
        return $this->successful() ? false :  $this->paymentResult;
    }

    /**
     * Serialize the object to an array
     *
     * @return array{
     * action: string,
     * paymentResult: string,
     * processInfo: array,
     * customerInfo: array,
     * basketItems: array,
     * paymentList: array,
     * success: bool,
     * failed: bool|string,
     * orderId: string|null,
     * merchantProcessId: string|null,
     * merchantCustomField: string|null,
     * signaturesVerified: bool,
     * requestedAmountVerified: bool,
     * processedAmountVerified: bool,
     * merchantCustomFieldVerified: bool,
     * }
     */
    public function toArray(array $skipKeys = []): array
    {
        $toReturn = [
            'action' => $this->action,
            'paymentResult' => $this->paymentResult,
            'processInfo' => $this->processInfo,
            'customerInfo' => $this->customerInfo,
            'basketItems' => $this->basketItems,
            'paymentList' => $this->paymentList,
            'success' => $this->successful(),
            'failed' => $this->failed(),
            'orderId' => $this->orderId,
            'merchantProcessId' => $this->merchantProcessId,
            'merchantCustomField' => $this->merchantCustomField,
            'signaturesVerified' => $this->signaturesVerified,
            'requestedAmountVerified' => $this->requestedAmountVerified,
            'processedAmountVerified' => $this->processedAmountVerified,
            'merchantCustomFieldVerified' => $this->merchantCustomFieldVerified,
        ];

        // Remove the keys that are in $skipKeys
        foreach ($skipKeys as $key) {
            unset($toReturn[$key]);
        }

        return $toReturn;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }
}
