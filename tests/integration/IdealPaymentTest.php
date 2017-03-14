<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class IdealPaymentTest extends Setup
{

    public function testSale_createsASaleUsingNonce()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);
        $nonce = Test\Helper::generateValidIdealPaymentNonce();

        $result = Braintree\IdealPayment::sale($nonce, [
            'merchantAccountId' => 'ideal_merchant_account',
            'amount' => '100.00',
            'orderId' => 'ABC123'
        ]);

        $this->assertTrue($result->success);
        $transaction = $result->transaction;
        $this->assertEquals(Braintree\Transaction::SETTLED, $transaction->status);
        $this->assertEquals(Braintree\Transaction::SALE, $transaction->type);
        $this->assertEquals('100.00', $transaction->amount);
        $this->assertRegExp('/^idealpayment_\w{6,}$/', $transaction->idealPayment->idealPaymentId);
        $this->assertRegExp('/^\d{16,}$/', $transaction->idealPayment->idealTransactionId);
        $this->assertRegExp('/^https:\/\//', $transaction->idealPayment->imageUrl);
        $this->assertNotNull($transaction->idealPayment->maskedIban);
        $this->assertNotNull($transaction->idealPayment->bic);
    }

    public function testSale_createsASaleUsingInvalidNonce()
    {
        $http = new HttpClientApi(Braintree\Configuration::$global);

        $result = Braintree\IdealPayment::sale('invalid_nonce', [
            'merchantAccountId' => 'ideal_merchant_account',
            'amount' => '100.00',
            'orderId' => 'ABC123'
        ]);

        $this->assertFalse($result->success);
        $baseErrors = $result->errors->forKey('transaction')->onAttribute('paymentMethodNonce');
        $this->assertEquals(Braintree\Error\Codes::TRANSACTION_PAYMENT_METHOD_NONCE_UNKNOWN, $baseErrors[0]->code);
    }
}
