# WARNING
Do not use unless you review the entire code yourself.

***Lidio cancelled my account without any warning or reason.*** 

So this wrapper never had chance to be used in live service.

Keep this in mind when you use this wrapper or Lidio as whole.

# laravel-lidio-api
Unofficial - **HIGHLY Experimental** Bindings for Lidio's Pay with Link API Interface

API Bindings for Laravel around Lidio's:
- CreatePaymentLink Endpoint
- Payment Notification Webhook Push

Some examples provided in examples folder.

## API Documentation
Always refer to the [official API documentation](https://developer.lidio.com/) for the latest information.

This package is not affiliated with Lidio in any way and does not guarantee that the API will not change.

Also does not guarantee any ***security or stability***.

## Example Usage
This is how you would create a payment link with the API.

Note if you want to use the webhook to receive payment notifications,   
you need to keep these parameter to compare the webhook payload with your order.

- For example for an invoice you can use the invoice ID as orderID and store it in your database.  
Then when you receive the webhook payload, you can compare the orderID from the webhook payload with your invoice ID.

- For the merchantProcessID, you can use a processing number or an UUID/ID describing this specific payment process/link.

- For the merchantCustomField, you can use any verification string you want, but it is recommended to use an UUID/ID.  
See $requestBuilder-\>setMerchantCustomFieldAsUUID()


```php
use shotwn\LidioAPI\LidioAPI;

// See shotwn\lidioAPI\Builders\PaymentLinkBuilder for all available parameters
$requestBuilder = LidioAPI::createPaymentLink()

$requestBuilder->orderID = '1234567890';
$requestBuilder->totalAmount = 1000;
$requestBuilder->customerInfo = [
    'name' => 'John Doe',
    'email' => ''
];

$requestBuilder->addBasketItem([
    'name' => 'Product 1',
    'quantity' => 1,
    'unitPrice' => 1000,
    'type' => 'Virtual'
]);

$requestBuilder->addBasketItem([
    'name' => 'Product 2',
    'quantity' => 1,
    'unitPrice' => 1000,
    'type' => 'Virtual'
]);

$requestBuilder->addPaymentMethod('NewCard');

$requestBuilder->addPaymentMethod('StoredCard', [
    'verificationMethods' => ['GoogleAuthenticator'],
]);

$myRandomStringToSave = $requestBuilder->setMerchantCustomFieldAsUUID();

try {
    $response = $requestBuilder->request();
} catch (\Exception $e) {
    // Handle Exception
    // See shotwn\lidioAPI\Exceptions for all available exceptions
}

$paymentLink = $response->linkURL;
```

## Webhook Usage
A handy middleware was provided to handle ingest of the webhook payload.

```php
namespace App\Http\Controllers;

use App\Models\Invoice;
use Shotwn\LidioAPI\Middleware\LidioWebhookMiddleware;

// You might need to disable CSRF protection for this route, if you are using it.
class LidioWebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware(LidioWebhookMiddleware::class);
    }

    public function post(Request $request)
    {
        // You can access the payment notification object like this thanks to the middleware
        $paymentNotification = $request->attributes->get('paymentNotification');

        // See shotwn\lidioAPI\Builders\PaymentNotificationBuilder for all available parameters
        $hashVerified = $paymentNotification->verifyHash();

        $merchantCustomFieldVerified = $paymentNotification->verifyMerchantCustomField($myRandomStringFromLinkCreation);

        if ($hashVerified && $merchantCustomFieldVerified) {
            // Do good stuff here, like update your invoice status
            $orderID = $paymentNotification->orderID;
            $merchantProcessID = $paymentNotification->merchantProcessId;
        }
    }
}
```

# Library API Documentation
```markdown
⚠️ WARNING: This is an AI generated documentation, 
   it might not be accurate and it is certainly not complete.

   Please refer to the source code for accurate documentation.

   Do not use this documentation as a reference for official API.

   This library is not affiliated with Lidio in any way.
```

## Required .env Fields
- `LIDIO_API_URL` - Lidio API Root URL, for initial tests use "https://test.lidio.com/api"
- `LIDIO_AUTHORIZATION_KEY` - Receive as a customer
- `LIDIO_MERCHANT_CODE` - Receive as a customer
- `LIDIO_API_PASSWORD` - Receive as a customer
- `LIDIO_MERCHANT_KEY` - Receive as a customer

## Class: PaymentLinkBuilder

This class provides a fluent interface for building payment links using the Lidio API. It allows you to set various parameters for the payment link, such as the order ID, the payment amount, and the customer's email and phone number.

For initiating it you can use the `LidioAPI::createPaymentLink()` method.

### Properties
- `orderId` (Optional): Order Id
- `merchantProcessId` (Optional): Merchant Process Id
- `merchantCustomField` (Optional): Merchant Custom Field
- `totalAmount` (Required): Total Amount
- `currency` (Optional): Currency (Default: TRY)
- `customerInfo` (Required): Customer Info
- `paymentInstruments` (Required with condition): Payment Instruments.
  - It is not recommended to use this setter directly.
  - `You should use addPaymentMethod() to add payment methods to this array.`
- `paymentInstrumentInfo` (Required with condition): Payment Instrument Info.
  - It is not recommended to use this setter directly.
  - `You should use addPaymentMethod() to add payment methods to this array.`
- `paymentConsents` (Optional): Payment Consents
- `linkExpireHours` (Optional): Link Expire Hours
  - Can be set with expireWithDate() method
- `sendVia` (Optional): Send Via
- `doNotDistributeSubsellerPayout` (Optional): Do Not Distribute Subseller Payout
- `basketItems` (Optional*): Basket Items.
  - It is not recommended to use this setter directly.
  - `You should use addBasketItem() to add basket items to this array.`
- `invoiceAddress` (Optional): Invoice Address
- `deliveryAddress` (Optional): Delivery Address
- `subscriptionConfig` (Optional): Subscription Config
- `partialPayment` (Optional): Partial Payment
- `customParameters` (Optional): Custom Parameters, should be set as an array, will be converted to CSV string
- `language` (Optional): Language
- `notificationUrl` (Optional): Notification Url
- `alternateNotificationUrl` (Optional): Alternate Notification Url
- `groupCode` (Optional): Group Code
- `useExternalFraudControl` (Optional): Use External Fraud Control
- `merchantSalesRepresentative` (Optional): Merchant Sales Representative
- `merchantReferralCode` (Optional): Merchant Referral Code
- `clientType` (Optional): Client Type
- `clientIp` (Optional): Client Ip
- `clientUserAgent` (Optional): Client User Agent
- `clientInfo` (Optional): Client Info

### Methods
- `__construct(LidioAPIClient $apiClient)`: Constructs a new `PaymentLinkBuilder` object with the given `LidioAPIClient`.
- `setMerchantCustomFieldAsUUID(): string`: Sets the merchant custom field as a UUID and returns the UUID.
- `addPaymentMethod(string $paymentMethod, array $paymentMethodInfo = []): self`: Adds a payment method to the payment link. See API documentation for available payment methods and their required parameters.
- `addBasketItem(array $basketItem): self`: Adds a basket item to the payment link. See API documentation for available basket item parameters.
- `expireWithDate(DateTime $dateTime): self`: Sets the link expiration date to the given `DateTime` object.
- `request(): LidioAPIPaymentLinkResponse`: Sends the payment link request to the Lidio API and returns a `LidioAPIPaymentLinkResponse` object.
- `toArray(): array`: Returns an array representation of the payment link request.

### Private Properties

- `uuidGenerator` (UuidFactory): A `UuidFactory` object used to generate UUIDs for the `setMerchantCustomFieldAsUUID` method.

### Private Methods

- `generateUUID(): string`: Generates a new UUID for use as a custom field on the payment link.

Let me know if you have any further questions!

## Repositories
Library has 2 repository classes to map the API responses to objects.


### Class: LidioAPIPaymentLinkResponse

This class represents a response from the Lidio API for a payment link request. It contains information about the result of the request, the order ID, the system transaction ID, the payment link URL, and the email and phone number associated with the payment link.

#### Properties

- `$result` (string): The result of the payment link request. Possible values are `'Success'` or `'Failure'`.
- `$resultMessage` (string|null): An optional message associated with the result of the payment link request.
- `$orderId` (string): The order ID associated with the payment link.
- `$systemTransId` (string|null): An optional system transaction ID associated with the payment link.
- `$linkURL` (string): The URL of the payment link.
- `$email` (string): The email address associated with the payment link.
- `$phone` (string): The phone number associated with the payment link.
- `$requestBuildedBy` (PaymentLinkBuilder): The `PaymentLinkBuilder` object that was used to make the API call that generated this response.

#### Methods

- `__construct(array $apiResult, PaymentLinkBuilder $requestBuildedBy = null)`: Constructs a new `LidioAPIPaymentLinkResponse` object from an array of API results and an optional `PaymentLinkBuilder` object.
- `success(): bool`: Returns `true` if the payment link request was successful, `false` otherwise.
- `failed(): bool`: Returns `true` if the payment link request failed, `false` otherwise.
- `getRequestBuildedBy(): PaymentLinkBuilder`: Returns the `PaymentLinkBuilder` object that was used to make the API call that generated this response.
- `toArray($withRequestBuilder): array`: Returns an array representation of the payment link response.

#### Throws
see shotwn\lidioAPI\Exceptions

### Class: LidioAPIPaymentNotification

This class represents a payment notification from the Lidio API. It contains information about the payment, including the payment ID, the payment status, and the payment amount.

#### Properties
- `$action` (string): The action associated with the payment notification.
- `$paymentResult` (string): The result of the payment notification.
- `$processInfo` (array): The process information associated with the payment notification.
- `$customerInfo` (array): The customer information associated with the payment notification.
- `$basketItems` (array): The basket items associated with the payment notification.
- `$paymentList` (array): The payment list associated with the payment notification.
- `$signaturesVerified` (bool): Whether or not the signatures of the payment notification have been verified.
- `$requestedAmountVerified` (bool): Whether or not the requested amount of the payment notification has been verified.
- `$processedAmountVerified` (bool): Whether or not the processed amount of the payment notification has been verified.
- `$merchantCustomFieldVerified` (bool): Whether or not the merchant custom field of the payment notification has been verified.

#### Methods

- `__construct(array $data)`: Constructs a new `LidioAPIPaymentNotification` object from an array of payment notification data.
- `success(): bool`: Returns `true` if the payment notification indicates a successful payment, `false` otherwise.
- `failed(): string`: Returns payment result if the payment notification indicates a failed payment, `false` otherwise.
- `verifyHash(): bool`: Verifies the hash of the payment notification.
- `verifyMerchantCustomField(string $merchantCustomField): bool`: Verifies the merchant custom field of the payment notification.
- `verifyRequestedAmount(float $requestedAmount): bool`: Verifies the requested amount of the payment notification.
- `verifyProcessedAmount(float $processedAmount): bool`: Verifies the processed amount of the payment notification.
- `toArray(): array`: Returns an array representation of the payment notification.
