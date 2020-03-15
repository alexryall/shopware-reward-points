<?php declare(strict_types=1);

namespace AlexRyall\RewardPoints\Subscriber;

use Shopware\Core\Framework\Struct\ArrayEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use AlexRyall\RewardPoints\Service\SpendPoints;

class CheckoutConfirmPageLoaded implements EventSubscriberInterface
{
    /**
     * @var SpendPoints
     */
    private $spendPoints;

    public function __construct(SpendPoints $spendPoints)
    {
        $this->spendPoints = $spendPoints;
    }

    public static function getSubscribedEvents(): array
    {
        return[
            CheckoutConfirmPageLoadedEvent::class => 'onCheckoutConfirmPageLoaded'
        ];
    }

    public function onCheckoutConfirmPageLoaded(CheckoutConfirmPageLoadedEvent $event): void
    {
        $event->getPage()->addExtension('ar_rewardpoints', new ArrayEntity([
            'used' => $this->spendPoints->getPointsForSession(),
            'total' => $this->spendPoints->getPointsForCustomer($event->getSalesChannelContext()->getCustomer()->getId())
        ]));
    }
}
