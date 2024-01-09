<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class CreditCardVerificationTest extends Setup
{
    public function testCreateSignature()
    {
        $expected = [
            ['creditCard' =>
                [
                    ['billingAddress' => Braintree\CreditCardGateway::billingAddressSignature()],
                    'cardholderName',
                    'cvv',
                    'expirationDate',
                    'expirationMonth',
                    'expirationYear',
                    'number'
                ]
            ],
            ['externalVault' =>
                [
                    'previousNetworkTransactionId',
                    'status'
                ]
            ],
            'intendedTransactionSource',
            ['options' =>
                [
                    'accountType',
                    'amount',
                    'merchantAccountId'
                ]
            ],
            'paymentMethodNonce',
            ['riskData' =>
                [
                    'customerBrowser',
                    'customerIp'
                ]
            ],
            'threeDSecureAuthenticationId',
            ['threeDSecurePassThru' =>
                [
                    'authenticationResponse',
                    'cavv',
                    'cavvAlgorithm',
                    'directoryResponse',
                    'dsTransactionId',
                    'eciFlag',
                    'threeDSecureVersion',
                    'xid'
                ]
            ]
        ];

        $this->assertEquals($expected, Braintree\CreditCardVerification::createSignature());
    }

    public function test_createWithInvalidArguments()
    {
        $this->expectException('InvalidArgumentException', 'invalid keys: invalidProperty');
        Braintree\CreditCardVerification::create(['options' => ['amount' => '123.45'], 'invalidProperty' => 'foo']);
    }

    // NEXT_MAJOR_VERSION Remove this test. The old venmo SDK class has been deprecated
    public function test_createWithPaymentMethodArguments()
    {
        $this->expectException('InvalidArgumentException', 'invalid keys: creditCard[venmoSdkPaymentMethodCode]');
        Braintree\CreditCardVerification::create(['options' => ['amount' => '123.45'], 'creditCard' => ['venmoSdkPaymentMethodCode' => 'foo']]);
    }
}
