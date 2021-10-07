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

    /** @var array */
    private $klarnaDelays;

    public function __construct(KlarnaService $klarnaService, array $klarnaMinAmounts, array $klarnaMaxAmounts, array $klarnaDelays)
    {
        $this->klarnaService = $klarnaService;
        $this->klarnaMinAmounts = $klarnaMinAmounts;
        $this->klarnaMaxAmounts = $klarnaMaxAmounts;
        $this->klarnaDelays = $klarnaDelays;
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
        $isDelayOK = in_array($order->getMaxDelay(), $this->klarnaDelays);
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

        return $isDelayOK && $isAmountOK;
    }
}
