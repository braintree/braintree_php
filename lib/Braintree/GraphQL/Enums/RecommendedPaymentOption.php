<?php

namespace Braintree\GraphQL\Enums;

/**
 * Represents available payment options related to PayPal customer session recommendations.
 *
 * @experimental This enum is experimental and may change in future releases.
 */
class RecommendedPaymentOption
{
    const PAYPAL = 'PAYPAL';
    const VENMO = 'VENMO';
}
