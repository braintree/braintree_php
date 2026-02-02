<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

# DEPRECATED: Visa Checkout is no longer supprted for creating new transactions

class VisaCheckoutCardTest extends Setup
{
    public function testBinData()
    {
        $card = Braintree\VisaCheckoutCard::factory(
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
