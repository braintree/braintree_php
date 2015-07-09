<?php namespace Braintree\Tests\Integration;

use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Http;
use Braintree\Test\CreditCardNumbers;
use Braintree\Test\TransactionAmounts;
use Braintree\Transaction;
use Braintree\TransactionSearch;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/HttpClientApi.php';

class TransactionAdvancedSearchTest extends \PHPUnit_Framework_TestCase
{
    function testNoMatches()
    {
        $collection = Transaction::search(array(
            TransactionSearch::billingFirstName()->is('thisnameisnotreal')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_noRequestsWhenIterating()
    {
        $resultsReturned = false;
        $collection = Transaction::search(array(
            TransactionSearch::billingFirstName()->is('thisnameisnotreal')
        ));

        foreach ($collection as $transaction) {
            $resultsReturned = true;
            break;
        }

        $this->assertSame(0, $collection->maximumCount());
        $this->assertEquals(false, $resultsReturned);
    }

    function testSearchOnTextFields()
    {
        $firstName = 'Tim' . rand();
        $token = 'creditcard' . rand();
        $customerId = 'customer' . rand();

        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'Tom Smith',
                'token'          => $token,
            ),
            'billing'    => array(
                'company'         => 'Braintree',
                'countryName'     => 'United States of America',
                'extendedAddress' => 'Suite 123',
                'firstName'       => $firstName,
                'lastName'        => 'Smith',
                'locality'        => 'Chicago',
                'postalCode'      => '12345',
                'region'          => 'IL',
                'streetAddress'   => '123 Main St'
            ),
            'customer'   => array(
                'company'   => 'Braintree',
                'email'     => 'smith@example.com',
                'fax'       => '5551231234',
                'firstName' => 'Tom',
                'id'        => $customerId,
                'lastName'  => 'Smith',
                'phone'     => '5551231234',
                'website'   => 'http://example.com',
            ),
            'options'    => array(
                'storeInVault' => true
            ),
            'orderId'    => 'myorder',
            'shipping'   => array(
                'company'         => 'Braintree P.S.',
                'countryName'     => 'Mexico',
                'extendedAddress' => 'Apt 456',
                'firstName'       => 'Thomas',
                'lastName'        => 'Smithy',
                'locality'        => 'Braintree',
                'postalCode'      => '54321',
                'region'          => 'MA',
                'streetAddress'   => '456 Road'
            ),
        ));

        $search_criteria = array(
            'billingCompany'             => "Braintree",
            'billingCountryName'         => "United States of America",
            'billingExtendedAddress'     => "Suite 123",
            'billingFirstName'           => $firstName,
            'billingLastName'            => "Smith",
            'billingLocality'            => "Chicago",
            'billingPostalCode'          => "12345",
            'billingRegion'              => "IL",
            'billingStreetAddress'       => "123 Main St",
            'creditCardCardholderName'   => "Tom Smith",
            'creditCardExpirationDate'   => "05/2012",
            'creditCardNumber'           => CreditCardNumbers::$visa,
            'customerCompany'            => "Braintree",
            'customerEmail'              => "smith@example.com",
            'customerFax'                => "5551231234",
            'customerFirstName'          => "Tom",
            'customerId'                 => $customerId,
            'customerLastName'           => "Smith",
            'customerPhone'              => "5551231234",
            'customerWebsite'            => "http://example.com",
            'orderId'                    => "myorder",
            'paymentMethodToken'         => $token,
            'processorAuthorizationCode' => $transaction->processorAuthorizationCode,
            'shippingCompany'            => "Braintree P.S.",
            'shippingCountryName'        => "Mexico",
            'shippingExtendedAddress'    => "Apt 456",
            'shippingFirstName'          => "Thomas",
            'shippingLastName'           => "Smithy",
            'shippingLocality'           => "Braintree",
            'shippingPostalCode'         => "54321",
            'shippingRegion'             => "MA",
            'shippingStreetAddress'      => "456 Road"
        );

        $query = array(TransactionSearch::id()->is($transaction->id));
        foreach ($search_criteria AS $criterion => $value) {
            $query[] = TransactionSearch::$criterion()->is($value);
        }

        $collection = Transaction::search($query);

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        foreach ($search_criteria AS $criterion => $value) {
            $collection = Transaction::search(array(
                TransactionSearch::$criterion()->is($value),
                TransactionSearch::id()->is($transaction->id)
            ));
            $this->assertEquals(1, $collection->maximumCount());
            $this->assertEquals($transaction->id, $collection->firstItem()->id);

            $collection = Transaction::search(array(
                TransactionSearch::$criterion()->is('invalid_attribute'),
                TransactionSearch::id()->is($transaction->id)
            ));
            $this->assertEquals(0, $collection->maximumCount());
        }
    }

