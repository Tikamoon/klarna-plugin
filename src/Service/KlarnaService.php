<?php

declare(strict_types=1);

namespace Tikamoon\KlarnaPlugin\Service;

use App\Entity\Order\Order;
use App\Entity\Payment\GatewayConfig;
use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Model\GatewayConfig as ModelGatewayConfig;
use Payum\Core\Model\GatewayConfigInterface;
use Psr\Http\Message\ResponseInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class KlarnaService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        EntityManagerInterface $em,
        RouterInterface $router
    ) {
        $this->em = $em;
        $this->router = $router;
    }

    public function __toString()
    {
        return 'klarna_service';
    }

    public function getSession(GatewayConfig $gatewayConfig, Order $order): array
    {
        $config = $gatewayConfig->getConfig();

        $session = $this->request(
            $config['environment'] . 'sessions',
            $gatewayConfig,
            $this->formatOrder($order),
            'POST'
        );

        $payment = $order->getLastPayment();
        $payment->setDetails($session);
        $this->em->persist($payment);
        $this->em->flush();

        return $session;
    }

    public function placeOrder(Order $order, $token): array
    {
        $payment = $order->getLastPayment();

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        $config = $gatewayConfig->getConfig();

        $placeOrder = $this->request(
            $config['environment'] . sprintf('authorizations/%s/order', $token),
            $gatewayConfig,
            $this->formatOrder($order),
            'POST'
        );

        return $placeOrder;
    }

    private function formatOrder(Order $order): string
    {
        /** @var Channel $channel */
        $channel = $order->getChannel();

        $products = [];

        foreach ($order->getItems() as $item) {
            $product = [
                "name" => $item->getProductName(),
                "quantity" => $item->getQuantity(),
                "reference" => $item->getProduct()->getCode(),
                "total_amount" => $item->getTotal(),
                "type" => "physical",
                "unit_price" => $item->getTotal() / $item->getQuantity(),
                "image_url" => $_ENV['AROBASES_URL'] . $item->getMainImagePath()
            ];
            $products[] = $product;
        }

        $shippingInfo = [
            "name" => "Order tax amount",
            "quantity" => 1,
            "total_amount" => $order->getAdjustmentsTotal(),
            "type" => "shipping_fee",
            "unit_price" => $order->getAdjustmentsTotal()
        ];

        $products[] = $shippingInfo;

        $merchantUrls = [
            "confirmation" => $this->router->generate('sylius_shop_order_thank_you', [], UrlGeneratorInterface::ABSOLUTE_URL),
            "notification" => $this->router->generate('payum_notify_do_unsafe', ['gateway' => 'klarna'], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        $countryCode = $channel->getDefaultCountry()->getCode();
        if ($order->getBillingAddress()) {
            $countryCode = $order->getBillingAddress()->getCountryCode();
        }

        $data = array(
            "purchase_country" => $countryCode,
            "purchase_currency" => $order->getCurrencyCode(),
            "locale" => str_replace('_', '-', $order->getLocaleCode()),
            "order_amount" => $order->getTotal(),
            "order_lines" => $products,
            "merchants_urls" => $merchantUrls,
            "merchant_reference1" => $order->getId()
        );


        $data['billing_address'] = $this->formatBillingAddress($order);
        $data['shipping_address'] = $this->formatShippingAddress($order);

        return json_encode($data);
    }

    private function formatShippingAddress(Order $order): array
    {
        $shippingAddress = $order->getShippingAddress();

        return [
            'street_address' => $shippingAddress->getStreet(),
            'postal_code' => $shippingAddress->getPostcode(),
            'city' => $shippingAddress->getCity(),
            'country' => $shippingAddress->getCountryCode(),
            'given_name' => $shippingAddress->getFirstName(),
            'family_name' => $shippingAddress->getLastName(),
            'email' => $order->getCustomer()->getEmail(),
        ];
    }

    private function formatBillingAddress(Order $order): array
    {
        $billingAddress = $order->getBillingAddress();

        return [
            'street_address' => $billingAddress->getStreet(),
            'postal_code' => $billingAddress->getPostcode(),
            'city' => $billingAddress->getCity(),
            'country' => $billingAddress->getCountryCode(),
            'given_name' => $billingAddress->getFirstName(),
            'family_name' => $billingAddress->getLastName(),
            'email' => $order->getCustomer()->getEmail(),
        ];
    }

    private function request(string $uri, GatewayConfig $gatewayConfig, string $body, string $method): array
    {
        $config = $gatewayConfig->getConfig();

        $ch = curl_init($uri);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $authorization = $config['username'] . ':' . $config['password'];

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body),
                "Authorization: Basic " . base64_encode($authorization)
            )
        );

        $response = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $response;
    }
}
