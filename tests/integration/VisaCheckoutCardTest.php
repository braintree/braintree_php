<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Braintree\CreditCardNumbers\CardTypeIndicators;
use Test\Setup;
use Braintree;

class VisaCheckoutCardTest extends Setup
{
    public function testCreateWithVisaCheckoutCardNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$visaCheckoutDiscover,
        ]);

        $this->assertTrue($result->success);
        $visaCheckoutCard = $result->paymentMethod;
        $this->assertNotNull($visaCheckoutCard->token);
        $this->assertSame(Braintree\CreditCard::DISCOVER, $visaCheckoutCard->cardType);
        $this->assertTrue($visaCheckoutCard->default);
        $this->assertContains('discover', $visaCheckoutCard->imageUrl);
        $this->assertTrue(intval($visaCheckoutCard->expirationMonth) > 0);
        $this->assertTrue(intval($visaCheckoutCard->expirationYear) > 0);
        $this->assertSame($customer->id, $visaCheckoutCard->customerId);
        $this->assertSame('abc123', $visaCheckoutCard->callId);
        $this->assertObjectNotHasAttribute('venmoSdk', $visaCheckoutCard);
        $this->assertNotNull($visaCheckoutCard->healthcare);
        $this->assertNotNull($visaCheckoutCard->uniqueNumberIdentifier);
        $this->assertNotNull($visaCheckoutCard->issuingBank);
        $this->assertSame($visaCheckoutCard->last4, '1117');
        $this->assertSame($visaCheckoutCard->maskedNumber, '601111******1117');
    }

    public function testCreateWithVisaCheckoutCardNonceWithVerification()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$visaCheckoutDiscover,
            'options' => [
                'verifyCard' => true
            ]
        ]);

        $this->assertTrue($result->success);
        $visaCheckoutCard = $result->paymentMethod;
        $verification = $visaCheckoutCard->verification;

        $this->assertNotNull($verification);
        $this->assertNotNull($verification->status);
    }

    public function testTransactionSearchWithVisaCheckout()
    {
        $transaction = Braintree\Transaction::saleNoValidate([
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'paymentMethodNonce' => Braintree\Test\Nonces::$visaCheckoutDiscover,
        ]);

        $collection = Braintree\Transaction::search([
            Braintree\TransactionSearch::id()->is($transaction->id),
            Braintree\TransactionSearch::paymentInstrumentType()->is(Braintree\PaymentInstrumentType::VISA_CHECKOUT_CARD)
        ]);


        $this->assertEquals($transaction->paymentInstrumentType, Braintree\PaymentInstrumentType::VISA_CHECKOUT_CARD);
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    public function testCreateCustomerWithVisaCheckoutCard()
    {
        $nonce = Braintree\Test\Nonces::$visaCheckoutDiscover;
        $result = Braintree\Customer::create([
            'paymentMethodNonce' => $nonce
        ]);
        $this->assertTrue($result->success);
        $customer = $result->customer;
        $this->assertNotNull($customer->visaCheckoutCards[0]);
        $this->assertNotNull($customer->paymentMethods[0]);
    }

    public function testCreateTransactionWithVisaCheckoutNonceAndVault()
    {
        $result = Braintree\Transaction::sale([
            'amount' => '47.00',
            'paymentMethodNonce' => Braintree\Test\Nonces::$visaCheckoutAmEx,
            'options' => [
                'storeInVault' => true
            ]
        ]);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('47.00', $transaction->amount);
        $visaCheckoutDetails = $transaction->visaCheckoutCardDetails;
        $this->assertSame(Braintree\CreditCard::AMEX, $visaCheckoutDetails->cardType);

        $this->assertNotNull($visaCheckoutDetails->bin);
        $this->assertNotNull($visaCheckoutDetails->cardType);
        $this->assertNotNull($visaCheckoutDetails->cardholderName);
        $this->assertNotNull($visaCheckoutDetails->commercial);
        $this->assertNotNull($visaCheckoutDetails->countryOfIssuance);
        $this->assertNotNull($visaCheckoutDetails->customerLocation);
        $this->assertNotNull($visaCheckoutDetails->debit);
        $this->assertNotNull($visaCheckoutDetails->expirationDate);
        $this->assertNotNull($visaCheckoutDetails->expirationMonth);
        $this->assertNotNull($visaCheckoutDetails->expirationYear);
        $this->assertNotNull($visaCheckoutDetails->healthcare);
        $this->assertNotNull($visaCheckoutDetails->imageUrl);
        $this->assertNotNull($visaCheckoutDetails->issuingBank);
        $this->assertNotNull($visaCheckoutDetails->last4);
        $this->assertNotNull($visaCheckoutDetails->maskedNumber);
        $this->assertNotNull($visaCheckoutDetails->payroll);
        $this->assertNotNull($visaCheckoutDetails->productId);
        $this->assertNotNull($visaCheckoutDetails->token);
    }
}
