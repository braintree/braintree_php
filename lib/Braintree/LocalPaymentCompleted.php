<?php

namespace Braintree;

/**
 * Braintree LocalPaymentCompleted module
 */

/**
 * Manages Braintree LocalPaymentCompleted
 *
 * See our {@link https://developer.paypal.com/braintree/docs/reference/general/webhooks/local-payment-methods developer docs} for more information
 */
class LocalPaymentCompleted extends Base
{
    /**
     * Creates an instance from given attributes
     *
     * @param array $attributes response object attributes
     *
     * @return LocalPaymentCompleted
     */
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /* instance methods */

    /**
     * sets instance properties from an array of values
     *
     * @param array $LocalPaymentCompletedAttribs array of localPaymentCompleted data
     *
     * @return void
     */
    protected function _initialize($localPaymentCompletedAttribs)
    {
        // set the attributes
        $this->_attributes = $localPaymentCompletedAttribs;

        if (isset($transactionAttribs['transaction'])) {
            $this->_set(
                'transaction',
                new Transaction(
                    $transactionAttribs['transaction']
                )
            );
        }

        $blikAliasesArray = array();
        if (isset($localPaymentCompletedAttribs['blikAliases'])) {
            foreach ($localPaymentCompletedAttribs['blikAliases'] as $blikAlias) {
                $blikAliasesArray[] = BlikAlias::factory($blikAlias);
            }
        }

        $this->_set('blikAliases', $blikAliasesArray);
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __toString()
    {
        return __CLASS__ . '[' .
                Util::attributesToString($this->_attributes) . ']';
    }
}
