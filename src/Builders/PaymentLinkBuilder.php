<?php

namespace Shotwn\LidioAPI\Builders;

use Shotwn\LidioAPI\LidioAPI;
use Shotwn\LidioAPI\Repositories\LidioAPIPaymentLinkResponse;
use Shotwn\LidioAPI\Traits\LidioConstants;
use Shotwn\LidioAPI\Traits\SetterRouter;
use Shotwn\LidioAPI\Traits\GetterRouter;
use Shotwn\LidioAPI\Exceptions;


/**
 * Payment Link Builder for Lidio API
 *
 * For detailed descriptions of parameters
 * see : https://developer.lidio.com/docs/api-documentation/e366acfe3a84b-create-payment-link
 * @property string $orderId (Optional) Order Id
 * @property string $merchantProcessId (Optional) Merchant Process Id
 * @property string $merchantCustomField (Optional) Merchant Custom Field
 * @property float $totalAmount (Required) Total Amount
 * @property string $currency (Optional) Currency (Default: TRY)
 * @property array $customerInfo (Required) Customer Info
 * @property array $paymentInstruments (Required with condition) Payment Instruments.
 * > It is not recommended to use this setter directly.
 * > `You should use addPaymentMethod() to add payment methods to this array.`
 *
 * @property array $paymentInstrumentInfo (Required with condition) Payment Instrument Info.
 * > It is not recommended to use this setter directly.
 * > `You should use addPaymentMethod() to add payment methods to this array.`
 *
 * @property array $paymentConsents (Optional) Payment Consents
 * @property float $linkExpireHours (Optional) Link Expire Hours
 * > Can be set with expireWithDate() method
 * @property string $sendVia (Optional) Send Via
 * @property bool $doNotDistributeSubsellerPayout (Optional) Do Not Distribute Subseller Payout
 * @property array $basketItems (Optional*) Basket Items.
 * > It is not recommended to use this setter directly.
 * > `You should use addBasketItem() to add basket items to this array.`
 * @property array $invoiceAddress (Optional) Invoice Address
 * @property array $deliveryAddress (Optional) Delivery Address
 * @property array $subscriptionConfig (Optional) Subscription Config
 * @property array $partialPayment (Optional) Partial Payment
 * @property array $customParameters (Optional) Custom Parameters, should be set as an array, will be converted to CSV string
 * @property string $language (Optional) Language
 * @property string $notificationUrl (Optional) Notification Url
 * @property string $alternateNotificationUrl (Optional) Alternate Notification Url
 * @property string $groupCode (Optional) Group Code
 * @property bool $useExternalFraudControl (Optional) Use External Fraud Control
 * @property string $merchantSalesRepresentative (Optional) Merchant Sales Representative
 * @property string $merchantReferralCode (Optional) Merchant Referral Code
 * @property string $clientType (Optional) Client Type
 * @property string $clientIp (Optional) Client Ip
 * @property string $clientUserAgent (Optional) Client User Agent
 * @property string $clientInfo (Optional) Client Info
 *
 */
class PaymentLinkBuilder
{
    use LidioConstants, SetterRouter, GetterRouter;

    private LidioAPI $lidio;
    private string $orderId; // Directly set
    private string $merchantProcessId; // Directly set
    private string $merchantCustomField; // Directly set
    private float $totalAmount;
    private string $currency = "TRY"; // Default currency is TRY
    private array $customerInfo;

    private array $paymentInstruments;
    private array $paymentInstrumentInfo;
    private bool $addPaymentMethodWasUsed = false;
    private bool $paymentInstrumentsWereDirectlySet = false;

    private array $paymentConsents;
    private float $linkExpireHours; // Directly set or use expireWithDate()
    private string $sendVia;
    private bool $doNotDistributeSubsellerPayout; // Directly set

    private array $basketItems;
    private bool $basketItemsWereDirectlySet = false;
    private bool $addBasketItemWasUsed = false;

