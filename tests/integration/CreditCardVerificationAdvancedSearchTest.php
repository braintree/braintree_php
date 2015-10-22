<?php
namespace Test\Integration;

require_once dirname(__DIR__) . '/Setup.php';

use Test;
use Test\Setup;
use Braintree;

class CreditCardVerificationAdvancedSearchTest extends Setup
{
    public function test_searchOnTextFields()
    {
        $searchCriteria = array(
            'creditCardCardholderName' => 'Tim Toole',
            'creditCardExpirationDate' => '05/2010',
            'creditCardNumber' => Braintree\Test\CreditCardNumbers::$failsSandboxVerification['Visa'],
            'billingAddressDetailsPostalCode' => '90210',
        );
        $result = Braintree\Customer::create(array(
            'creditCard' => array(
                'cardholderName' => $searchCriteria['creditCardCardholderName'],
                'number' => $searchCriteria['creditCardNumber'],
                'expirationDate' => $searchCriteria['creditCardExpirationDate'],
                'billingAddress' => array(
                    'postalCode' => $searchCriteria['billingAddressDetailsPostalCode']
                ),
                'options' => array('verifyCard' => true),
            ),
        ));
        $verification = $result->creditCardVerification;

        $query = array(Braintree\CreditCardVerificationSearch::id()->is($verification->id));
        foreach ($searchCriteria AS $criterion => $value) {
            $query[] = Braintree\CreditCardVerificationSearch::$criterion()->is($value);
        }

        $collection = Braintree\CreditCardVerification::search($query);
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($result->creditCardVerification->id, $collection->firstItem()->id);

        foreach ($searchCriteria AS $criterion => $value) {
            $collection = Braintree\CreditCardVerification::search(array(
                Braintree\CreditCardVerificationSearch::$criterion()->is($value),
                Braintree\CreditCardVerificationSearch::id()->is($result->creditCardVerification->id)
            ));
            $this->assertEquals(1, $collection->maximumCount());
            $this->assertEquals($result->creditCardVerification->id, $collection->firstItem()->id);

            $collection = Braintree\CreditCardVerification::search(array(
                Braintree\CreditCardVerificationSearch::$criterion()->is('invalid_attribute'),
                Braintree\CreditCardVerificationSearch::id()->is($result->creditCardVerification->id)
            ));
            $this->assertEquals(0, $collection->maximumCount());
        }
    }

    public function test_searchOnSuccessfulCustomerAndPaymentFields()
    {
        $customerId = uniqid();
        $searchCriteria = array(
            'customerId' => $customerId,
            'customerEmail' => $customerId . 'sandworm@example.com',
            'paymentMethodToken' => $customerId . 'token',
        );
        $result = Braintree\Customer::create(array(
            'id' => $customerId,
            'email' => $searchCriteria['customerEmail'],
            'creditCard' => array(
                'token' => $searchCriteria['paymentMethodToken'],
                'number' => Braintree\Test\CreditCardNumbers::$visa,
                'expirationDate' => '05/2017',
                'options' => array('verifyCard' => true)
            )
        ));
        $customer = $result->customer;

        $query = array();
        foreach ($searchCriteria AS $criterion => $value) {
            $query[] = Braintree\CreditCardVerificationSearch::$criterion()->is($value);
        }

        $collection = Braintree\CreditCardVerification::search($query);
        $this->assertEquals(1, $collection->maximumCount());

        foreach ($searchCriteria AS $criterion => $value) {
            $collection = Braintree\CreditCardVerification::search(array(
                Braintree\CreditCardVerificationSearch::$criterion()->is($value),
            ));
            $this->assertEquals(1, $collection->maximumCount());

            $collection = Braintree\CreditCardVerification::search(array(
                Braintree\CreditCardVerificationSearch::$criterion()->is('invalid_attribute'),
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
            'privateKey' => 'integration_private_key'
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
            Braintree\CreditCardVerificationSearch::createdAt()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($verification->id),
            Braintree\CreditCardVerificationSearch::createdAt()->lessThanOrEqualTo($future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($verification->id),
            Braintree\CreditCardVerificationSearch::createdAt()->greaterThanOrEqualTo($past)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);
    }

    public function test_multipleValueNode_ids()
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
            Braintree\CreditCardVerificationSearch::ids()->is($creditCardVerification->id)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::ids()->in(
                array($creditCardVerification->id,'1234')
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::ids()->is('1234')
        ));
        $this->assertEquals(0, $collection->maximumCount());
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
            Braintree\CreditCardVerificationSearch::creditCardCardType()->is($creditCardVerification->creditCard['cardType'])
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            Braintree\CreditCardVerificationSearch::creditCardCardType()->in(
                array($creditCardVerification->creditCard['cardType'], Braintree\CreditCard::CHINA_UNION_PAY)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            Braintree\CreditCardVerificationSearch::creditCardCardType()->is(Braintree\CreditCard::CHINA_UNION_PAY)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    public function test_multipleValueNode_status()
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
            Braintree\CreditCardVerificationSearch::status()->is($creditCardVerification->status)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            Braintree\CreditCardVerificationSearch::status()->in(
                array($creditCardVerification->status, Braintree\Result\CreditCardVerification::VERIFIED)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = Braintree\CreditCardVerification::search(array(
            Braintree\CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            Braintree\CreditCardVerificationSearch::status()->is(Braintree\Result\CreditCardVerification::VERIFIED)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }
}
