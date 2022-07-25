<?php

namespace Braintree;

/**
 * Braintree Exchange Rate Quote API module
 * Creates ExchangeRateQuoteInput for an ExchangeRateQuoteRequest
 *
 * An ExchangeRateQuote includes basecurrency, quotecurrency, baseAmount
 * and markup
 * See our {@link https://graphql.braintreepayments.com/reference/#input_object--exchangeratequoteinput graphql reference docs} for information on attributes
 */
class ExchangeRateQuoteInput extends Base
{
    protected $_attributes = [
        'baseCurrency' => '',
        'quoteCurrency' => '',
        'baseAmount' => '',
        'markup' => '',
    ];

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    protected function _initialize($quoteAttribs)
    {
        $this->_attributes = $quoteAttribs;

        if (isset($quoteAttribs['baseCurrency'])) {
            $this->_set('baseCurrency', $quoteAttribs['baseCurrency']);
        }
        if (isset($quoteAttribs['quoteCurrency'])) {
            $this->_set('quoteCurrency', $quoteAttribs['quoteCurrency']);
        }
        if (isset($quoteAttribs['baseAmount'])) {
            $this->_set('baseAmount', $quoteAttribs['baseAmount']);
        }
        if (isset($quoteAttribs['markup'])) {
            $this->_set('markup', $quoteAttribs['markup']);
        }
    }
    /**
     * Creates an instance of a ExchangeRateQuoteInput from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return ExchangeRateQuoteInput
     */
    public static function factory($attributes)
    {
        $instance = new ExchangeRateQuoteInput();
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
