<?php
final class Braintree_Disbursement extends Braintree
{
    private $_merchantAccount;

    protected function _initialize($disbursementAttribs)
    {
        $this->_attributes = $disbursementAttribs;
        $this->_merchantAccount = NULL;
    }

    public function merchantAccount()
    {
        if(is_null($this->_merchantAccount))
        {
            $this->_merchantAccount = Braintree_MerchantAccount::factory($this->merchantAccount);
        }

        return $this->_merchantAccount;
    }

    public function transactions()
    {
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::ids()->in($this->transactionIds)
        ));

        return $collection;
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
            'id', 'merchantAccount', 'exceptionMessage', 'amount',
            'disbursementDate', 'followUpAction', 'retry', 'success',
            'transactionIds'
            );

        $displayAttributes = array();
        foreach ($display AS $attrib) {
            $displayAttributes[$attrib] = $this->$attrib;
        }
        return __CLASS__ . '[' .
                Braintree_Util::attributesToString($displayAttributes) .']';
    }
}
