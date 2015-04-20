<?php

require_once realpath(dirname(__FILE__)).'/../TestHelper.php';

class Braintree_TransactionTest extends PHPUnit_Framework_TestCase
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $t = Braintree_Transaction::factory(array(
            'creditCard' => array('expirationMonth' => '05', 'expirationYear' => '2010', 'bin' => '510510', 'last4' => '5100'),
            'customer' => array(),
            'billing' => array(),
            'descriptor' => array(),
            'shipping' => array(),
            'subscription' => array('billingPeriodStartDate' => '1983-07-12'),
            'statusHistory' => array(),
        ));
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree_Transaction: foo');
        $t->foo;
    }

    public function testCloneTransaction_RaisesErrorOnInvalidProperty()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Transaction::cloneTransaction('an id', array('amount' => '123.45', 'invalidProperty' => 'foo'));
    }

    public function testErrorsWhenFindWithBlankString()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Transaction::find('');
    }

    public function testErrorsWhenFindWithWhitespaceString()
    {
        $this->setExpectedException('InvalidArgumentException');
        Braintree_Transaction::find('\t');
    }

    public function testInitializationWithoutArguments()
    {
        $transaction = Braintree_Transaction::factory(array());

        $this->assertTrue($transaction instanceof Braintree_Transaction);
    }
}
