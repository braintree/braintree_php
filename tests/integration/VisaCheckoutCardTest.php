<?php

namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Braintree\CreditCardNumbers\CardTypeIndicators;
use Test\Setup;
use Braintree;

class VisaCheckoutCardTest extends Setup
{
    public function testSearchByPaymentInstrumentType()
    {
        $collection = Braintree\Transaction::search([
            Braintree\TransactionSearch::paymentInstrumentType()->is(Braintree\PaymentInstrumentType::VISA_CHECKOUT_CARD)
        ]);

        $this->assertNotNull($collection);
    }
}
