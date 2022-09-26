<?php

namespace Braintree;

/**
 * Braintree ExchangeRateQuote module
 * Creates and manages Braintree ExchangeRateQuote
 */

class ExchangeRateQuoteResponse extends Base
{
    protected $_attributes = [
        'quotes'  => '',
    ];

    /**
     * Creates an instance of a ExchangeRateQuoteResponse from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return ExchangeRateQuoteResponse
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }


    protected function _initialize($payloadAttribs)
    {
        $this->_attributes = $payloadAttribs;

        $quotesArray = [];
        if (isset($payloadAttribs['quotes'])) {
            foreach ($payloadAttribs['quotes'] as $quote) {
                $quotesArray[] = ExchangeRateQuote::factory($quote);
            }
        }

        $this->_set('quotes', $quotesArray);
    }
}
