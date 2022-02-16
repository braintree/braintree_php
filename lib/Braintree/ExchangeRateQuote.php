<?php

namespace Braintree;

/**
 * Braintree ExchangeRateQuote module
 * Creates and manages Braintree ExchangeRateQuote
 *
 */
class ExchangeRateQuote extends Base
{
    private $_baseAmount ;
    private $_quoteAmount;
    private $_exchangeRate;
    private $_expiresAt;
    private $_refreshesAt;
    private $_tradeRate;
    private $_id;
    
    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($monetaryAmount)
    {
        $this->_baseAmount = $monetaryAmount->value;
        $this->_quoteAmount = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Http($gateway->config);
    }
    /**
     * Get the value of _id
     */ 
    public function get_id()
    {
        return $this->_id;
    }

    /**
     * Set the value of _id
     *
     * @return  self
     */ 
    public function set_id($_id)
    {
        $this->_id = $_id;

        return $this;
    }

    /**
     * Get the value of _tradeRate
     */ 
    public function get_tradeRate()
    {
        return $this->_tradeRate;
    }

    /**
     * Set the value of _tradeRate
     *
     * @return  self
     */ 
    public function set_tradeRate($_tradeRate)
    {
        $this->_tradeRate = $_tradeRate;

        return $this;
    }

    /**
     * Get the value of _refreshesAt
     */ 
    public function get_refreshesAt()
    {
        return $this->_refreshesAt;
    }

    /**
     * Set the value of _refreshesAt
     *
     * @return  self
     */ 
    public function set_refreshesAt($_refreshesAt)
    {
        $this->_refreshesAt = $_refreshesAt;

        return $this;
    }

    /**
     * Get the value of _expiresAt
     */ 
    public function get_expiresAt()
    {
        return $this->_expiresAt;
    }

    /**
     * Set the value of _expiresAt
     *
     * @return  self
     */ 
    public function set_expiresAt($_expiresAt)
    {
        $this->_expiresAt = $_expiresAt;

        return $this;
    }

    /**
     * Get the value of _exchangeRate
     */ 
    public function get_exchangeRate()
    {
        return $this->_exchangeRate;
    }

    /**
     * Set the value of _exchangeRate
     *
     * @return  self
     */ 
    public function set_exchangeRate($_exchangeRate)
    {
        $this->_exchangeRate = $_exchangeRate;

        return $this;
    }
}
