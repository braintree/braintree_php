<?php
/**
 * Braintree Subscription module
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on Subscriptions, see {@link http://www.braintreepaymentsolutions.com/gateway/subscription-api http://www.braintreepaymentsolutions.com/gateway/subscription-api}
 *
 * PHP Version 5
 *
 * @package   Braintree
 * @copyright 2010 Braintree Payment Solutions
 */
class Braintree_Subscription extends Braintree
{
    const ACTIVE = 'Active';
    const CANCELED = 'Canceled';
    const EXPIRED = 'Expired';
    const PAST_DUE = 'Past Due';

    public static function create($attributes)
    {
        Braintree_Util::verifyKeys(self::createSignature(), $attributes);
        $response = Braintree_Http::post('/subscriptions', array('subscription' => $attributes));
        return self::_verifyGatewayResponse($response);
    }

    /**
     * @ignore
     */
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

    public static function search($query)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }

        $response = braintree_http::post('/subscriptions/advanced_search_ids', array('search' => $criteria));
        $pager = array(
            'className' => __CLASS__,
            'classMethod' => 'fetch',
            'methodArgs' => array($query)
            );

        return new Braintree_ResourceCollection($response, $pager);
    }

    public static function fetch($query, $ids)
    {
        $criteria = array();
        foreach ($query as $term) {
            $criteria[$term->name] = $term->toparam();
        }
        $criteria["ids"] = Braintree_SubscriptionSearch::ids()->in($ids)->toparam();
        $response = Braintree_Http::post('/subscriptions/advanced_search', array('search' => $criteria));

        return Braintree_Util::extractAttributeAsArray(
            $response['subscriptions'],
            'subscription'
        );
    }

    public static function update($subscriptionId, $attributes)
    {
        Braintree_Util::verifyKeys(self::updateSignature(), $attributes);
        $response = Braintree_Http::put(
            '/subscriptions/' . $subscriptionId,
            array('subscription' => $attributes)
        );
        return self::_verifyGatewayResponse($response);
    }

    public static function retryCharge($subscriptionId, $amount = null)
    {
        $transaction_params = array('type' => Braintree_Transaction::SALE,
            'subscriptionId' => $subscriptionId);
        if (isset($amount)) {
            $transaction_params['amount'] = $amount;
        }

        $response = Braintree_Http::post(
            '/transactions',
            array('transaction' => $transaction_params));
        return self::_verifyGatewayResponse($response);
    }

    public static function cancel($subscriptionId)
    {
        $response = Braintree_Http::put('/subscriptions/' . $subscriptionId . '/cancel');
        return self::_verifyGatewayResponse($response);
    }

    private static function createSignature()
    {
        return array(
            'merchantAccountId', 'numberOfBillingCycles', 'paymentMethodToken', 'planId',
            'id', 'neverExpires', 'price', 'trialPeriod', 'trialDuration', 'trialDurationUnit',
            array(
                'addOns' => array(
                    array('update' => array('amount', 'existingId', 'neverExpires', 'numberOfBillingCycles', 'quantity')),
                    array('remove' => array('_anyKey_')),
                )
            ),
            array(
                'discounts' => array(
                    array('update' => array('amount', 'existingId', 'neverExpires', 'numberOfBillingCycles', 'quantity')),
                    array('remove' => array('_anyKey_')),
                )
            ),
            array('options' => array('doNotInheritAddOnsOrDiscounts')),
        );
    }

    private static function updateSignature()
    {
        return array(
            'merchantAccountId', 'numberOfBillingCycles', 'paymentMethodToken', 'planId',
            'id', 'neverExpires', 'price',
        );
    }

    /**
     * @ignore
     */
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;

        $addOnArray = array();
        if (isset($attributes['addOns'])) {
            foreach ($attributes['addOns'] AS $addOn) {
                $addOnArray[] = Braintree_AddOn::factory($addOn);
            }
        }
        $this->_attributes['addOns'] = $addOnArray;

        $discountArray = array();
        if (isset($attributes['discounts'])) {
            foreach ($attributes['discounts'] AS $discount) {
                $discountArray[] = Braintree_Discount::factory($discount);
            }
        }
        $this->_attributes['discounts'] = $discountArray;

        $transactionArray = array();
        if (isset($attributes['transactions'])) {
            foreach ($attributes['transactions'] AS $transaction) {
                $transactionArray[] = Braintree_Transaction::factory($transaction);
            }
        }
        $this->_attributes['transactions'] = $transactionArray;
    }

    /**
     * @ignore
     */
    private static function _verifyGatewayResponse($response)
    {
        if (isset($response['subscription'])) {
            return new Braintree_Result_Successful(
                self::factory($response['subscription'])
            );
        } else if (isset($response['transaction'])) {
            // return a populated instance of Braintree_Transaction, for subscription retryCharge
            return new Braintree_Result_Successful(
                Braintree_Transaction::factory($response['transaction'])
            );
        } else if (isset($response['apiErrorResponse'])) {
            return new Braintree_Result_Error($response['apiErrorResponse']);
        }
    }
}
?>
