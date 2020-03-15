<?php declare(strict_types=1);

namespace AlexRyall\RewardPoints\Subscriber;

use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AlexRyall\RewardPoints\Service\AwardPoints;
use AlexRyall\RewardPoints\Service\SpendPoints;

class OrderWritten implements EventSubscriberInterface
{
    /**
     * @var AwardPoints
     */
    private $awardPoints;

    /**
     * @var SpendPoints
     */
    private $spendPoints;

    public function __construct(
        AwardPoints $awardPoints,
        SpendPoints $spendPoints
    )
    {
        $this->awardPoints = $awardPoints;
        $this->spendPoints = $spendPoints;
    }

    public static function getSubscribedEvents(): array
    {
        return[
            OrderEvents::ORDER_WRITTEN_EVENT => 'onOrderWritten'
        ];
    }

    public function onOrderWritten(EntityWrittenEvent $event)
    {
        $payload = $event->getWriteResults()[0]->getPayload();
        $this->spendPoints->spendPointsForOrder($payload['id']);
        $this->awardPoints->awardPointsForOrder($payload['id']);
    }
}
