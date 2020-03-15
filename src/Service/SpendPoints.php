<?php declare(strict_types = 1);

namespace AlexRyall\RewardPoints\Service;

use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use AlexRyall\RewardPoints\Transaction\TransactionEntity;

class SpendPoints
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var EntityRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        SessionInterface $session,
        SystemConfigService $systemConfigService,
        EntityRepositoryInterface $transactionRepository,
        EntityRepositoryInterface $orderRepository
    )
    {
        $this->session = $session;
        $this->systemConfigService = $systemConfigService;
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository = $orderRepository;
    }

    public function getPointsForSession(): int
    {
        return (int)$this->session->get('ar_rewardpoints');
    }

    public function setPointsForSession($points, $customer): void
    {
        if ($points >= 0 && $points <= $this->getPointsForCustomer($customer->getId())) {
            $this->session->set('ar_rewardpoints', (int)$points);
        }
    }

    public function getPointsForCustomer($customerId): int
    {
        $points = 0;
        $transactions = $this->transactionRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('customerId', $customerId)),
            \Shopware\Core\Framework\Context::createDefaultContext()
        );
        foreach ($transactions as $transaction) {
            $points += $transaction->getPoints();
        }
        return $points;
    }

    public function spendPointsForOrder($orderNumber): void
    {
        $points = $this->getPointsForSession();
        $orders = $this->orderRepository->search(
            new Criteria([
                $orderNumber
            ]),
            Context::createDefaultContext()
        );
        $order = $orders->first();
        if ($points
            && $points <= $this->getPointsForCustomer($order->getOrderCustomer()->getCustomerId())
            && $this->systemConfigService->get('RewardPoints.config.enabled')) {
            $this->transactionRepository->upsert([
                [
                    'action' => TransactionEntity::ACTION_ORDER,
                    'points' => (int)-$points,
                    'customerId' => $order->getOrderCustomer()->getCustomerId(),
                    'orderId' => $order->getId(),
                    'orderVersionId' => $order->getVersionId()
                ]
            ], Context::createDefaultContext());
            $this->session->set('ar_rewardpoints', 0);
        }
    }
}
