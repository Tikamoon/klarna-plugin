<?php

declare(strict_types=1);

namespace Tikamoon\KlarnaPlugin\Twig;

use Twig\Environment;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Tikamoon\KlarnaPlugin\Service\KlarnaService;

class KlarnaExtension extends AbstractExtension
{
    /** @var KlarnaService */
    private $klarnaService;

    /** @var array */
    private $klarnaMinAmounts;

    /** @var array */
    private $klarnaMaxAmounts;

    public function __construct(KlarnaService $klarnaService, array $klarnaMinAmounts, array $klarnaMaxAmounts)
    {
        $this->klarnaService = $klarnaService;
        $this->klarnaMinAmounts = $klarnaMinAmounts;
        $this->klarnaMaxAmounts = $klarnaMaxAmounts;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getWidget', [$this, 'getWidget'], ['needs_environment' => true]),
            new TwigFunction('hasMethodAvailable', [$this, 'hasMethodAvailable'])
        ];
    }

    public function getWidget(Environment $environment, $gatewayConfig): string
    {
        return $environment->render("@klarna/_payments_method.html.twig", ['config' => $gatewayConfig]);
    }

    public function hasMethodAvailable($order): bool
    {
        $country = $order->getBillingAddress()->getCountryCode();
        $orderTotal = $order->getTotal();
        $minAmount = 0;
        $maxAmount = null;
        $isAmountOK = false;

        foreach ($this->klarnaMinAmounts as $klarnaMethod => $klarnaMinAmount) {
            if (array_key_exists($country, $klarnaMinAmount)) {
                $minAmount = $klarnaMinAmount[$country];
                $maxAmount = $this->klarnaMaxAmounts[$klarnaMethod][$country];
                $isAmountOK = ($minAmount <= $orderTotal && $maxAmount >= $orderTotal);

                if ($isAmountOK) {
                    break;
                }
            }
        }

        return $isAmountOK;
    }
}
