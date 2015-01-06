<?php
/**
 * Braintree CoinbaseAccountGateway module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * Manages Braintree CoinbaseAccounts
 *
 * <b>== More information ==</b>
 *
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_CoinbaseAccountGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_http = new Braintree_Http($gateway->config);
    }


    /**
     * find a coinbaseAccount by token
     *
     * @access public
     * @param string $token coinbase accountunique id
     * @return object Braintree_CoinbaseAccount
     * @throws Braintree_Exception_NotFound
     */
    public function find($token)
    {
        $this->_validateId($token);
        try {
            $path = $this->_config->merchantPath() . '/payment_methods/coinbase_account/' . $token;
            $response = $this->_http->get($path);
            return Braintree_CoinbaseAccount::factory($response['coinbaseAccount']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound(
                'coinbase account with token ' . $token . ' not found'
            );
        }

    }

    public function delete($token)
    {
        $this->_validateId($token);
        $path = $this->_config->merchantPath() . '/payment_methods/coinbase_account/' . $token;
        $this->_http->delete($path);
        return new Braintree_Result_Successful();
    }

    /**
     * create a new sale for the current Coinbase account
     *
     * @param string $token
     * @param array $transactionAttribs
     * @return object Braintree_Result_Successful or Braintree_Result_Error
     * @see Braintree_Transaction::sale()
     */
    public function sale($token, $transactionAttribs)
    {
        $this->_validateId($token);
        return Braintree_Transaction::sale(
            array_merge(
                $transactionAttribs,
                array('paymentMethodToken' => $token)
            )
        );
    }

    /**
     * verifies that a valid coinbase account identifier is being used
     * @ignore
     * @param string $identifier
     * @throws InvalidArgumentException
     */
    private function _validateId($identifier = null)
    {
        if (empty($identifier)) {
           throw new InvalidArgumentException(
                   'expected coinbase account id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $identifier)) {
            throw new InvalidArgumentException(
                    $identifier . ' is an invalid coinbase account token.');
        }
    }
}
