<?php

namespace Shotwn\LidioAPI\Traits;

trait LidioConstants
{
    static $ALLOWED_CURRENCIES = [
        "DKK",
        "JPY",
        "NOK",
        "RUB",
        "SEK",
        "CHF",
        "AED",
        "GBP",
        "USD",
        "TRY",
        "EUR"
    ];

    static $ALLOWED_PAYMENT_INSTRUMENTS = [
        "StoredCard",
        "NewCard",
        "BKMExpress",
        "GarantiPay",
        "MaximumMobil",
        "Emoney",
        "WireTransfer",
        "DirectWireTransfer",
        "InstantLoan",
        "MarketplaceBalance",
        "Ideal",
        "Sofort",
        "Sepa",
    ];

    static $CARD_PAYMENT_INSTRUMENTS = [
        "StoredCard",
        "NewCard",
        "BKMExpress",
        "GarantiPay",
        "MaximumMobil",
    ];

    static $THREE_D_SECURE_MODES = [
        "None",
        "Mandatory",
        "Optional",
        "Optional3DSelected"
    ];

    static $CARD_SAVE_OFFERS = [
        "PostPayment",
        "PrepaymentMandatory",
        "PrepaymentOptional"
    ];

    static $CARD_SAVE_EXTRA_CONSENTS = [
        "None",
        "Mandatory",
        "Optional"
    ];

    static $VERIFICATION_METHODS = [
        "OTP",
        "CVV",
        "OTPandCVV",
        "GoogleAuthenticator"
    ];

    static $PAYMENT_INSTRUMENT_INFO_ALLOWED_LANGUAGES = [
        "EN",
        "TR"
    ];

    static $SEND_VIA_OPTIONS = [
        "None",
        "Email",
        "SMS"
    ];

    static $CRITICAL_BASKET_ITEM_CATEGORIES = [
        "Gold",
        "MobilePhone",
        "Tablet",
        "Computer",
        "CarLoan",
        "Other",
        "DiyStore",
        "DigitalItem",
        "SuperMarket",
        "WhiteWare",
        "WearableTech",
        "SmallWhiteWare",
        "TV",
        "GameConsole",
        "AirConditionerHeater",
        "Electronic",
        "Accessory",
        "MotherBabyChild",
        "Shoes",
        "Clothes",
        "Cosmetics",
        "Furniture",
        "HomeLife",
        "Car"
    ];

    static $BASKET_ITEM_TYPES = [
        "Virtual",
        "Physical"
    ];

    static $SUBSCRIPTION_CONFIG_PAYMENT_ITEM_SUBSCRIPTION_TYPES = [
        "None",
        "TermBasedService",
        "InstallmentSales",
        "Continuous"
    ];

    static $SUBSCRIPTION_CONFIG_TRIAL_DURATION_UNITS = [
        "None",
        "Day",
        "Week",
        "Month",
        "Year"
    ];

    static $SUBSCRIPTION_CONFIG_PERIOD_DURATION_UNITS = [
        "None",
        "Week",
        "Month",
        "Year"
    ];

    static $PARTIAL_PAYMENT_MODES = [
        "None",
        "Default",
        "Optional"
    ];

    //  Tr,tr,Tur,tur,En,en,Eng,eng,Fra,fra,Deu,deu,Ita,ita
    static $LANGUAGE_OPTIONS = [
        "Tr",
        "Tur",
        "En",
        "en",
        "Eng",
        "eng",
        "Fra",
        "fra",
        "Deu",
        "deu",
        "Ita",
        "ita"
    ];

    static $ALLOWED_CUSTOM_PARAMETERS = [
        "Lang",
        "MOTO",
        "MaskedCardNum",
        "SelectedInstallmentCount",
        "BankReferenceNo",
        "FirstSixLastFourTCKNMode"
    ];
};
