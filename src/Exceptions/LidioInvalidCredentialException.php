<?php

namespace Shotwn\LidioAPI\Exceptions;

/**
 * Invalid Authorization or MerchantCode values in header or IP address of the API call may be out of defined IP’s (for production env.) for the merchant.
 */
class LidioInvalidCredentialException extends LidioAPIException
{
}
