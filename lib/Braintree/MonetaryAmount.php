<?php

namespace Braintree;

/**
 * Braintree MonetaryAmount module
 */
class MonetaryAmount extends Base
{
    protected $_attributes = [
        'value' => '',
        'currencyCode' => ''
    ];

    protected function _initialize($monetaryAmount)
    {
        $this->_attributes = $monetaryAmount;
        if (isset($monetaryAmount['value'])) {
            $this->_set('value', $monetaryAmount['value']);
        }
        if (isset($monetaryAmount['currencyCode'])) {
            $this->_set('currencyCode', $monetaryAmount['currencyCode']);
        }
    }

    /**
     * Creates an instance of an MonetaryAmount from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return MonetaryAmount
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
