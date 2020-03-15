<?php declare(strict_types = 1);

namespace AlexRyall\RewardPoints\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use AlexRyall\RewardPoints\Transaction\TransactionEntity;

class AwardPoints
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        $systemConfigService,
        $orderRepository,
        $transactionRepository
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function awardPointsForOrder($orderNumber): void
    {
        if ($this->systemConfigService->get('RewardPoints.config.enabled')) {
            $orders = $this->orderRepository->search(
                new Criteria([
                    $orderNumber
                ]),
                Context::createDefaultContext()
            );
            $order = $orders->first();
            $this->transactionRepository->upsert([
                [
                    'action' => TransactionEntity::ACTION_ORDER,
                    'points' => (int)floor($order->getAmountTotal() / $this->systemConfigService->get('RewardPoints.config.earningRate')),
                    'customerId' => $order->getOrderCustomer()->getCustomerId(),
                    'orderId' => $order->getId(),
                    'orderVersionId' => $order->getVersionId()
                ]
            ], Context::createDefaultContext());
        }
    }
}
