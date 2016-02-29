<?php
namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class TransactionTest extends Setup
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $t = Braintree\Transaction::factory([
            'creditCard' => ['expirationMonth' => '05', 'expirationYear' => '2010', 'bin' => '510510', 'last4' => '5100'],
            'customer' => [],
            'billing' => [],
            'descriptor' => [],
            'shipping' => [],
            'subscription' => ['billingPeriodStartDate' => '1983-07-12'],
            'statusHistory' => []
        ]);
        $this->setExpectedException('PHPUnit_Framework_Error', 'Undefined property on Braintree\Transaction: foo');
        $t->foo;
    }

	public function testCloneTransaction_RaisesErrorOnInvalidProperty()
	{
        $this->setExpectedException('InvalidArgumentException');
		Braintree\Transaction::cloneTransaction('an id', ['amount' => '123.45', 'invalidProperty' => 'foo']);
	}

	public function testErrorsWhenFindWithBlankString()
	{
        $this->setExpectedException('InvalidArgumentException');
        Braintree\Transaction::find('');
	}

	public function testErrorsWhenFindWithWhitespaceString()
	{
        $this->setExpectedException('InvalidArgumentException');
        Braintree\Transaction::find('\t');
	}

    public function testInitializationWithoutArguments()
    {
        $transaction = Braintree\Transaction::factory([]);

        $this->assertTrue($transaction instanceof Braintree\Transaction);
    }
}
