<?php

namespace Braintree;

/**
 * Braintree ExchangeRateQuoteAPI module
 * Creates and manages ExchangeRateQuoteRequest
 *
 * <b>== More information ==</b>
 */
class ExchangeRateQuoteRequest extends Base
{
    protected $_attributes = [
        'quotes' => ''
    ];

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    protected function _initialize($quoteReq)
    {
        $this->_attributes = $quoteReq;

        $quoteslist = null;
        if (isset($quoteReq['quotes'])) {
            $quotesList = ExchangeRateQuoteInput::factory($quoteReq['quotes']);
        }
        $this->_set('quotes', $quotesList);
    }
    /**
     * Creates an instance of a ExchangeRateQuote from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return ExchangeRateQuoteRequest
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __toString()
    {
        return __CLASS__ . '[' .
            Util::attributesToString($this->_attributes) . ']';
    }
}