    function testIs()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardholderName()->is('tom smith')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardholderName()->is('somebody else')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function testIsNot()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardholderName()->isNot('somebody else')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardholderName()->isNot('tom smith')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function testEndsWith()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardholderName()->endsWith('m smith')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardholderName()->endsWith('tom s')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function testStartsWith()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardholderName()->startsWith('tom s')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardholderName()->startsWith('m smith')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function testContains()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardholderName()->contains('m sm')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardholderName()->contains('something else')
        ));

        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_createdUsing()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::createdUsing()->is(Transaction::FULL_INFORMATION)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::createdUsing()->in(
                array(Transaction::FULL_INFORMATION, Transaction::TOKEN)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::createdUsing()->in(array(Transaction::TOKEN))
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_createdUsing_allowedValues()
    {
        $this->setExpectedException('\InvalidArgumentException',
            'Invalid argument(s) for created_using: noSuchCreatedUsing');
        $collection = Transaction::search(array(
            TransactionSearch::createdUsing()->is('noSuchCreatedUsing')
        ));
    }

    function test_multipleValueNode_creditCardCustomerLocation()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCustomerLocation()->is(CreditCard::US)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCustomerLocation()->in(
                array(CreditCard::US, CreditCard::INTERNATIONAL)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCustomerLocation()->in(array(CreditCard::INTERNATIONAL))
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_creditCardCustomerLocation_allowedValues()
    {
        $this->setExpectedException('\InvalidArgumentException',
            'Invalid argument(s) for credit_card_customer_location: noSuchLocation');
        $collection = Transaction::search(array(
            TransactionSearch::creditCardCustomerLocation()->is('noSuchLocation')
        ));
    }

    function test_multipleValueNode_merchantAccountId()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::merchantAccountId()->is($transaction->merchantAccountId)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::merchantAccountId()->in(
                array($transaction->merchantAccountId, "bogus_merchant_account_id")
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::merchantAccountId()->is("bogus_merchant_account_id")
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_creditCardType()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardType()->is($transaction->creditCardDetails->cardType)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardType()->in(
                array($transaction->creditCardDetails->cardType, CreditCard::CHINA_UNION_PAY)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::creditCardCardType()->is(CreditCard::CHINA_UNION_PAY)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_creditCardType_allowedValues()
    {
        $this->setExpectedException('\InvalidArgumentException',
            'Invalid argument(s) for credit_card_card_type: noSuchCardType');
        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardType()->is('noSuchCardType')
        ));
    }

    function test_multipleValueNode_status()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::status()->is($transaction->status)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::status()->in(
                array($transaction->status, Transaction::SETTLED)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::status()->is(Transaction::SETTLED)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_status_authorizationExpired()
    {
        $collection = Transaction::search(array(
            TransactionSearch::status()->is(Transaction::AUTHORIZATION_EXPIRED)
        ));
        $this->assertGreaterThan(0, $collection->maximumCount());
        $this->assertEquals(Transaction::AUTHORIZATION_EXPIRED, $collection->firstItem()->status);
    }

    function test_multipleValueNode_status_allowedValues()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Invalid argument(s) for status: noSuchStatus');
        $collection = Transaction::search(array(
            TransactionSearch::status()->is('noSuchStatus')
        ));
    }

    function test_multipleValueNode_source()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::source()->is(Transaction::API)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::source()->in(
                array(Transaction::API, Transaction::RECURRING)
            )
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::source()->is(Transaction::RECURRING)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_multipleValueNode_source_allowedValues()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Invalid argument(s) for source: noSuchSource');
        $collection = Transaction::search(array(
            TransactionSearch::source()->is('noSuchSource')
        ));
    }

    function test_multipleValueNode_type()
    {
        $customer = Customer::createNoValidate();
        $creditCard = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Joe Everyman' . rand(),
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $sale = Transaction::saleNoValidate(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodToken' => $creditCard->token,
            'options'            => array('submitForSettlement' => true)
        ));
        $http = new Http(Configuration::$global);
        $path = Configuration::$global->merchantPath() . '/transactions/' . $sale->id . '/settle';
        $http->put($path);
        $refund = Transaction::refund($sale->id)->transaction;

        $credit = Transaction::creditNoValidate(array(
            'amount'             => '100.00',
            'paymentMethodToken' => $creditCard->token
        ));


        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            TransactionSearch::type()->is($sale->type)
        ));
        $this->assertEquals(1, $collection->maximumCount());


        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            TransactionSearch::type()->in(
                array($sale->type, $credit->type)
            )
        ));
        $this->assertEquals(3, $collection->maximumCount());


        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            TransactionSearch::type()->is($credit->type)
        ));
        $this->assertEquals(2, $collection->maximumCount());
    }

    function test_multipleValueNode_type_allowedValues()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Invalid argument(s) for type: noSuchType');
        $collection = Transaction::search(array(
            TransactionSearch::type()->is('noSuchType')
        ));
    }

    function test_multipleValueNode_type_withRefund()
    {
        $customer = Customer::createNoValidate();
        $creditCard = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Joe Everyman' . rand(),
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $sale = Transaction::saleNoValidate(array(
            'amount'             => TransactionAmounts::$authorize,
            'paymentMethodToken' => $creditCard->token,
            'options'            => array('submitForSettlement' => true)
        ));
        $http = new Http(Configuration::$global);
        $path = Configuration::$global->merchantPath() . '/transactions/' . $sale->id . '/settle';
        $http->put($path);
        $refund = Transaction::refund($sale->id)->transaction;

        $credit = Transaction::creditNoValidate(array(
            'amount'             => '100.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            TransactionSearch::type()->is($credit->type),
            TransactionSearch::refund()->is(true)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($refund->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            TransactionSearch::type()->is($credit->type),
            TransactionSearch::refund()->is(false)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($credit->id, $collection->firstItem()->id);
    }

    function test_rangeNode_amount()
    {
        $customer = Customer::createNoValidate();
        $creditCard = CreditCard::create(array(
            'customerId'     => $customer->id,
            'cardholderName' => 'Jane Everywoman' . rand(),
            'number'         => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $t_1000 = Transaction::saleNoValidate(array(
            'amount'             => '1000.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $t_1500 = Transaction::saleNoValidate(array(
            'amount'             => '1500.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $t_1800 = Transaction::saleNoValidate(array(
            'amount'             => '1800.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            TransactionSearch::amount()->greaterThanOrEqualTo('1700')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($t_1800->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            TransactionSearch::amount()->lessThanOrEqualTo('1250')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($t_1000->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            TransactionSearch::amount()->between('1100', '1600')
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($t_1500->id, $collection->firstItem()->id);
    }

    private function runDisbursementDateSearchTests($disbursementDateString, $comparison)
    {
        $knownDepositId = "deposittransaction";
        $now = new \DateTime($disbursementDateString);
        $past = clone $now;
        $past->modify("-1 hour");
        $future = clone $now;
        $future->modify("+1 hour");

        $collections = array(
            'future' => Transaction::search(array(
                TransactionSearch::id()->is($knownDepositId),
                $comparison($future)
            )),
            'now'    => Transaction::search(array(
                TransactionSearch::id()->is($knownDepositId),
                $comparison($now)
            )),
            'past'   => Transaction::search(array(
                TransactionSearch::id()->is($knownDepositId),
                $comparison($past)
            ))
        );
        return $collections;
    }

    function test_rangeNode_disbursementDate_lessThanOrEqualTo()
    {
        $compareLessThan = function ($time) {
            return TransactionSearch::disbursementDate()->lessThanOrEqualTo($time);
        };
        $collection = $this->runDisbursementDateSearchTests("2013-04-10", $compareLessThan);

        $this->assertEquals(0, $collection['past']->maximumCount());
        $this->assertEquals(1, $collection['now']->maximumCount());
        $this->assertEquals(1, $collection['future']->maximumCount());
    }

    function test_rangeNode_disbursementDate_GreaterThanOrEqualTo()
    {
        $comparison = function ($time) {
            return TransactionSearch::disbursementDate()->GreaterThanOrEqualTo($time);
        };
        $collection = $this->runDisbursementDateSearchTests("2013-04-11", $comparison);

        $this->assertEquals(1, $collection['past']->maximumCount());
        $this->assertEquals(0, $collection['now']->maximumCount());
        $this->assertEquals(0, $collection['future']->maximumCount());
    }

    function test_rangeNode_disbursementDate_between()
    {
        $knownId = "deposittransaction";

        $now = new \DateTime("2013-04-10");
        $past = clone $now;
        $past->modify("-1 day");
        $future = clone $now;
        $future->modify("+1 day");
        $future2 = clone $now;
        $future2->modify("+2 days");

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disbursementDate()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disbursementDate()->between($now, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disbursementDate()->between($past, $now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disbursementDate()->between($future, $future2)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_disbursementDate_is()
    {
        $knownId = "deposittransaction";

        $now = new \DateTime("2013-04-10");
        $past = clone $now;
        $past->modify("-1 day");
        $future = clone $now;
        $future->modify("+1 day");
        $future2 = clone $now;
        $future2->modify("+2 days");

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disbursementDate()->is($past)
        ));
        $this->assertEquals(0, $collection->maximumCount());

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disbursementDate()->is($now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disbursementDate()->is($future)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    private function rundisputeDateSearchTests($disputeDateString, $comparison)
    {
        $knowndisputedId = "disputedtransaction";
        $now = new \DateTime($disputeDateString);
        $past = clone $now;
        $past->modify("-1 hour");
        $future = clone $now;
        $future->modify("+1 hour");

        $collections = array(
            'future' => Transaction::search(array(
                TransactionSearch::id()->is($knowndisputedId),
                $comparison($future)
            )),
            'now'    => Transaction::search(array(
                TransactionSearch::id()->is($knowndisputedId),
                $comparison($now)
            )),
            'past'   => Transaction::search(array(
                TransactionSearch::id()->is($knowndisputedId),
                $comparison($past)
            ))
        );
        return $collections;
    }

    function test_rangeNode_disputeDate_lessThanOrEqualTo()
    {
        $compareLessThan = function ($time) {
            return TransactionSearch::disputeDate()->lessThanOrEqualTo($time);
        };
        $collection = $this->rundisputeDateSearchTests("2014-03-01", $compareLessThan);

        $this->assertEquals(0, $collection['past']->maximumCount());
        $this->assertEquals(1, $collection['now']->maximumCount());
        $this->assertEquals(1, $collection['future']->maximumCount());
    }

    function test_rangeNode_disputeDate_GreaterThanOrEqualTo()
    {
        $comparison = function ($time) {
            return TransactionSearch::disputeDate()->GreaterThanOrEqualTo($time);
        };
        $collection = $this->rundisputeDateSearchTests("2014-03-01", $comparison);

        $this->assertEquals(1, $collection['past']->maximumCount());
        $this->assertEquals(1, $collection['now']->maximumCount());
        $this->assertEquals(1, $collection['future']->maximumCount());
    }

    function test_rangeNode_disputeDate_between()
    {
        $knownId = "disputedtransaction";

        $now = new \DateTime("2014-03-01");
        $past = clone $now;
        $past->modify("-1 day");
        $future = clone $now;
        $future->modify("+1 day");
        $future2 = clone $now;
        $future2->modify("+2 days");

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disputeDate()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disputeDate()->between($now, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disputeDate()->between($past, $now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disputeDate()->between($future, $future2)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_disputeDate_is()
    {
        $knownId = "disputedtransaction";

        $now = new \DateTime("2014-03-01");
        $past = clone $now;
        $past->modify("-1 day");
        $future = clone $now;
        $future->modify("+1 day");
        $future2 = clone $now;
        $future2->modify("+2 days");

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disputeDate()->is($past)
        ));
        $this->assertEquals(0, $collection->maximumCount());

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disputeDate()->is($now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($knownId, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($knownId),
            TransactionSearch::disputeDate()->is($future)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_createdAt_lessThanOrEqualTo()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everywoman' . rand(),
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = clone $transaction->createdAt;
        $past->modify("-1 hour");
        $now = $transaction->createdAt;
        $future = clone $transaction->createdAt;
        $future->modify("+1 hour");

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->lessThanOrEqualTo($future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->lessThanOrEqualTo($now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->lessThanOrEqualTo($past)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_createdAt_GreaterThanOrEqualTo()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everyman' . rand(),
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = clone $transaction->createdAt;
        $past->modify("-1 hour");
        $now = $transaction->createdAt;
        $future = clone $transaction->createdAt;
        $future->modify("+1 hour");

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->GreaterThanOrEqualTo($future)
        ));
        $this->assertEquals(0, $collection->maximumCount());

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->GreaterThanOrEqualTo($now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->GreaterThanOrEqualTo($past)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }


    function test_rangeNode_createdAt_between()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everyman' . rand(),
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = clone $transaction->createdAt;
        $past->modify("-1 hour");
        $now = $transaction->createdAt;
        $future = clone $transaction->createdAt;
        $future->modify("+1 hour");
        $future2 = clone $transaction->createdAt;
        $future2->modify("+1 day");

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->between($now, $future)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->between($past, $now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->between($future, $future2)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_createdAt_is()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everyman' . rand(),
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = clone $transaction->createdAt;
        $past->modify("-1 hour");
        $now = $transaction->createdAt;
        $future = clone $transaction->createdAt;
        $future->modify("+1 hour");

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->is($future)
        ));
        $this->assertEquals(0, $collection->maximumCount());

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->is($now)
        ));
        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Transaction::search(array(
            TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            TransactionSearch::createdAt()->is($past)
        ));
        $this->assertEquals(0, $collection->maximumCount());
    }

    function test_rangeNode_createdAt_convertLocalToUTC()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount'     => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Pingu Penguin' . rand(),
                'number'         => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));

        $ten_min_ago = date_create("now -10 minutes", new \DateTimeZone("US/Pacific"));
        $ten_min_from_now = date_create("now +10 minutes", new \DateTimeZone("US/Pacific"));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::createdAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_createdAt_handlesUTCDateTimes()
    {
        $transaction = Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Pingu Penguin' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));

        $ten_min_ago = date_create("now -10 minutes", new \DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new \DateTimeZone("UTC"));

        $collection = Transaction::search(array(
            TransactionSearch::id()->is($transaction->id),
            TransactionSearch::createdAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->maximumCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

function test_rangeNode_authorizationExpiredAt()
{
    $two_days_ago = date_create("now -2 days", new \DateTimeZone("UTC"));
    $yesterday = date_create("now -1 day", new \DateTimeZone("UTC"));
    $tomorrow = date_create("now +1 day", new \DateTimeZone("UTC"));

    $collection = Transaction::search(array(
        TransactionSearch::authorizationExpiredAt()->between($two_days_ago, $yesterday)
    ));

    $this->assertEquals(0, $collection->maximumCount());

    $collection = Transaction::search(array(
        TransactionSearch::authorizationExpiredAt()->between($yesterday, $tomorrow)
    ));

    $this->assertGreaterThan(0, $collection->maximumCount());
    $this->assertEquals(Transaction::AUTHORIZATION_EXPIRED, $collection->firstItem()->status);
}

function test_rangeNode_authorizedAt()
{
    $transaction = Transaction::saleNoValidate(array(
        'amount'     => '1000.00',
        'creditCard' => array(
            'number'         => '4111111111111111',
            'expirationDate' => '05/12'
        )
    ));

    $twenty_min_ago = date_create("now -20 minutes", new \DateTimeZone("UTC"));
    $ten_min_ago = date_create("now -10 minutes", new \DateTimeZone("UTC"));
    $ten_min_from_now = date_create("now +10 minutes", new \DateTimeZone("UTC"));

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::authorizedAt()->between($twenty_min_ago, $ten_min_ago)
    ));

    $this->assertEquals(0, $collection->maximumCount());

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::authorizedAt()->between($ten_min_ago, $ten_min_from_now)
    ));

    $this->assertEquals(1, $collection->maximumCount());
    $this->assertEquals($transaction->id, $collection->firstItem()->id);
}

function test_rangeNode_failedAt()
{
    $transaction = Transaction::sale(array(
        'amount'     => '3000.00',
        'creditCard' => array(
            'number'         => '4111111111111111',
            'expirationDate' => '05/12'
        )
    ))->transaction;

    $twenty_min_ago = date_create("now -20 minutes", new \DateTimeZone("UTC"));
    $ten_min_ago = date_create("now -10 minutes", new \DateTimeZone("UTC"));
    $ten_min_from_now = date_create("now +10 minutes", new \DateTimeZone("UTC"));

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::failedAt()->between($twenty_min_ago, $ten_min_ago)
    ));

    $this->assertEquals(0, $collection->maximumCount());

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::failedAt()->between($ten_min_ago, $ten_min_from_now)
    ));

    $this->assertEquals(1, $collection->maximumCount());
    $this->assertEquals($transaction->id, $collection->firstItem()->id);
}

function test_rangeNode_gatewayRejectedAt()
{
    $old_merchant_id = Configuration::merchantId();
    $old_public_key = Configuration::publicKey();
    $old_private_key = Configuration::privateKey();

    Configuration::merchantId('processing_rules_merchant_id');
    Configuration::publicKey('processing_rules_public_key');
    Configuration::privateKey('processing_rules_private_key');

    $transaction = Transaction::sale(array(
        'amount'     => '1000.00',
        'creditCard' => array(
            'number'         => '4111111111111111',
            'expirationDate' => '05/12',
            'cvv'            => '200'
        )
    ))->transaction;

    $twenty_min_ago = date_create("now -20 minutes", new \DateTimeZone("UTC"));
    $ten_min_ago = date_create("now -10 minutes", new \DateTimeZone("UTC"));
    $ten_min_from_now = date_create("now +10 minutes", new \DateTimeZone("UTC"));

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::gatewayRejectedAt()->between($twenty_min_ago, $ten_min_ago)
    ));

    $firstCount = $collection->maximumCount();

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::gatewayRejectedAt()->between($ten_min_ago, $ten_min_from_now)
    ));

    $secondCount = $collection->maximumCount();
    $firstId = $collection->firstItem()->id;

    Configuration::merchantId($old_merchant_id);
    Configuration::publicKey($old_public_key);
    Configuration::privateKey($old_private_key);

    $this->assertEquals(0, $firstCount);
    $this->assertEquals(1, $secondCount);
    $this->assertEquals($transaction->id, $firstId);
}

