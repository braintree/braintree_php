<?php
/**
 * Creates an instance of Dispute as returned from a transaction
 *
 *
 * @package    Braintree
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $amount
 * @property-read string $currencyIsoCode
 * @property-read date   $receivedDate
 * @property-read string $reason
 * @property-read string $status
 * @property-read string $disbursementDate
 * @uses Braintree_Instance inherits methods
 */
final class Braintree_Dispute extends Braintree_Instance
{
    protected $_attributes = array();

    /* Dispute Status */
    const Open  = 'open';
    const WON  = 'won';
    const LOST = 'lost';

    /* Dispute Reason */
    const CANCELLED_RECURRING_TRANSACTION = "cancelled_recurring_transaction";
    const CREDIT_NOT_PROCESSED            = "credit_not_processed";
    const DUPLICATE                       = "duplicate";
    const FRAUD                           = "fraud";
    const GENERAL                         = "general";
    const INVALID_ACCOUNT                 = "invalid_account";
    const NOT_RECOGNIZED                  = "not_recognized";
    const PRODUCT_NOT_RECEIVED            = "product_not_received";
    const PRODUCT_UNSATISFACTORY          = "product_unsatisfactory";
    const TRANSACTION_AMOUNT_DIFFERS      = "transaction_amount_differs";


    protected function _initialize($disputeAttribs)
    {
        $this->_attributes = $disputeAttribs;
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    public function  __toString()
    {
        $display = array(
            'amount', 'reason', 'status',
            'replyByDate', 'receivedDate', 'currencyIsoCode'
            );

        $displayAttributes = array();
        foreach ($display AS $attrib) {
            $displayAttributes[$attrib] = $this->$attrib;
        }
        return __CLASS__ . '[' .
                Braintree_Util::attributesToString($displayAttributes) .']';
    }
}
