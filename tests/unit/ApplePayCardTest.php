<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class ApplePayCardTest extends Setup
{
    public function testBinData()
    {
        $card = Braintree\ApplePayCard::factory(
            [
                'business' => 'No',
                'consumer' => 'Yes',
                'corporate' => 'No',
                'purchase' => 'Yes'
            ]
        );
        $this->assertEquals(Braintree\CreditCard::BUSINESS_NO, $card->business);
        $this->assertEquals(Braintree\CreditCard::CONSUMER_YES, $card->consumer);
        $this->assertEquals(Braintree\CreditCard::CORPORATE_NO, $card->corporate);
        $this->assertEquals(Braintree\CreditCard::PURCHASE_YES, $card->purchase);
    }
}