    private array $invoiceAddress; // Directly set
    private array $deliveryAddress; // Directly set
    private array $subscriptionConfig;
    private array $partialPayment;
    private string $customParameters; // Receive as array, convert to CSV string
    private string $language;
    private string $notificationUrl; // Directly set
    private string $alternateNotificationUrl; // Directly set
    private string $groupCode; // Directly set
    private bool $useExternalFraudControl; // Directly set
    private string $merchantSalesRepresentative; // Directly set
    private string $merchantReferralCode; // Directly set
    private string $clientType; // Directly set
    private string $clientIp; // Directly set
    private string $clientUserAgent; // Directly set
    private string $clientInfo; // Directly set


    private array $allowedKeys = [
        "orderId",
        "merchantProcessId",
        "merchantCustomField",
        "totalAmount",
        "currency",
        "customerInfo",
        "paymentInstruments",
        "paymentInstrumentInfo",
        "paymentConsents",
        "linkExpireHours",
        "sendVia",
        "doNotDistributeSubsellerPayout",
        "basketItems",
        "invoiceAddress",
        "deliveryAddress",
        "subscriptionConfig",
        "partialPayment",
        "customParameters",
        "language",
        "notificationUrl",
        "alternateNotificationUrl",
        "groupCode",
        "useExternalFraudControl",
        "merchantSalesRepresentative",
        "merchantReferralCode",
        "clientType",
        "clientIp",
        "clientUserAgent",
        "clientInfo"
    ];

    protected array $requiredKeys = [
        "customerInfo",
        "paymentInstruments",
        "paymentInstrumentInfo",
        "basketItems"
    ];

    private bool $visibleProperties = true; // Whitelist all properties by default

    function __construct(LidioAPI $lidio)
    {
        $this->lidio = $lidio;
    }

    /**
     * Create and save a random merchantCustomField
     *
     * Useful if you want to use merchantCustomField for verification.
     */
    public function setMerchantCustomFieldAsUUID()
    {
        $this->merchantCustomField = uniqid();

        return $this->merchantCustomField;
    }

    private function totalAmountSetter(float $totalAmount)
    {
        // Number checking and empty checking is handled by type hinting
        // Make sure totalAmount is not negative
        if ($totalAmount < 0) {
            throw new \Exception('Total Amount cannot be negative');
        }

        // Format it to 2 decimals
        $totalAmount = number_format($totalAmount, 2, '.', '');

        $this->totalAmount = $totalAmount;
    }

    private function currencySetter(string $currency)
    {
        // Check if currency is allowed
        if (!in_array($currency, self::$ALLOWED_CURRENCIES)) {
            throw new \Exception('Currency is not allowed');
        }

        $this->currency = $currency;
    }

    private function customerInfoSetter(array $customerInfo)
    {
        // Make sure e-mail is set
        if (empty($customerInfo['email'])) {
            throw new \Exception('Customer Info E-mail is required');
        }

        // Make sure customer ID is set
        if (empty($customerInfo['customerId'])) {
            throw new \Exception('Customer Info Customer Id is required');
        }

        // Defaults were kept empty on purpose since those fields are not required
        // Consider adding them in this classes api docs.

        $this->customerInfo = $customerInfo;
    }

    private function paymentInstrumentsSetter(array $paymentInstruments)
    {
        // Make sure paymentInstruments is not empty
        if (empty($paymentInstruments)) {
            throw new \Exception('Payment Instruments is required');
        }

        // Make sure paymentInstruments is in the allowed list
        foreach ($paymentInstruments as $paymentInstrument) {
            if (!in_array($paymentInstrument, self::$ALLOWED_PAYMENT_INSTRUMENTS)) {
                throw new \Exception("Payment Instrument is not allowed: $paymentInstrument");
            }
        }

        $this->paymentInstruments = $paymentInstruments;
    }

