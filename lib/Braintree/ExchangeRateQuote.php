<?php

namespace Braintree;

/**
 * Braintree ExchangeRateQuoteAPI module
 * Creates and manages ExchangeRateQuote
 *
 * See our {@link https://graphql.braintreepayments.com/reference/#Mutation--generateExchangeRateQuote graphql reference docs} for information on attributes
 */
class ExchangeRateQuote extends Base
{
    protected $_attributes = [
        'id' => '',
        'baseAmount' => '',
        'quoteAmount' => '',
        'exchangeRate' => '',
        'tradeRate' => '',
        'expiresAt' => '',
        'refreshesAt' => ','
    ];

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    protected function _initialize($quoteAttribs)
    {
        $this->_attributes = $quoteAttribs;

        $baseMonetaryAmount = null;
        if (isset($quoteAttribs['baseAmount'])) {
            $baseMonetaryAmount = MonetaryAmount::factory($quoteAttribs['baseAmount']);
        }
        $this->_set('baseAmount', $baseMonetaryAmount);
        $quoteMonetaryAmount = null;
        if (isset($quoteAttribs['quoteAmount'])) {
            $quoteMonetaryAmount = MonetaryAmount::factory($quoteAttribs['quoteAmount']);
        }
        $this->_set('quoteAmount', $quoteMonetaryAmount);
        if (isset($quoteAttribs['exchangeRate'])) {
            $this->_set('exchangeRate', $quoteAttribs['exchangeRate']);
        }
        if (isset($quoteAttribs['tradeRate'])) {
            $this->_set('tradeRate', $quoteAttribs['tradeRate']);
        }
        if (isset($quoteAttribs['expiresAt'])) {
            $this->_set('expiresAt', $quoteAttribs['expiresAt']);
        }
        if (isset($quoteAttribs['refreshesAt'])) {
            $this->_set('refreshesAt', $quoteAttribs['refreshesAt']);
        }
    }
    /**
     * Creates an instance of a ExchangeRateQuote from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return ExchangeRateQuote
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
