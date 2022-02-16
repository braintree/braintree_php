<?php

namespace Braintree;

/**
 * Braintree ExchangeRateQuote module
 * Creates and manages Braintree ExchangeRateQuote
 *
 */

class MonetaryAmount extends Base {

private $_value;

private $_currencyCode;



/**
 * Get the value of _value
 */ 
public function get_value()
{
return $this->_value;
}

/**
 * Set the value of _value
 *
 * @return  self
 */ 
public function set_value($_value)
{
$this->_value = $_value;

return $this;
}

/**
 * Get the value of _currencyCode
 */ 
public function get_currencyCode()
{
return $this->_currencyCode;
}

/**
 * Set the value of _currencyCode
 *
 * @return  self
 */ 
public function set_currencyCode($_currencyCode)
{
$this->_currencyCode = $_currencyCode;

return $this;
}
}