    private function paymentInstrumentInfoSetter(array $paymentInstrumentInfo)
    {
        // Make sure subsetters were not used to prevent overwriting
        if ($this->addPaymentMethodWasUsed) {
            throw new \Exception('Payment Instrument Info is not empty. You cannot use this setter if you had used addPaymentMethod().');
        }

        // Make sure paymentInstrumentInfo is not empty
        if (empty($paymentInstrumentInfo)) {
            throw new \Exception('Payment Instrument Info is required');
        }

        $this->paymentInstrumentInfo = $paymentInstrumentInfo;
        $this->paymentInstrumentsWereDirectlySet = true;
    }


    /**
     * Add a payment method to the paymentInstruments and paymentInstrumentsInfo array
     *
     * @param string $name Payment Instrument name.
     *     Options: `StoredCard`, `NewCard`, `BKMExpress`, `GarantiPay`, `MaximumMobil`, `Emoney`, `WireTransfer`, `DirectWireTransfer`, `InstantLoan`, `MarketplaceBalance`, `Ideal`, `Sofort`, `Sepa`
     * @param array $options
     * @throws \Exception
     */
    public function addPaymentMethod(string $name, array $options = [])
    {
        /**
         * * Note
         *
         * A class based builder was not used for this method because:
         * 1. I don't foresee these needing modification in runtime. So -> arrow accessors are not needed.
         * 2. I don't want to clutter the codebase with a lot of different mapping classes.
         * But it can be done. See BasketItemBuilder for an example.
         */

        // Make sure name is in the allowed list
        if (!in_array($name, self::$ALLOWED_PAYMENT_INSTRUMENTS)) {
            throw new \Exception("Payment Instrument is not allowed: $name");
        }

        // Make sure payment instruments and payment instrument info arrays are not initialized
        if ($this->paymentInstrumentsWereDirectlySet) {
            throw new \Exception('Payment Instruments or Payment Instrument Info is already set. You cannot use this method if you had set PaymentLink->paymentInstruments or PaymentLink->paymentInstrumentInfo.');
        }

        // Initiate payment instruments array if not yet initiated
        if (empty($this->paymentInstruments)) {
            $this->paymentInstruments = [];
        }

        // Make sure payment instrument is not already added
        if (in_array($name, $this->paymentInstruments)) {
            throw new \Exception("Payment Instrument was already added: $name");
        }

        // Push the payment method to the paymentInstruments array
        $this->paymentInstruments[] = $name;

        // If payment instrument is a card. Initiate the card array if not yet initiated.
        if (in_array($name, self::$CARD_PAYMENT_INSTRUMENTS)) {
            if (empty($this->paymentInstrumentInfo['card'])) {
                // Default card options
                $this->paymentInstrumentInfo['card'] = [
                    "processType" => "sales",
                    "useInstallment" => false,
                    "useLoyaltyPoints" => false,
                    "noAmex" => false,
                    "noDebitCard" => false,
                    "noForeignCard" => false,
                    "noCreditCard" => false,
                    "posConfiguration" => [],
                    "maxInstallmentConfig" => []
                ];
            }
        }

        /**
         * Here is options for every payment instrument.
         * This is little bit too cluttered and verbose.
         * But for readibility, I think it's better to keep it this way.
         */

        // If payment instrument is "NewCard". Set the newCard options if not yet set.
        if ($name === "NewCard") {
            if (!empty($this->paymentInstrumentInfo['card']['newCard'])) {
                throw new \Exception('NewCard options are already set');
            }

            // Default newCard options
            $defaultNewCardOptions = [
                "threeDSecureMode" => "Optional3DSelected",
                "useIVRForCardEntry" => false,
                "noCvv" => false,
                "cardSaveOffer" => "PostPayment",
                "cardConsents" => [
                    "cardSaveExtraConsent1" => "None"
                ]
            ];

            // Make sure threeDSecureMode is in the allowed list
            if (!empty($options['threeDSecureMode'])) {
                if (!in_array($options['threeDSecureMode'], self::$THREE_D_SECURE_MODES)) {
                    throw new \Exception("NewCard threeDSecureMode is not allowed: $options[threeDSecureMode]");
                }
            }

            // Make sure cardSaveOffer is in the allowed list
            if (!empty($options['cardSaveOffer'])) {
                if (!in_array($options['cardSaveOffer'], self::$CARD_SAVE_OFFERS)) {
                    throw new \Exception("NewCard cardSaveOffer is not allowed: $options[cardSaveOffer]");
                }
            }

            // Make sure cardSaveExtraConsent1 is in the allowed list
            if (!empty($options['cardConsents']['cardSaveExtraConsent1'])) {
                if (!in_array($options['cardConsents']['cardSaveExtraConsent1'], self::$CARD_SAVE_EXTRA_CONSENTS)) {
                    throw new \Exception("NewCard cardSaveExtraConsent1 is not allowed: $options[cardConsents][cardSaveExtraConsent1]");
                }
            }

            // Merge default options with user options
            $this->paymentInstrumentInfo['card']['newCard'] = array_merge($defaultNewCardOptions, $options);
        }

        // If payment instrument is "StoredCard". Set the storedCard options if not yet set.
        if ($name === "StoredCard") {
            if (!empty($this->paymentInstrumentInfo['card']['storedCard'])) {
                throw new \Exception('StoredCard options are already set');
            }

            // Default storedCard options
            $defaultStoredCardOptions = [
                "threeDSecureMode" => "Optional3DSelected",
                "verificationMethods" => [
                    "OTP",
                    "CVV",
                    "OTPandCVV",
                    "GoogleAuthenticator"
                ],
                "customerIsLoggedIn" => true
            ];

            // Make sure threeDSecureMode is in the allowed list
            if (!empty($options['threeDSecureMode'])) {
                if (!in_array($options['threeDSecureMode'], self::$THREE_D_SECURE_MODES)) {
                    throw new \Exception("StoredCard threeDSecureMode is not allowed: $options[threeDSecureMode]");
                }
            }

            // Make sure verificationMethods is in the allowed list
            if (!empty($options['verificationMethods'])) {
                foreach ($options['verificationMethods'] as $verificationMethod) {
                    if (!in_array($verificationMethod, self::$VERIFICATION_METHODS)) {
                        throw new \Exception("StoredCard verificationMethods is not allowed: $verificationMethod");
                    }
                }
            }

            // Merge default options with user options
            $this->paymentInstrumentInfo['card']['storedCard'] = array_merge($defaultStoredCardOptions, $options);
        }

        // If payment instrument is "BKMExpress". Set the bkmExpress options if not yet set.
        if ($name === "BKMExpress") {
            if (!empty($this->paymentInstrumentInfo['card']['bkmExpress'])) {
                throw new \Exception('BKMExpress options are already set');
            }

            // Default bkmExpress options
            $defaultBKMExpressOptions = [];

            // Merge default options with user options
            $this->paymentInstrumentInfo['card']['bkmExpress'] = array_merge($defaultBKMExpressOptions, $options);
        }

        // If payment instrument is "GarantiPay". Set the garantiPay options if not yet set.
        if ($name === "GarantiPay") {
            if (!empty($this->paymentInstrumentInfo['card']['garantiPay'])) {
                throw new \Exception('GarantiPay options are already set');
            }

            // Default garantiPay options
            $defaultGarantiPayOptions = [];

            // Merge default options with user options
            $this->paymentInstrumentInfo['card']['garantiPay'] = array_merge($defaultGarantiPayOptions, $options);
        }

        // If payment instrument is "MaximumMobil". Set the maximumMobil options if not yet set.
        if ($name === "MaximumMobil") {
            if (!empty($this->paymentInstrumentInfo['card']['maximumMobil'])) {
                throw new \Exception('MaximumMobil options are already set');
            }

            // Default maximumMobil options
            $defaultMaximumMobilOptions = [];

            // Merge default options with user options
            $this->paymentInstrumentInfo['card']['maximumMobil'] = array_merge($defaultMaximumMobilOptions, $options);
        }

        // If payment instrument is "Emoney". Set the emoney options if not yet set.
        if ($name === "Emoney") {
            if (!empty($this->paymentInstrumentInfo['emoney'])) {
                throw new \Exception('Emoney options are already set');
            }

            // Default emoney options
            $defaultEmoneyOptions = [];

            // Merge default options with user options
            $this->paymentInstrumentInfo['emoney'] = array_merge($defaultEmoneyOptions, $options);
        }

        // If payment instrument is "WireTransfer". Set the wireTransfer options if not yet set.
        if ($name === "WireTransfer") {
            if (!empty($this->paymentInstrumentInfo['wireTransfer'])) {
                throw new \Exception('WireTransfer options are already set');
            }

            // Default wireTransfer options
            $defaultWireTransferOptions = [];

            // Merge default options with user options
            $this->paymentInstrumentInfo['wireTransfer'] = array_merge($defaultWireTransferOptions, $options);
        }

        // If payment instrument is "DirectWireTransfer". Set the directWireTransfer options if not yet set.
        if ($name === "DirectWireTransfer") {
            if (!empty($this->paymentInstrumentInfo['directWireTransfer'])) {
                throw new \Exception('DirectWireTransfer options are already set');
            }

            // Default directWireTransfer options
            $defaultDirectWireTransferOptions = [];

            // Merge default options with user options
            $this->paymentInstrumentInfo['directWireTransfer'] = array_merge($defaultDirectWireTransferOptions, $options);
        }

        // If payment instrument is "InstantLoan". Set the instantLoan options if not yet set.
        if ($name === "InstantLoan") {
            if (!empty($this->paymentInstrumentInfo['instantLoan'])) {
                throw new \Exception('InstantLoan options are already set');
            }

            // Default instantLoan options
            $defaultInstantLoanOptions = [
                "campaignCodeList" => [
                    [
                        "bankCode" => "",
                        "campaignCode" => ""
                    ]
                ]
            ];

            // Merge default options with user options
            $this->paymentInstrumentInfo['instantLoan'] = array_merge($defaultInstantLoanOptions, $options);
        }

        // If payment instrument is "Ideal". Set the ideal options if not yet set.
        if ($name === "Ideal") {
            if (!empty($this->paymentInstrumentInfo['ideal'])) {
                throw new \Exception('Ideal options are already set');
            }

            // Make sure language is in the allowed list
            if (!empty($options['language'])) {
                if (!in_array($options['language'], self::$PAYMENT_INSTRUMENT_INFO_ALLOWED_LANGUAGES)) {
                    throw new \Exception("Sofort language is not allowed: $options[language]");
                }
            }

            // Default ideal options
            $defaultIdealOptions = [
                "posId" => 0,
                "bankAccountCountry" => "",
                "address" => "",
                "postalCode" => "",
                "city" => "",
                "countryOfResidence" => "",
                "language" => "TR"
            ];

            // Merge default options with user options
            $this->paymentInstrumentInfo['ideal'] = array_merge($defaultIdealOptions, $options);
        }

        // If payment instrument is "Sofort". Set the sofort options if not yet set.
        if ($name === "Sofort") {
            if (!empty($this->paymentInstrumentInfo['sofort'])) {
                throw new \Exception('Sofort options are already set');
            }

            // Make sure language is in the allowed list
            if (!empty($options['language'])) {
                if (!in_array($options['language'], self::$PAYMENT_INSTRUMENT_INFO_ALLOWED_LANGUAGES)) {
                    throw new \Exception("Sofort language is not allowed: $options[language]");
                }
            }

            // Default sofort options
            $defaultSofortOptions = [
                "posId" => 0,
                "bankAccountCountry" => "",
                "address" => "",
                "postalCode" => "",
                "city" => "",
                "countryOfResidence" => "",
                "language" => "TR"
            ];

            // Merge default options with user options
            $this->paymentInstrumentInfo['sofort'] = array_merge($defaultSofortOptions, $options);
        }

        // If payment instrument is "Sepa". Set the sepa options if not yet set.
        if ($name === "Sepa") {
            if (!empty($this->paymentInstrumentInfo['sepa'])) {
                throw new \Exception('Sepa options are already set');
            }

            // Default sepa options
            $defaultSepaOptions = [
                "accountId" => 0,
                "iban" => "",
                "bic" => "",
                "accountHolder" => "",
                "address" => "",
                "postalCode" => "",
                "city" => "",
                "countryOfResidence" => ""
            ];

            // Merge default options with user options
            $this->paymentInstrumentInfo['sepa'] = array_merge($defaultSepaOptions, $options);
        }

        $this->addPaymentMethodWasUsed = true;
    }

