<?php namespace Braintree\Tests\Integration;

use Braintree\CreditCard;
use Braintree\CreditCardVerification;
use Braintree\CreditCardVerificationSearch;
use Braintree\Customer;
use Braintree\Gateway;
use Braintree\Test\CreditCardNumbers;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class CreditCardVerificationAdvancedSearchTest extends \PHPUnit_Framework_TestCase
{
    function test_searchOnTextFields()
    {
        $searchCriteria = array(
            'creditCardCardholderName'        => 'Tim Toole',
            'creditCardExpirationDate'        => '05/2010',
            'creditCardNumber'                => CreditCardNumbers::$failsSandboxVerification['Visa'],
            'billingAddressDetailsPostalCode' => '90210',
        );
        $result = Customer::create(array(
            'creditCard' => array(
                'cardholderName' => $searchCriteria['creditCardCardholderName'],
                'number'         => $searchCriteria['creditCardNumber'],
                'expirationDate' => $searchCriteria['creditCardExpirationDate'],
                'billingAddress' => array(
                    'postalCode' => $searchCriteria['billingAddressDetailsPostalCode']
                ),
                'options'        => array('verifyCard' => true),
            ),
        ));
        $verification = $result->creditCardVerification;

        $query = array(CreditCardVerificationSearch::id()->is($verification->id));
        foreach ($searchCriteria AS $criterion => $value) {
            $query[] = CreditCardVerificationSearch::$criterion()->is($value);
        }

        $collection = CreditCardVerification::search($query);
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($result->creditCardVerification->id, $collection->firstItem()->id);

        foreach ($searchCriteria AS $criterion => $value) {
            $collection = CreditCardVerification::search(array(
                CreditCardVerificationSearch::$criterion()->is($value),
                CreditCardVerificationSearch::id()->is($result->creditCardVerification->id)
            ));
            $this->assertEquals(1, $collection->maximumCount());
            $this->assertEquals($result->creditCardVerification->id, $collection->firstItem()->id);

            $collection = CreditCardVerification::search(array(
                CreditCardVerificationSearch::$criterion()->is('invalid_attribute'),
                CreditCardVerificationSearch::id()->is($result->creditCardVerification->id)
            ));
            $this->assertEquals(0, $collection->maximumCount());
        }
    }

    function test_searchOnSuccessfulCustomerAndPaymentFields()
    {
        $customerId = uniqid();
        $searchCriteria = array(
            'customerId'         => $customerId,
            'customerEmail'      => $customerId . 'sandworm@example.com',
            'paymentMethodToken' => $customerId . 'token',
        );
        $result = Customer::create(array(
            'id'         => $customerId,
            'email'      => $searchCriteria['customerEmail'],
            'creditCard' => array(
                'token'          => $searchCriteria['paymentMethodToken'],
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2017',
                'options'        => array('verifyCard' => true)
            )
        ));
        $customer = $result->customer;

        $query = array();
        foreach ($searchCriteria AS $criterion => $value) {
            $query[] = CreditCardVerificationSearch::$criterion()->is($value);
        }

        $collection = CreditCardVerification::search($query);
        $this->assertEquals(1, $collection->maximumCount());

        foreach ($searchCriteria AS $criterion => $value) {
            $collection = CreditCardVerification::search(array(
                CreditCardVerificationSearch::$criterion()->is($value),
            ));
            $this->assertEquals(1, $collection->maximumCount());

            $collection = CreditCardVerification::search(array(
                CreditCardVerificationSearch::$criterion()->is('invalid_attribute'),
            ));
            $this->assertEquals(0, $collection->maximumCount());
        }
    }

    function testGateway_searchEmpty()
    {
        $query = array();
        $query[] = CreditCardVerificationSearch::creditCardCardholderName()->is('Not Found');

        $gateway = new Gateway(array(
            'environment' => 'development',
            'merchantId'  => 'integration_merchant_id',
            'publicKey'   => 'integration_public_key',
            'privateKey'  => 'integration_private_key'
        ));
        $collection = $gateway->creditCardVerification()->search($query);

        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_createdAt()
    {
        $result = Customer::create(array(
            'creditCard' => array(
                'cardholderName' => 'Joe Smith',
                'number'         => '4000111111111115',
                'expirationDate' => '12/2016',
                'options'        => array('verifyCard' => true),
            ),
        ));

        $verification = $result->creditCardVerification;

        $past = clone $verification->createdAt;
        $past->modify('-1 hour');
        $future = clone $verification->createdAt;
        $future->modify('+1 hour');

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::id()->is($verification->id),
            CreditCardVerificationSearch::createdAt()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::id()->is($verification->id),
            CreditCardVerificationSearch::createdAt()->lessThanOrEqualTo($future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::id()->is($verification->id),
            CreditCardVerificationSearch::createdAt()->greaterThanOrEqualTo($past)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($verification->id, $collection->firstItem()->id);
    }

    function test_multipleValueNode_ids()
    {
        $result = Customer::create(array(
            'creditCard' => array(
                'cardholderName' => 'Joe Smith',
                'number'         => '4000111111111115',
                'expirationDate' => '12/2016',
                'options'        => array('verifyCard' => true),
            ),
        ));

        $creditCardVerification = $result->creditCardVerification;

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::ids()->is($creditCardVerification->id)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::ids()->in(
                array($creditCardVerification->id, '1234')
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::ids()->is('1234')
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_creditCardType()
    {
        $result = Customer::create(array(
            'creditCard' => array(
                'cardholderName' => 'Joe Smith',
                'number'         => '4000111111111115',
                'expirationDate' => '12/2016',
                'options'        => array('verifyCard' => true),
            ),
        ));

        $creditCardVerification = $result->creditCardVerification;

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            CreditCardVerificationSearch::creditCardCardType()->is($creditCardVerification->creditCard['cardType'])
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            CreditCardVerificationSearch::creditCardCardType()->in(
                array($creditCardVerification->creditCard['cardType'], CreditCard::CHINA_UNION_PAY)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            CreditCardVerificationSearch::creditCardCardType()->is(CreditCard::CHINA_UNION_PAY)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_status()
    {
        $result = Customer::create(array(
            'creditCard' => array(
                'cardholderName' => 'Joe Smith',
                'number'         => '4000111111111115',
                'expirationDate' => '12/2016',
                'options'        => array('verifyCard' => true),
            ),
        ));

        $creditCardVerification = $result->creditCardVerification;

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            CreditCardVerificationSearch::status()->is($creditCardVerification->status)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            CreditCardVerificationSearch::status()->in(
                array($creditCardVerification->status, Result_creditCardVerification::VERIFIED)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($creditCardVerification->id, $collection->firstItem()->id);

        $collection = CreditCardVerification::search(array(
            CreditCardVerificationSearch::id()->is($creditCardVerification->id),
            CreditCardVerificationSearch::status()->is(Result_creditCardVerification::VERIFIED)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }
}
