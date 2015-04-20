<?php

namespace Braintree;

final class Disbursement extends Braintree
{
    private $_merchantAccount;

    protected function _initialize($disbursementAttribs)
    {
        $this->_attributes = $disbursementAttribs;
        $this->merchantAccountDetails = $disbursementAttribs['merchantAccount'];

        if (isset($disbursementAttribs['merchantAccount'])) {
            $this->_set('merchantAccount',
                MerchantAccount::factory($disbursementAttribs['merchantAccount'])
            );
        }
    }

    public function transactions()
    {
        $collection = Transaction::search(array(
            TransactionSearch::ids()->in($this->transactionIds),
        ));

        return $collection;
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);

        return $instance;
    }

    public function __toString()
    {
        $display = array(
            'id', 'merchantAccountDetails', 'exceptionMessage', 'amount',
            'disbursementDate', 'followUpAction', 'retry', 'success',
            'transactionIds',
            );

        $displayAttributes = array();
        foreach ($display as $attrib) {
            $displayAttributes[$attrib] = $this->$attrib;
        }

        return __CLASS__.'['.
                Util::attributesToString($displayAttributes).']';
    }
}