    /**
     * Modify card options after adding a card based payment method using addPaymentMethod()
     * @param array $options
     */
    public function setCardOptions(array $options = [])
    {
        // Make sure payment instrument info array was initiated via addPaymentMethod()
        if (!$this->addPaymentMethodWasUsed) {
            throw new \Exception('Add a card based payment method before modifying card options.');
        }

        // Make sure card field was initiated
        if (empty($this->paymentInstrumentInfo['card'])) {
            throw new \Exception('Card field was not initiated. Add a card based payment method before modifying card options.');
        }

        // Make sure $options doesn't have unrelated keys
        $allowedKeys = [
            "processType",
            "useInstallment",
            "useLoyaltyPoints",
            "noAmex",
            "noDebitCard",
            "noForeignCard",
            "noCreditCard",
            "posConfiguration",
            "maxInstallmentConfig"
        ];

        foreach ($options as $key => $value) {
            if (!in_array($key, $allowedKeys)) {
                throw new \Exception("Card option is not allowed: $key. Individual cards should be added with addPaymentMethod(). For more fine control directly set paymentInstrumentOptions instead of addPaymentMethod().");
            }
        }

        // Merge default options with user options
        $this->paymentInstrumentInfo['card'] = array_merge($this->paymentInstrumentInfo['card'], $options);
    }

