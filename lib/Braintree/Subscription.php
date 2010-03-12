<?php
/**
 * Braintree Subscription module
 *
 * PHP Version 5
 *
 * @package   Braintree
 * @copyright 2010 Braintree Payment Solutions
 */
class Braintree_Subscription extends Braintree
{
    private $_attributes = array();

    public static function create($attributes)
    {
        $response = Braintree_Http::post('/subscriptions', array('subscription' => $attributes));

        return self::_verifyGatewayResponse($response);
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    public static function find($id)
    {
        try {
            $response = Braintree_Http::get('/subscriptions/' . $id);
            return self::factory($response['subscription']);
        } catch (Braintree_Exception_NotFound $e) {
            throw new Braintree_Exception_NotFound('subscription with id ' . $id . ' not found');
        }

    }

    public static function update($subscriptionId, $attributes)
    {
        $response = Braintree_Http::put(
            '/subscriptions/' . $subscriptionId,
            array('subscription' => $attributes)
        );
        return self::_verifyGatewayResponse($response);
    }

    public static function cancel($subscriptionId)
    {
        $response = Braintree_Http::put('/subscriptions/' . $subscriptionId . '/cancel');
        return self::_verifyGatewayResponse($response);
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        }
        else {
            return parent::__get($name);
        }
    }

    protected function _initialize($attributes)
    {
        $this->_attributes = array_merge($this->_attributes, $attributes);

        $transactionArray = null;
        if (isset($attributes['transactions'])) {
            foreach ($attributes['transactions'] AS $transaction) {
                $transactionArray[] = Braintree_Transaction::factory($transaction);
            }
        }
        $this->_attributes['transactions'] = $transactionArray;
    }

    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['subscription'])) {
            return new Braintree_Result_Successful(
                self::factory($response['subscription'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        }
    }
}
?>
