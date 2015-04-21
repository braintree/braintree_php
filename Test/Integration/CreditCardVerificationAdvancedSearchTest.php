<?php

namespace Test\Integration;

require_once dirname(__DIR__).'/Setup.php';

use Test\Setup;
use Braintree;

class CreditCardVerificationAdvancedSearchTest extends Setup
{
    public function test_searchOnTextFields()
    {
        $search_criteria = array(
            'creditCardCardholderName' => 'Tim Toole',
            'creditCardExpirationDate' => '05/2010',
            'creditCardNumber' => '4000111111111115',
        );

        $result = Braintree\Customer::create(array(
            'creditCard' => array(
                'cardholderName' => $search_criteria['creditCardCardholderName'],
                'number' => $search_criteria['creditCardNumber'],
                'expirationDate' => $search_criteria['creditCardExpirationDate'],
                'options' => array('verifyCard' => true),
            ),
        ));

        $verification = $result->creditCardVerification;
        $query = array(Braintree\CreditCardVerificationSearch::id()->is($verification->id));
        foreach ($search_criteria as $criterion => $value) {
            $query[] = Braintree\CreditCardVerificationSearch::$criterion()->is($value);
        }

        $collection = Braintree\CreditCardVerification::search($query);

        $this->assertEquals(1, $collection->maximumCount());

        $this->assertEquals($result->creditCardVerification->id, $collection->firstItem()->id);

        foreach ($search_criteria as $criterion => $value) {
            $collection = Braintree\CreditCardVerification::search(array(
                Braintree\CreditCardVerificationSearch::$criterion()->is($value),
                Braintree\CreditCardVerificationSearch::id()->is($result->creditCardVerification->id),
            ));
            $this->assertEquals(1, $collection->maximumCount());
            $this->assertEquals($result->creditCardVerification->id, $collection->firstItem()->id);

            $collection = Braintree\CreditCardVerification::search(array(
                Braintree\CreditCardVerificationSearch::$criterion()->is('invalid_attribute'),
                Braintree\CreditCardVerificationSearch::id()->is($result->creditCardVerification->id),
            ));
            $this->assertEquals(0, $collection->maximumCount());
        }
    }

    public function testGateway_searchEmpty()
    {
        $query = array();
        $query[] = Braintree\CreditCardVerificationSearch::creditCardCardholderName()->is('Not Found');

        $gateway = new Braintree\Gateway(array(
            'environment' => 'development',
            'merchantId' => 'integration_merchant_id',
            'publicKey' => 'integration_public_key',
            'privateKey' => 'integration_private_key',
        ));
        $collection = $gateway->creditCardVerification()->search($query);

        $this->assertEquals(0, $collection->maximumCount());
    }

    public function test_createdAt()
    {
        $result = Braintree\Customer::create(array(
            'creditCard' => array(
                'cardholderName' => 'Joe Smith',
                'number' => '4000111111111115',
                'expirationDate' => '12/2016',
                'options' => array('verifyCard' => true),
            ),
        ));

        $verification = $result->creditCardVerification;

        $past = clone $verification->createdAt;
        $past->modify('-1 hour');
        $future = clone $verification->createdAt;
        $future->modify('+1 hour');

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($verification->id),
            Braintree\CreditCardVerificationSearch::createdAt()->between($past, $future),
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($verification->id),
            Braintree\CreditCardVerificationSearch::createdAt()->lessThanOrEqualTo($future),
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($verification->id),
            Braintree\CreditCardVerificationSearch::createdAt()->greaterThanOrEqualTo($past),
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);
    }

    public function test_multipleValueNode_creditCardType()
    {
        $result = Braintree\Customer::create(array(
            'creditCard' => array(
                'cardholderName' => 'Joe Smith',
                'number' => '4000111111111115',
                'expirationDate' => '12/2016',
                'options' => array('verifyCard' => true),
            ),
        ));

        $creditCardVerification = $result->creditCardVerification;

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            Braintree\CreditCardVerificationSearch::creditCardCardType()->is($creditCardVerification->creditCard['cardType']),
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            Braintree\CreditCardVerificationSearch::creditCardCardType()->in(
                array($creditCardVerification->creditCard['cardType'], Braintree\CreditCard::CHINA_UNION_PAY)
            ),
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            Braintree\CreditCardVerificationSearch::creditCardCardType()->is(Braintree\CreditCard::CHINA_UNION_PAY),
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }
}
