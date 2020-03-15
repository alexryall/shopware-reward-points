<?php declare(strict_types=1);

namespace AlexRyall\RewardPoints\Core\Checkout;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Rule\LineItemRule;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use AlexRyall\RewardPoints\Service\SpendPoints;

class ApplyDiscount implements CartProcessorInterface
{
    /**
     * @var AbsolutePriceCalculator
     */
    private $calculator;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var SpendPoints
     */
    private $spendPoints;

    public function __construct(
        AbsolutePriceCalculator $calculator,
        SystemConfigService $systemConfigService,
        SpendPoints $spendPoints
    )
    {
        $this->calculator = $calculator;
        $this->systemConfigService = $systemConfigService;
        $this->spendPoints = $spendPoints;
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $products = $this->findProducts($toCalculate);
        $points = $this->spendPoints->getPointsForSession();

        if (!$points) {
            return;
        }

        $discountLineItem = $this->createDiscount('AR_REWARDPOINTS_DISCOUNT');

        $definition = new AbsolutePriceDefinition(
            -($points/$this->systemConfigService->get('RewardPoints.config.spendingRate')),
            $context->getContext()->getCurrencyPrecision(),
            new LineItemRule(LineItemRule::OPERATOR_EQ, $products->getKeys())
        );

        $discountLineItem->setPriceDefinition($definition);

        $discountLineItem->setPrice(
            $this->calculator->calculate($definition->getPrice(), $products->getPrices(), $context)
        );

        $toCalculate->add($discountLineItem);
    }

    private function findProducts(Cart $cart): LineItemCollection
    {
        return $cart->getLineItems()->filter(function (LineItem $item) {
            if ($item->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                return false;
            }

            return $item;
        });
    }

    private function createDiscount(string $name): LineItem
    {
        $discountLineItem = new LineItem($name, 'ar_rewardpoints_discount', null, 1);

        $discountLineItem->setLabel('Reward points discount');
        $discountLineItem->setGood(false);
        $discountLineItem->setStackable(false);
        $discountLineItem->setRemovable(false);

        return $discountLineItem;
    }
}
