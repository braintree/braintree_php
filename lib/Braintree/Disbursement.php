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
            $this->_merchantAccount = Braintree_MerchantAccount::find($this->merchantAccountId);
        }

        return $this->_merchantAccount;
    }

    public function transactions()
    {
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::merchantAccountId()->is($this->merchantAccountId),
            Braintree_TransactionSearch::disbursementDate()->is($this->disbursementDate)
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
            'id', 'merchantAccountId', 'message', 'amount',
            'disbursementDate', 'followUpAction'
            );

        $displayAttributes = array();
        foreach ($display AS $attrib) {
            $displayAttributes[$attrib] = $this->$attrib;
        }
        return __CLASS__ . '[' .
                Braintree_Util::attributesToString($displayAttributes) .']';
    }
}