function test_rangeNode_processorDeclinedAt()
{
    $transaction = Transaction::sale(array(
        'amount'     => '2000.00',
        'creditCard' => array(
            'number'         => '4111111111111111',
            'expirationDate' => '05/12'
        )
    ))->transaction;

    $twenty_min_ago = date_create("now -20 minutes", new \DateTimeZone("UTC"));
    $ten_min_ago = date_create("now -10 minutes", new \DateTimeZone("UTC"));
    $ten_min_from_now = date_create("now +10 minutes", new \DateTimeZone("UTC"));

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::processorDeclinedAt()->between($twenty_min_ago, $ten_min_ago)
    ));

    $this->assertEquals(0, $collection->maximumCount());

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::processorDeclinedAt()->between($ten_min_ago, $ten_min_from_now)
    ));

    $this->assertEquals(1, $collection->maximumCount());
    $this->assertEquals($transaction->id, $collection->firstItem()->id);
}

function test_rangeNode_settledAt()
{
    $transaction = Transaction::saleNoValidate(array(
        'amount'     => '1000.00',
        'creditCard' => array(
            'number'         => '4111111111111111',
            'expirationDate' => '05/12'
        ),
        'options'    => array(
            'submitForSettlement' => true
        )
    ));

    $http = new Http(Configuration::$global);
    $path = Configuration::$global->merchantPath() . '/transactions/' . $transaction->id . '/settle';
    $http->put($path);
    $transaction = Transaction::find($transaction->id);

    $twenty_min_ago = date_create("now -20 minutes", new \DateTimeZone("UTC"));
    $ten_min_ago = date_create("now -10 minutes", new \DateTimeZone("UTC"));
    $ten_min_from_now = date_create("now +10 minutes", new \DateTimeZone("UTC"));

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::settledAt()->between($twenty_min_ago, $ten_min_ago)
    ));

    $this->assertEquals(0, $collection->maximumCount());

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::settledAt()->between($ten_min_ago, $ten_min_from_now)
    ));

    $this->assertEquals(1, $collection->maximumCount());
    $this->assertEquals($transaction->id, $collection->firstItem()->id);
}