    public function expireWithDate(\DateTime $dateTime)
    {
        $now = new \DateTime();

        $diff = $dateTime->diff($now);

        $hours = $diff->h;
        $hours += $diff->days * 24;

        $this->linkExpireHours = $hours;
    }

    private function paymentConsentsSetter(array $paymentConsents)
    {
        // Make sure paymentConsents is not empty
        if (empty($paymentConsents)) {
            throw new \Exception('Payment Consents is required');
        }

        // Rule of thumb for lazy devs: No loops if there are only 2 items and less than 10 lines of code
        // Make sure paymentExtraConsent1 and paymentExtraConsent2 is in the allowed list
        if (!empty($paymentConsents['paymentExtraConsent1'])) {
            if (!in_array($paymentConsents['paymentExtraConsent1'], self::$CARD_SAVE_EXTRA_CONSENTS)) {
                throw new \Exception("Payment Extra Consent 1 is not allowed: $paymentConsents[paymentExtraConsent1]. Allowed values: " . implode(', ', self::$CARD_SAVE_EXTRA_CONSENTS));
            }
        }

        if (!empty($paymentConsents['paymentExtraConsent2'])) {
            if (!in_array($paymentConsents['paymentExtraConsent2'], self::$CARD_SAVE_EXTRA_CONSENTS)) {
                throw new \Exception("Payment Extra Consent 2 is not allowed: $paymentConsents[paymentExtraConsent2] Allowed values: " . implode(', ', self::$CARD_SAVE_EXTRA_CONSENTS));
            }
        }

        $this->paymentConsents = $paymentConsents;
    }

