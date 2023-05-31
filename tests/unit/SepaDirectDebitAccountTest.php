<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class SepaDirectDebitAccountTest extends Setup
{
    public function testGet_givesErrorIfInvalidProperty()
    {
        $this->expectError();
        $sepaDirectDebitAccount = Braintree\SepaDirectDebitAccount::factory([]);
        $sepaDirectDebitAccount->foo;
    }

    public function testIsDefault()
    {
        $sepaDirectDebitAccount = Braintree\SepaDirectDebitAccount::factory(['default' => true]);
        $this->assertTrue($sepaDirectDebitAccount->isDefault());

        $sepaDirectDebitAccount = Braintree\SepaDirectDebitAccount::factory(['default' => false]);
        $this->assertFalse($sepaDirectDebitAccount->isDefault());
    }

    public function testErrorsOnFindWithBlankArgument()
    {
        $this->expectException('InvalidArgumentException');
        Braintree\SepaDirectDebitAccount::find('');
    }

    public function testSubscriptions()
    {
        $sepaDirectDebitAccount = Braintree\SepaDirectDebitAccount::factory([
            'subscriptions' => [
                [
                    'id' => '120',
                    'price' => '10.00'
                ],
                [
                    'id' => '121',
                    'price' => '12.00'
                ],
            ]
        ]);

        $this->assertEquals(2, count($sepaDirectDebitAccount->subscriptions));

        $subscription1 = $sepaDirectDebitAccount->subscriptions[0];
        $this->assertEquals('120', $subscription1->id);
        $this->assertEquals('10.00', $subscription1->price);

        $subscription2 = $sepaDirectDebitAccount->subscriptions[1];
        $this->assertEquals('121', $subscription2->id);
        $this->assertEquals('12.00', $subscription2->price);
    }
}
