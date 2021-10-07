<?php

namespace Tikamoon\KlarnaPlugin;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Tikamoon\KlarnaPlugin\Action\NotifyAction;
use Tikamoon\KlarnaPlugin\Action\StatusAction;

final class KlarnaGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(array(
            'payum.factory_name' => 'klarna_checkout',
            'payum.factory_title' => 'Klarna Checkout',
            'payum.template.authorize' => '@PayumKlarnaCheckout/Action/capture.html.twig',
            'sandbox' => true,
        ));

        $config->defaults(array(
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
        ));
    }
}