function test_rangeNode_submittedForSettlementAt()
{
    $transaction = Transaction::sale(array(
        'amount'     => '1000.00',
        'creditCard' => array(
            'number'         => '4111111111111111',
            'expirationDate' => '05/12'
        ),
        'options'    => array(
            'submitForSettlement' => true
        )
    ))->transaction;

    $twenty_min_ago = date_create("now -20 minutes", new \DateTimeZone("UTC"));
    $ten_min_ago = date_create("now -10 minutes", new \DateTimeZone("UTC"));
    $ten_min_from_now = date_create("now +10 minutes", new \DateTimeZone("UTC"));

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::submittedForSettlementAt()->between($twenty_min_ago, $ten_min_ago)
    ));

    $this->assertEquals(0, $collection->maximumCount());

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::submittedForSettlementAt()->between($ten_min_ago, $ten_min_from_now)
    ));

    $this->assertEquals(1, $collection->maximumCount());
    $this->assertEquals($transaction->id, $collection->firstItem()->id);
}

function test_rangeNode_voidedAt()
{
    $transaction = Transaction::saleNoValidate(array(
        'amount'     => '1000.00',
        'creditCard' => array(
            'number'         => '4111111111111111',
            'expirationDate' => '05/12'
        )
    ));

    $transaction = Transaction::void($transaction->id)->transaction;

    $twenty_min_ago = date_create("now -20 minutes", new \DateTimeZone("UTC"));
    $ten_min_ago = date_create("now -10 minutes", new \DateTimeZone("UTC"));
    $ten_min_from_now = date_create("now +10 minutes", new \DateTimeZone("UTC"));

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::voidedAt()->between($twenty_min_ago, $ten_min_ago)
    ));

    $this->assertEquals(0, $collection->maximumCount());

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::voidedAt()->between($ten_min_ago, $ten_min_from_now)
    ));

    $this->assertEquals(1, $collection->maximumCount());
    $this->assertEquals($transaction->id, $collection->firstItem()->id);
}

