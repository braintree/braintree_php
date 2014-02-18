<?php
final class Braintree_Transfer extends Braintree
{
    protected function _initialize($transferAttribs)
    {
        $this->_attributes = $transferAttribs;
    }

    public function merchantAccount()
    {
        return Braintree_MerchantAccount::find($this->merchantAccountId);
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