    private function sendViaSetter(string $sendVia)
    {
        // Make sure sendVia is in the allowed list
        if (!in_array($sendVia, self::$SEND_VIA_OPTIONS)) {
            throw new \Exception("Send Via is not allowed: $sendVia. Allowed values: " . implode(', ', self::$SEND_VIA_OPTIONS));
        }

        $this->sendVia = $sendVia;
    }

    private function basketItemsSetter(array $basketItems)
    {
        // Type checking handles array and empty checks
        // Make sure addBasketItem() was not used
        if ($this->addBasketItemWasUsed) {
            throw new \Exception('Basket Items are already set. You cannot use this setter if you had used addBasketItem().');
        }

        $this->basketItems = $basketItems;

        $this->basketItemsWereDirectlySet = true;
    }

    /**
     * Add a basket item to the basketItems array
     * @param array{
     * name: string,
     * category1?: string,
     * category2?: string,
     * category3?: string,
     * quantity: int,
     * unitPrice: float,
     * criticalCategory?: string,
     * isParticipationBankingCompatible?: bool,
     * acquirerCategoryCode?: string,
     * itemIdGivenByMerchant?: string,
     * itemType?: string,
     * marketplace?: array{
     *   subsellerId: int,
     *   itemTotalPrice: float,
     *   subsellerPayoutAmount?: float
     * },
     * extendedItemInfo?: string
     * } $basketItem Basket Item Options -- See Lidio API docs for detailed descriptions
     *
     * - Required fields are: name, quantity, unitPrice
     *
     * - If you want to add a marketplace item, you should set marketplace array.
     * With its required fields: subsellerId, itemTotalPrice
     *
     * @throws \Exception
     */
    public function addBasketItem(array $basketItem)
    {
        // Make sure array is not empty
        if (empty($basketItem)) {
            throw new \Exception('Basket Item is required');
        }

        // Make sure basketItems array is not directly set
        if ($this->basketItemsWereDirectlySet) {
            throw new \Exception('Basket Items are already set. You cannot use this method if you had set PaymentLink->basketItems.');
        }

        // Initiate basketItems array if not yet initiated
        if (empty($this->basketItems)) {
            $this->basketItems = [];
        }

        // Use basket items builder to create a basket item
        $basketItemBuilder = new BasketItemBuilder($basketItem);

        // Serialization will be done later on.
        $this->basketItems[] = $basketItemBuilder;

        $this->addBasketItemWasUsed = true;
    }

