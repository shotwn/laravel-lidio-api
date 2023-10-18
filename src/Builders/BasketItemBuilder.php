<?php

namespace Shotwn\LidioAPI\Builders;

use Shotwn\LidioAPI\Traits\GetterRouter;
use Shotwn\LidioAPI\Traits\LidioConstants;
use Shotwn\LidioAPI\Traits\SetterRouter;

/**
"basketItems": [
    {
        "name": "string",
        "category1": "string",
        "category2": "string",
        "category3": "string",
        "quantity": 0,
        "unitPrice": 0,
        "criticalCategory": "Gold",
        "isParticipationBankingCompatible": true,
        "acquirerCategoryCode": "string",
        "itemIdGivenByMerchant": "string",
        "itemType": "Virtual",
        "marketplace": {
            "subsellerId": 0,
            "itemTotalPrice": 0,
            "subsellerPayoutAmount": 0
        },
        "extendedItemInfo": "string"
    }
],
 */
class BasketItemMarketplace
{
    use SetterRouter, GetterRouter;

    private int $subsellerId;
    private float $itemTotalPrice;
    private float $subsellerPayoutAmount;
    private BasketItemBuilder $item;

    protected $visibleProperties = [
        "subsellerId",
        "itemTotalPrice",
        "subsellerPayoutAmount",
    ];

    public function __construct(array $options, BasketItemBuilder $item)
    {
        $this->subsellerId = $options['subsellerId'] ?? null;

        if (isset($options['itemTotalPrice'])) {
            $this->itemTotalPrice = $options['itemTotalPrice'];
        }

        if (isset($options['subsellerPayoutAmount'])) {
            $this->subsellerPayoutAmount = $options['subsellerPayoutAmount'];
        }

        // This is mainly used for exception handling
        $this->item = $item;
    }

    public function toArray()
    {
        // SubsellerId is required int
        if (!isset($this->subsellerId)) {
            throw new \Exception("subsellerId is required for marketplace. Item: " . $this->item->name);
        }

        // itemTotalPrice is required float
        if (!isset($this->itemTotalPrice)) {
            throw new \Exception("itemTotalPrice is required for marketplace. Item: " . $this->item->name);
        }

        return [
            "subsellerId" => $this->subsellerId,
            "itemTotalPrice" => $this->itemTotalPrice,
            "subsellerPayoutAmount" => $this->subsellerPayoutAmount,
        ];
    }
}

class BasketItemBuilder
{
    use LidioConstants, SetterRouter, GetterRouter;

    private string $name;
    private string $category1;
    private string $category2;
    private string $category3;
    private int $quantity;
    private float $unitPrice;

    /**
     * This field associates the product with predefined categories those are critical for risk assessment processes of the acquirer or payment system.
     * Required when paymentInsturment includes InstantLoan or DirectWireTransfer.
     */
    private string $criticalCategory;

    /**
     * Set true if the basket item is compatible with Participation (Islamic) Banking criteria, set false if not.
     * You may contact your bank for compatiblity criteria.
     * Value must be set if acquirer bank for InstantLoan or DirectWiretransfer requires it.
     */
    private bool $isParticipationBankingCompatible;
    private string $acquirerCategoryCode;
    private string $itemIdGivenByMerchant;
    private string $itemType;
    private BasketItemMarketplace $marketplace;
    private string $extendedItemInfo;

    protected $visibleProperties = [
        "name",
        "category1",
        "category2",
        "category3",
        "quantity",
        "unitPrice",
        "criticalCategory",
        "isParticipationBankingCompatible",
        "acquirerCategoryCode",
        "itemIdGivenByMerchant",
        "itemType",
        "marketplace",
        "extendedItemInfo",
    ];

    public function __construct(array $options)
    {
        foreach ($options as $key => $value) {
            // Setter router will handle sanity checks and type casting
            $this->$key = $value;
        }
    }

    private function itemTypeSetter(string $value)
    {
        if (!in_array($value, self::$BASKET_ITEM_TYPES)) {
            throw new \Exception("itemType must be one of " . implode(', ', self::$BASKET_ITEM_TYPES));
        }

        $this->itemType = $value;
    }

    private function criticalCategorySetter(string $value)
    {
        if (!in_array($value, self::$CRITICAL_BASKET_ITEM_CATEGORIES)) {
            throw new \Exception("criticalCategory must be one of " . implode(', ', self::$CRITICAL_BASKET_ITEM_CATEGORIES));
        }

        $this->criticalCategory = $value;
    }

    private function marketplaceSetter(array $options)
    {
        $this->marketplace = new BasketItemMarketplace($options, $this);
    }

    /**
     * Serialization method for BasketItemBuilder
     *
     * @return array
     */
    public function toArray()
    {
        $basketItem = [];

        // Add all visible properties if they are set
        foreach ($this->visibleProperties as $property) {
            // Skip marketplace, it is handled separately
            if ($property == 'marketplace') {
                continue;
            }

            if (isset($this->$property)) {
                $basketItem[$property] = $this->$property;
            }
        }

        // Add marketplace if it is set
        if (isset($this->marketplace)) {
            $basketItem['marketplace'] = $this->marketplace->toArray();
        }

        // Make sure name, quantity and unitPrice is set
        // Marketplace checks are done in BasketItemMarketplace::toArray()
        if (!isset($basketItem['name'])) {
            throw new \Exception("name is required for basket item");
        }

        if (!isset($basketItem['quantity'])) {
            throw new \Exception("quantity is required for basket item (name: " . $basketItem['name'] . ")");
        }

        if (!isset($basketItem['unitPrice'])) {
            throw new \Exception("unitPrice is required for basket item (name: " . $basketItem['name'] . ")");
        }

        return $basketItem;
    }
}