function test_rangeNode_canSearchOnMultipleStatuses()
{
    $transaction = Transaction::sale(array(
        'amount'     => '1000.00',
        'creditCard' => array(
            'number'         => '4111111111111111',
            'expirationDate' => '05/12'
        ),
        'options'    => array(
            'submitForSettlement' => true
        )
    ))->transaction;

    $twenty_min_ago = date_create("now -20 minutes", new \DateTimeZone("UTC"));
    $ten_min_ago = date_create("now -10 minutes", new \DateTimeZone("UTC"));
    $ten_min_from_now = date_create("now +10 minutes", new \DateTimeZone("UTC"));

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::authorizedAt()->between($twenty_min_ago, $ten_min_ago),
        TransactionSearch::submittedForSettlementAt()->between($twenty_min_ago, $ten_min_ago)
    ));

    $this->assertEquals(0, $collection->maximumCount());

    $collection = Transaction::search(array(
        TransactionSearch::id()->is($transaction->id),
        TransactionSearch::authorizedAt()->between($ten_min_ago, $ten_min_from_now),
        TransactionSearch::submittedForSettlementAt()->between($ten_min_ago, $ten_min_from_now)
    ));

    $this->assertEquals(1, $collection->maximumCount());
    $this->assertEquals($transaction->id, $collection->firstItem()->id);
}

