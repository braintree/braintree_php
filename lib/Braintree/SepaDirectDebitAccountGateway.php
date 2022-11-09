<?php

namespace Braintree;

use InvalidArgumentException;

/**
 * Braintree SepaDirectDebitAccountGateway module
 *
 * Manages Braintree SepaDirectDebitAccounts
 *
 * For more detailed information on Sepa Direct Debit Accounts, see {@link https://developer.paypal.com/braintree/docs/reference/response/sepa-direct-debit-account/php our developer docs}<br />
 */
class SepaDirectDebitAccountGateway
{
    private $_gateway;
    private $_config;
    private $_http;

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __construct($gateway)
    {
        $this->_gateway = $gateway;
        $this->_config = $gateway->config;
        $this->_config->assertHasAccessTokenOrKeys();
        $this->_http = new Http($gateway->config);
    }


    /**
     * Find a sepaDirectDebitAccount by token
     *
     * @param string $token sepa direct debit account unique id
     *
     * @throws Exception\NotFound
     *
     * @return SepaDirectDebitAccount
     */
    public function find($token)
    {
        $this->_validateId($token);
        $path = $this->_config->merchantPath() . '/payment_methods/sepa_debit_account/' . $token;
        $response = $this->_http->get($path);
        return SepaDirectDebitAccount::factory($response['sepaDebitAccount']);
    }

    /**
     * Delete a Sepa Direct Debit Account record
     *
     * @param string $token sepa direct debit account identifier
     *
     * @return Result
     */
    public function delete($token)
    {
        $this->_validateId($token);
        $path = $this->_config->merchantPath() . '/payment_methods/sepa_debit_account/' . $token;
        $this->_http->delete($path);
        return new Result\Successful();
    }

    /**
     * Create a new sale for the current Sepa Direct Debit Account
     *
     * @param string $token              sepa direct debit account identifier
     * @param array  $transactionAttribs containing request parameters
     *
     * @return Result\Successful|Result\Error
     */
    public function sale($token, $transactionAttribs)
    {
        $this->_validateId($token);
        return Transaction::sale(
            array_merge(
                $transactionAttribs,
                ['paymentMethodToken' => $token]
            )
        );
    }

    /**
     * Verifies that a valid sepa direct debit account identifier is being used
     *
     * @param string   $identifier
     * @param Optional string     $identifierType type of identifier supplied, default 'token'
     *
     * @throws InvalidArgumentException
     */
    private function _validateId($identifier = null, $identifierType = 'token')
    {
        if (empty($identifier)) {
            throw new InvalidArgumentException(
                'expected SEPA direct debit account id to be set'
            );
        }
    }
}
