<?php
namespace Tikamoon\KlarnaPlugin;

final class Constants
{
    const STATUS_CHECKOUT_INCOMPLETE = 'checkout_incomplete';

    const STATUS_CHECKOUT_COMPLETE = 'checkout_complete';

    const STATUS_CREATED = 'created';

    const BASE_URI_LIVE = 'https://api.klarna.com/payments/v1/';

    const BASE_URI_SANDBOX = 'https://api.playground.klarna.com/payments/v1/';

    private function __construct()
    {
    }
}