    private function subscriptionConfigSetter(array $subscriptionConfig)
    {
        // Make sure array is not empty
        if (empty($subscriptionConfig)) {
            throw new \Exception('Subscription Config is required');
        }

        // Make sure paymentItemSubscriptionType is set correctly if set
        if (!empty($subscriptionConfig['paymentItemSubscriptionType'])) {
            if (!in_array($subscriptionConfig['paymentItemSubscriptionType'], self::$SUBSCRIPTION_CONFIG_PAYMENT_ITEM_SUBSCRIPTION_TYPES)) {
                throw new \Exception("Payment Item Subscription Type is not allowed: $subscriptionConfig[paymentItemSubscriptionType]. Allowed values: " . implode(', ', self::$SUBSCRIPTION_CONFIG_PAYMENT_ITEM_SUBSCRIPTION_TYPES));
            }
        }

        // Make sure trial duration unit is set correctly if set
        if (!empty($subscriptionConfig['trialDurationUnit'])) {
            if (!in_array($subscriptionConfig['trialDurationUnit'], self::$SUBSCRIPTION_CONFIG_TRIAL_DURATION_UNITS)) {
                throw new \Exception("Trial Duration Unit is not allowed: $subscriptionConfig[trialDurationUnit]. Allowed values: " . implode(', ', self::$SUBSCRIPTION_CONFIG_TRIAL_DURATION_UNITS));
            }
        }

        // Make sure period duration unit is set correctly if set
        if (!empty($subscriptionConfig['periodDurationUnit'])) {
            if (!in_array($subscriptionConfig['periodDurationUnit'], self::$SUBSCRIPTION_CONFIG_PERIOD_DURATION_UNITS)) {
                throw new \Exception("Period Duration Unit is not allowed: $subscriptionConfig[periodDurationUnit]. Allowed values: " . implode(', ', self::$SUBSCRIPTION_CONFIG_PERIOD_DURATION_UNITS));
            }
        }

        //TODO: Here requires many more checks. But I don't have time for it. Maybe later.

        $this->subscriptionConfig = $subscriptionConfig;
    }