function test_advancedSearchGivesIterableResult()
{
    $collection = Transaction::search(array(
        TransactionSearch::creditCardNumber()->startsWith("411111")
    ));
    $this->assertTrue($collection->maximumCount() > 100);

    $arr = array();
    foreach ($collection as $transaction) {
        array_push($arr, $transaction->id);
    }
    $unique_transaction_ids = array_unique(array_values($arr));
    $this->assertEquals($collection->maximumCount(), count($unique_transaction_ids));
}

function test_handles_search_timeout()
{
    $this->setExpectedException('Braintree\Exception\DownForMaintenance');
    $collection = Transaction::search(array(
        TransactionSearch::amount()->is('-5')
    ));
}

function testHandlesPayPalAccounts()
{
    $http = new HttpClientApi(Configuration::$global);
    $nonce = $http->nonceForPayPalAccount(array(
        'paypal_account' => array(
            'access_token' => 'PAYPAL_ACCESS_TOKEN'
        )
    ));

    $result = Transaction::sale(array(
        'amount'             => TransactionAmounts::$authorize,
        'paymentMethodNonce' => $nonce,
    ));

    $this->assertTrue($result->success);
    $paypalDetails = $result->transaction->paypalDetails;

    $collection = Transaction::search(array(
        TransactionSearch::paypalPaymentId()->is($paypalDetails->paymentId),
        TransactionSearch::paypalAuthorizationId()->is($paypalDetails->authorizationId),
        TransactionSearch::paypalPayerEmail()->is($paypalDetails->payerEmail)
    ));

    $this->assertEquals(1, $collection->maximumCount());
    $this->assertEquals($result->transaction->id, $collection->firstItem()->id);

}
}
