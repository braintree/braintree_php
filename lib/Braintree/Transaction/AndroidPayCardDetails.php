<?php namespace Braintree\Transaction;

    /**
     * Android Pay card details from a transaction
     *
     * @package    Braintree
     * @subpackage Transaction
     * @copyright  2014 Braintree, a division of PayPal, Inc.
     */
use Braintree\Instance;

/**
 * creates an instance of AndroidPayCardDetails
 *
 *
 * @package    Braintree
 * @subpackage Transaction
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 * @property-read string $bin
 * @property-read string $default
 * @property-read string $expirationMonth
 * @property-read string $expirationYear
 * @property-read string $googleTransactionId
 * @property-read string $imageUrl
 * @property-read string $sourceCardLast4
 * @property-read string $sourceCardType
 * @property-read string $token
 * @property-read string $virtualCardLast4
 * @property-read string $virtualCardType
 * @uses Instance inherits methods
 */
class AndroidPayCardDetails extends Instance
{
    protected $_attributes = array();

    /**
     * @ignore
     */
    public function __construct($attributes)
    {
        parent::__construct($attributes);
        $this->_attributes['cardType'] = $this->virtualCardType;
        $this->_attributes['last4'] = $this->virtualCardLast4;
    }
}