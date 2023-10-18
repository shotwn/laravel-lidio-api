<?php

namespace Shotwn\LidioAPI\Exceptions;

/**
 * The currency rate for this payment plan line is not defined. Merchants must define the currency rates for the foreign currency payment plans at Merchant management console.
 */
class LidioCurrencyNotFoundException extends LidioAPIException
{
}
