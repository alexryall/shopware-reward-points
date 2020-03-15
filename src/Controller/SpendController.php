<?php declare(strict_types=1);

namespace AlexRyall\RewardPoints\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AlexRyall\RewardPoints\Service\SpendPoints;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class SpendController extends StorefrontController
{
    /**
     * @var SpendPoints
     */
    private $spendPoints;

    public function __construct(SpendPoints $spendPoints)
    {
        $this->spendPoints = $spendPoints;
    }

    /**
     * @Route("/checkout/spend-reward-points/change-amount", name="frontend.checkout.spend_reward_points.change-amount", defaults={"XmlHttpRequest": true}, methods={"POST"})
     */
    public function changeQuantity(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $this->spendPoints->setPointsForSession($request->get('ar_rewardpoints'), $salesChannelContext->getCustomer());
        return $this->createActionResponse($request);
    }
}
