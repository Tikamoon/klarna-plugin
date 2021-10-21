<?php

/**
 * This file was created by the developers from Tikamoon.

 */

namespace Tikamoon\KlarnaPlugin\Bridge;

use Tikamoon\KlarnaPlugin\Legacy\Klarna;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Vincent Notebaert <vnotebaert@kiosc.com>
 */
final class KlarnaBridge implements KlarnaBridgeInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $apiKeyId;

    /**
     * @var string
     */
    private $accountKey;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $keyVersion;

    /**
     * @var int
     */
    private $numberOfPayments;

    /**
     * @var string
     */
    private $environment;

    /** @var Klarna */
    private $klarna;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
     */
    public function createKlarna($secretKey)
    {
        return new Klarna($secretKey);
    }

    /**
     * {@inheritDoc}
     */
    public function paymentVerification()
    {
        if ($this->isPostMethod()) {

            $this->klarna = new Klarna($this->secretKey);
            $this->klarna->setResponse($_POST);

            return $this->klarna->isValid();
        }

        return false;
    }

    public function getAuthorisationId()
    {
        return $this->klarna->getAuthorisationId();
    }

    /**
     * {@inheritDoc}
     */
    public function isPostMethod()
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        return $currentRequest->isMethod('POST');
    }

    /**
     * @return string
     */
    public function getAccountKey()
    {
        return $this->accountKey;
    }

    /**
     * @param string $accountKey
     */
    public function setAccountKey($accountKey)
    {
        $this->accountKey = $accountKey;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getKeyVersion()
    {
        return $this->keyVersion;
    }

    /**
     * @param string $keyVersion
     */
    public function setKeyVersion($keyVersion)
    {
        $this->keyVersion = $keyVersion;
    }

    /**
     * @return int
     */
    public function getNumberOfPayments(): int
    {
        return $this->numberOfPayments;
    }

    /**
     * @param int $numberOfPayment
     */
    public function setNumberOfPayments(int $numberOfPayment): void
    {
        $this->numberOfPayments = $numberOfPayment;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getApiKeyId(): string
    {
        return $this->apiKeyId;
    }

    /**
     * @param string $apiKeyId
     */
    public function setApiKeyId(string $apiKeyId): void
    {
        $this->apiKeyId = $apiKeyId;
    }
}
