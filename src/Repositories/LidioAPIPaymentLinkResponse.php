<?php

namespace Shotwn\LidioAPI\Repositories;

use Shotwn\LidioAPI\Builders\PaymentLinkBuilder;

class LidioAPIPaymentLinkResponse
{
    public string $result;
    public string|null $resultMessage;
    public string $orderId;
    public string|null $systemTransId;
    public string $linkURL;
    public string $email;
    public string $phone;
    private PaymentLinkBuilder $requestBuildedBy;

    public function __construct(array $apiResult, PaymentLinkBuilder $requestBuildedBy = null)
    {
        $this->result = $apiResult['result'];
        $this->resultMessage = $apiResult['resultMessage'];
        $this->orderId = $apiResult['orderId'];
        $this->systemTransId = $apiResult['systemTransId'];
        $this->linkURL = $apiResult['linkURL'];
        $this->email = $apiResult['email'];
        $this->phone = $apiResult['phone'];

        if ($requestBuildedBy) {
            $this->requestBuildedBy = $requestBuildedBy;
        }
    }

    /**
     * Check if the API call was successful
     * It is not necessary to check this
     * if PaymentLinkBuilder was being used, since it throws an exception on failure
     */
    public function success(): bool
    {
        return $this->result === 'Success';
    }

    /**
     * Check if the API call failed
     * It is not necessary to check this
     * if PaymentLinkBuilder was being used since it throws an exception on failure
     */
    public function failed(): bool
    {
        return $this->success() ? false : $this->result;
    }

    /**
     * Get the PaymentLinkBuilder that was used to make the API call
     */
    public function getRequestBuilder(): PaymentLinkBuilder
    {
        return $this->requestBuildedBy;
    }

    public function toArray($withRequestParameters = false): array
    {
        $asArray = [
            'result' => $this->result,
            'resultMessage' => $this->resultMessage,
            'orderId' => $this->orderId,
            'systemTransId' => $this->systemTransId,
            'linkURL' => $this->linkURL,
            'email' => $this->email,
            'phone' => $this->phone,
        ];

        if ($withRequestParameters) {
            $asArray['requestParameters'] = $this->requestBuildedBy->toArray();
        }

        return $asArray;
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
