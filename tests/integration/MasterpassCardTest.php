<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Braintree\CreditCardNumbers\CardTypeIndicators;
use Test\Setup;
use Braintree;

class MasterpassCardTest extends Setup
{
    public function testCreateWithMasterpassCardNonce()
    {
        $customer = Braintree\Customer::createNoValidate();
        $result = Braintree\PaymentMethod::create([
            'customerId' => $customer->id,
            'paymentMethodNonce' => Braintree\Test\Nonces::$masterpassDiscover,
        ]);

        $this->assertTrue($result->success);
        $masterpassCard = $result->paymentMethod;
        $this->assertNotNull($masterpassCard->token);
        $this->assertSame(Braintree\CreditCard::DISCOVER, $masterpassCard->cardType);
        $this->assertTrue($masterpassCard->default);
        $this->assertContains('discover', $masterpassCard->imageUrl);
        $this->assertTrue(intval($masterpassCard->expirationMonth) > 0);
        $this->assertTrue(intval($masterpassCard->expirationYear) > 0);
        $this->assertSame($customer->id, $masterpassCard->customerId);
        $this->assertObjectNotHasAttribute('venmoSdk', $masterpassCard);
        $this->assertNotNull($masterpassCard->healthcare);
        $this->assertNotNull($masterpassCard->uniqueNumberIdentifier);
        $this->assertNotNull($masterpassCard->issuingBank);
        $this->assertSame($masterpassCard->last4, '1117');
        $this->assertSame($masterpassCard->maskedNumber, '601111******1117');
    }

    public function testTransactionSearchWithMasterpass()
    {
        $transaction = Braintree\Transaction::saleNoValidate([
            'amount' => Braintree\Test\TransactionAmounts::$authorize,
            'paymentMethodNonce' => Braintree\Test\Nonces::$masterpassDiscover,
        ]);

        $collection = Braintree\Transaction::search([
            Braintree\TransactionSearch::id()->is($transaction->id),
            Braintree\TransactionSearch::paymentInstrumentType()->is(Braintree\PaymentInstrumentType::MASTERPASS_CARD)
        ]);


        $this->assertEquals($transaction->paymentInstrumentType, Braintree\PaymentInstrumentType::MASTERPASS_CARD);
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    public function testCreateCustomerwithMasterpassCard()
    {
        $nonce = Braintree\Test\Nonces::$masterpassDiscover;
        $result = Braintree\Customer::create([
            'paymentMethodNonce' => $nonce
        ]);
        $this->assertTrue($result->success);
        $customer = $result->customer;
        $this->assertNotNull($customer->masterpassCards[0]);
        $this->assertNotNull($customer->paymentMethods[0]);
    }

    public function testCreateTransactionWithMasterpassNonceAndVault()
    {
        $result = Braintree\Transaction::sale([
            'amount' => '47.00',
            'paymentMethodNonce' => Braintree\Test\Nonces::$masterpassAmEx,
            'options' => [
                'storeInVault' => true
            ]
        ]);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals('47.00', $transaction->amount);
        $masterpassDetails = $transaction->masterpassCardDetails;
        $this->assertSame(Braintree\CreditCard::AMEX, $masterpassDetails->cardType);

        $this->assertNotNull($masterpassDetails->bin);
        $this->assertNotNull($masterpassDetails->cardType);
        $this->assertNotNull($masterpassDetails->cardholderName);
        $this->assertNotNull($masterpassDetails->commercial);
        $this->assertNotNull($masterpassDetails->countryOfIssuance);
        $this->assertNotNull($masterpassDetails->customerLocation);
        $this->assertNotNull($masterpassDetails->debit);
        $this->assertNotNull($masterpassDetails->expirationDate);
        $this->assertNotNull($masterpassDetails->expirationMonth);
        $this->assertNotNull($masterpassDetails->expirationYear);
        $this->assertNotNull($masterpassDetails->healthcare);
        $this->assertNotNull($masterpassDetails->imageUrl);
        $this->assertNotNull($masterpassDetails->issuingBank);
        $this->assertNotNull($masterpassDetails->last4);
        $this->assertNotNull($masterpassDetails->maskedNumber);
        $this->assertNotNull($masterpassDetails->payroll);
        $this->assertNotNull($masterpassDetails->productId);
        $this->assertNotNull($masterpassDetails->token);
    }
}
