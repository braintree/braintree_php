<?php
/**
 * Braintree Subscription module
 *
 * <b>== More information ==</b>
 *
 * For more detailed information on Subscriptions, see {@link http://www.braintreepayments.com/gateway/subscription-api http://www.braintreepaymentsolutions.com/gateway/subscription-api}
 *
 * PHP Version 5
 *
 * @package   Braintree
 * @copyright 2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_Subscription extends Braintree
{
    const ACTIVE = 'Active';
    const CANCELED = 'Canceled';
    const EXPIRED = 'Expired';
    const PAST_DUE = 'Past Due';
    const PENDING = 'Pending';

    public static function create($attributes)
    {
        Braintree_Util::verifyKeys(self::_createSignature(), $attributes);
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
        self::_validateId($id);

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


        $response = Braintree_Http::post('/subscriptions/advanced_search_ids', array('search' => $criteria));
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
        Braintree_Util::verifyKeys(self::_updateSignature(), $attributes);
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

    private static function _createSignature()
    {
        return array_merge(
            array(
                'billingDayOfMonth',
                'firstBillingDate',
                'id',
                'merchantAccountId',
                'neverExpires',
                'numberOfBillingCycles',
                'paymentMethodToken',
                'paymentMethodNonce',
                'planId',
                'price',
                'trialDuration',
                'trialDurationUnit',
                'trialPeriod',
                array('descriptor' => array('name', 'phone')),
                array('options' => array('doNotInheritAddOnsOrDiscounts', 'startImmediately')),
            ),
            self::_addOnDiscountSignature()
        );
    }

    private static function _updateSignature()
    {
        return array_merge(
            array(
                'merchantAccountId', 'numberOfBillingCycles', 'paymentMethodToken', 'planId',
                'paymentMethodNonce', 'id', 'neverExpires', 'price',
                array('descriptor' => array('name', 'phone')),
                array('options' => array('prorateCharges', 'replaceAllAddOnsAndDiscounts', 'revertSubscriptionOnProrationFailure')),
            ),
            self::_addOnDiscountSignature()
        );
    }

    private static function _addOnDiscountSignature()
    {
        return array(
            array(
                'addOns' => array(
                    array('add' => array('amount', 'inheritedFromId', 'neverExpires', 'numberOfBillingCycles', 'quantity')),
                    array('update' => array('amount', 'existingId', 'neverExpires', 'numberOfBillingCycles', 'quantity')),
                    array('remove' => array('_anyKey_')),
                )
            ),
            array(
                'discounts' => array(
                    array('add' => array('amount', 'inheritedFromId', 'neverExpires', 'numberOfBillingCycles', 'quantity')),
                    array('update' => array('amount', 'existingId', 'neverExpires', 'numberOfBillingCycles', 'quantity')),
                    array('remove' => array('_anyKey_')),
                )
            )
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

        if (isset($attributes['descriptor'])) {
            $this->_set('descriptor', new Braintree_Descriptor($attributes['descriptor']));
        }

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
    private static function _validateId($id = null) {
        if (empty($id)) {
           throw new InvalidArgumentException(
                   'expected subscription id to be set'
                   );
        }
        if (!preg_match('/^[0-9A-Za-z_-]+$/', $id)) {
            throw new InvalidArgumentException(
                    $id . ' is an invalid subscription id.'
                    );
        }
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
        } else {
            throw new Braintree_Exception_Unexpected(
            "Expected subscription, transaction, or apiErrorResponse"
            );
        }
    }

    /**
     * returns a string representation of the customer
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__ . '[' .
                Braintree_Util::attributesToString($this->_attributes) .']';
    }

}
