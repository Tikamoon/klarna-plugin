<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\KlarnaPlugin\Action;

use Monolog\Logger;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Tikamoon\KlarnaPlugin\Bridge\KlarnaBridgeInterface;
use Webmozart\Assert\Assert;
use Payum\Core\Payum;
use Psr\Log\LoggerInterface;
use Tikamoon\KlarnaPlugin\Service\KlarnaService;

/**
 * @author Vincent Notebaert <vnotebaert@kiosc.com>
 * @author @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var Payum
     */
    private $payum;

    /**
     * @var KlarnaBridgeInterface
     */
    private $klarnaBridge;

    /** @var LoggerInterface */
    private $logger;

    /** @var KlarnaService */
    private $klarnaService;

    /**
     * @param Payum $payum
     * @param LoggerInterface $logger
     */
    public function __construct(Payum $payum, $logger, KlarnaService $klarnaService)
    {
        $this->payum = $payum;
        $this->logger = $logger;
        $this->klarnaService = $klarnaService;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($klarnaBridge)
    {
        if (!$klarnaBridge instanceof KlarnaBridgeInterface) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->klarnaBridge = $klarnaBridge;
    }

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        // After uninstall mollie plugin by bitbag, prefer to use authorization_token in payment details
        if (isset($_SESSION['authorization_token'])) {
            $placeOrder = $this->klarnaService->placeOrder($payment->getOrder(), $_SESSION['authorization_token']);
            $model['place_order_response'] = $placeOrder;
        }

        $request->setModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
