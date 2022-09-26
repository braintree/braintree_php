<?php

namespace Braintree;

/**
 * Braintree LiabilityShift class
 *
 * If enrolled in Chargeback Protection, returns any information regarding scenarios where liability in the event of a chargeback is shifted from the merchant to another party.
 *
 * See our {@link https://developer.paypal.com/braintree/docs/reference/response/liability_shift developer docs} for information on attributes
 */
class LiabilityShift extends Modification
{
    /**
     * Creates an instance of a LiabilityShift from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return LiabilityShift
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