    private function partialPaymentSetter(array $partialPaymentConfig)
    {
        // Make sure array is not empty
        if (empty($partialPaymentConfig)) {
            throw new \Exception('Partial Payment Config is required');
        }

        // Make sure mode is set correctly if set
        if (!empty($partialPaymentConfig['mode'])) {
            if (!in_array($partialPaymentConfig['mode'], self::$PARTIAL_PAYMENT_MODES)) {
                throw new \Exception("Partial Payment Mode is not allowed: $partialPaymentConfig[mode]. Allowed values: " . implode(', ', self::$PARTIAL_PAYMENT_MODES));
            }
        }

        $this->partialPayment = $partialPaymentConfig;
    }

    private function languageSetter(string $language)
    {
        // Make sure language is in the allowed list
        if (!in_array($language, self::$LANGUAGE_OPTIONS)) {
            throw new \Exception("Language is not allowed: $language. Allowed values: " . implode(', ', self::$LANGUAGE_OPTIONS));
        }

        $this->language = $language;
    }

    /**
     * By api spec customParameters are comma separated key value pairs.
     * Take an array and convert it to comma separated key value pairs.
     */
    private function customParametersSetter(array $customParameters)
    {
        // Make sure array is not empty
        if (empty($customParameters)) {
            throw new \Exception('Custom Parameters array shouldn\'t be empty');
        }

        // Make sure array keys are in $ALLOWED_CUSTOM_PARAMETERS
        // Push it on CSV string if no error.
        $customParametersCSV = "";
        foreach ($customParameters as $key => $value) {
            if (!in_array($key, self::$ALLOWED_CUSTOM_PARAMETERS)) {
                throw new \Exception("Custom Parameter is not allowed: $key. Allowed values: " . implode(', ', self::$ALLOWED_CUSTOM_PARAMETERS));
            }

            $customParametersCSV .= "$key:$value,";
        }

        $this->customParameters = rtrim($customParametersCSV, ',');
    }

    public function toArray()
    {
        $linkBuilderRequestBody = [];
        // Loop allowed keys and set them
        foreach ($this->allowedKeys as $key) {
            // If key is not set, skip it or give error if it's required
            if (!isset($this->$key)) {
                if (in_array($key, $this->requiredKeys)) {
                    // Required but not set
                    throw new \Exception("Required key is not set: $key");
                }
                continue;
            }

            // If key is basketItems, and addBasketItem was used, serialize them
            if ($key === "basketItems" && $this->addBasketItemWasUsed) {
                $linkBuilderRequestBody[$key] = [];
                foreach ($this->basketItems as $basketItem) {
                    $linkBuilderRequestBody[$key][] = $basketItem->toArray();
                }
                continue;
            }

            // If key is found, set and valid, push it to body
            $linkBuilderRequestBody[$key] = $this->$key;
        }

        return $linkBuilderRequestBody;
    }

    /**
     * Request a payment link from Lidio API
     *
     * Builder's required fields should be properly set before calling this method.
     *
     * @return LidioAPIPaymentLinkResponse
     * @throws \Exception
     */
    public function request()
    {
        $requestBody = $this->toArray();

        try {
            $response = $this->lidio->doApiCall('/CreatePaymentLink', $requestBody, 'post');
        } catch (\Exception $e) {
            throw new Exceptions\LidioAPIException('Lidio API CreatePaymentLink Request Error.' . $e->getMessage());
        }

        if ($response['result'] !== 'Success') {
            $exceptionClass = 'Exceptions\\' . $response['result'] . 'Exception';

            // Make sure exception class exists
            if (!class_exists($exceptionClass)) {
                throw new Exceptions\LidioAPIException(
                    'Lidio API CreatePaymentLink Request Error. Unknown error: ' . $response['result'] . ' ' . $response['resultMessage']
                );
            }

            throw new $exceptionClass('Lidio API CreatePaymentLink Request Error. ' . $response['resultMessage']);
        }

        return new LidioAPIPaymentLinkResponse($response, $this);
    }
}
