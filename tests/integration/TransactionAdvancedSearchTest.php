<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_TransactionAdvancedSearchTest extends PHPUnit_Framework_TestCase
{
    function testNoMatches()
    {
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::billingFirstName()->is('thisnameisnotreal')
        ));

        $this->assertEquals(0, $collection->_approximateCount());
    }

    function testOneResultForTextFieldSearch()
    {
        $firstName  = 'Tim' . rand();
        $token      = 'creditcard' . rand();
        $customerId = 'customer' . rand();

        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith',
                'token'          => $token,
            ),
            'billing' => array(
                'company'         => 'braintree',
                'countryName'     => 'united states of america',
                'extendedAddress' => 'suite 123',
                'firstName'       => $firstName,
                'lastName'        => 'smith',
                'locality'        => 'chicago',
                'postalCode'      => '12345',
                'region'          => 'il',
                'streetAddress'   => '123 main st'
            ),
            'customer' => array(
                'company'   => 'braintree',
                'email'     => 'smith@example.com',
                'fax'       => '5551231234',
                'firstName' => 'tom',
                'id'        => $customerId,
                'lastName'  => 'smith',
                'phone'     => '5551231234',
                'website'   => 'http://example.com',
            ),
            'options' => array(
                'storeInVault' => true
            ),
            'orderId' => 'myorder',
            'shipping' => array(
                'company'         => 'braintree p.s.',
                'countryName'     => 'mexico',
                'extendedAddress' => 'apt 456',
                'firstName'       => 'thomas',
                'lastName'        => 'smithy',
                'locality'        => 'braintree',
                'postalCode'      => '54321',
                'region'          => 'ma',
                'streetAddress'   => '456 road'
            ),
        ));


        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::billingCompany()->is("Braintree"),
            Braintree_TransactionSearch::billingCountryName()->is("United States of America"),
            Braintree_TransactionSearch::billingExtendedAddress()->is("Suite 123"),
            Braintree_TransactionSearch::billingFirstName()->is($firstName),
            Braintree_TransactionSearch::billingLastName()->is("Smith"),
            Braintree_TransactionSearch::billingLocality()->is("Chicago"),
            Braintree_TransactionSearch::billingPostalCode()->is("12345"),
            Braintree_TransactionSearch::billingRegion()->is("IL"),
            Braintree_TransactionSearch::billingStreetAddress()->is("123 Main St"),
            Braintree_TransactionSearch::creditCardCardholderName()->is("Tom Smith"),
            Braintree_TransactionSearch::creditCardExpirationDate()->is("05/2012"),
            Braintree_TransactionSearch::creditCardNumber()->is(Braintree_Test_CreditCardNumbers::$visa),
            Braintree_TransactionSearch::customerCompany()->is("Braintree"),
            Braintree_TransactionSearch::customerEmail()->is("smith@example.com"),
            Braintree_TransactionSearch::customerFax()->is("5551231234"),
            Braintree_TransactionSearch::customerFirstName()->is("Tom"),
            Braintree_TransactionSearch::customerId()->is($customerId),
            Braintree_TransactionSearch::customerLastName()->is("Smith"),
            Braintree_TransactionSearch::customerPhone()->is("5551231234"),
            Braintree_TransactionSearch::customerWebsite()->is("http://example.com"),
            Braintree_TransactionSearch::orderId()->is("myorder"),
            Braintree_TransactionSearch::paymentMethodToken()->is($token),
            Braintree_TransactionSearch::processorAuthorizationCode()->is($transaction->processorAuthorizationCode),
            Braintree_TransactionSearch::shippingCompany()->is("Braintree P.S."),
            Braintree_TransactionSearch::shippingCountryName()->is("Mexico"),
            Braintree_TransactionSearch::shippingExtendedAddress()->is("Apt 456"),
            Braintree_TransactionSearch::shippingFirstName()->is("Thomas"),
            Braintree_TransactionSearch::shippingLastName()->is("Smithy"),
            Braintree_TransactionSearch::shippingLocality()->is("Braintree"),
            Braintree_TransactionSearch::shippingPostalCode()->is("54321"),
            Braintree_TransactionSearch::shippingRegion()->is("MA"),
            Braintree_TransactionSearch::shippingStreetAddress()->is("456 Road"),
            Braintree_TransactionSearch::id()->is($transaction->id)
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function testEachTextField()
    {
        $firstName  = 'Tim' . rand();
        $token      = 'creditcard' . rand();
        $customerId = 'customer' . rand();

        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith',
                'token'          => $token,
            ),
            'billing' => array(
                'company'         => 'braintree',
                'countryName'     => 'united states of america',
                'extendedAddress' => 'suite 123',
                'firstName'       => $firstName,
                'lastName'        => 'smith',
                'locality'        => 'chicago',
                'postalCode'      => '12345',
                'region'          => 'il',
                'streetAddress'   => '123 main st'
            ),
            'customer' => array(
                'company'   => 'braintree',
                'email'     => 'smith@example.com',
                'fax'       => '5551231234',
                'firstName' => 'tom',
                'id'        => $customerId,
                'lastName'  => 'smith',
                'phone'     => '5551231234',
                'website'   => 'http://example.com',
            ),
            'options' => array(
                'storeInVault' => true
            ),
            'orderId' => 'myorder',
            'shipping' => array(
                'company'         => 'braintree p.s.',
                'countryName'     => 'mexico',
                'extendedAddress' => 'apt 456',
                'firstName'       => 'thomas',
                'lastName'        => 'smithy',
                'locality'        => 'braintree',
                'postalCode'      => '54321',
                'region'          => 'ma',
                'streetAddress'   => '456 road'
            ),
        ));

        $search_criteria = array(
          'billingCompany' => "Braintree",
          'billingCountryName' => "United States of America",
          'billingExtendedAddress' => "Suite 123",
          'billingFirstName' => $firstName,
          'billingLastName' => "Smith",
          'billingLocality' => "Chicago",
          'billingPostalCode' => "12345",
          'billingRegion' => "IL",
          'billingStreetAddress' => "123 Main St",
          'creditCardCardholderName' => "Tom Smith",
          'creditCardExpirationDate' => "05/2012",
          'creditCardNumber' => Braintree_Test_CreditCardNumbers::$visa,
          'customerCompany' => "Braintree",
          'customerEmail' => "smith@example.com",
          'customerFax' => "5551231234",
          'customerFirstName' => "Tom",
          'customerId' => $customerId,
          'customerLastName' => "Smith",
          'customerPhone' => "5551231234",
          'customerWebsite' => "http://example.com",
          'orderId' => "myorder",
          'paymentMethodToken' => $token,
          'processorAuthorizationCode' => $transaction->processorAuthorizationCode,
          'shippingCompany' => "Braintree P.S.",
          'shippingCountryName' => "Mexico",
          'shippingExtendedAddress' => "Apt 456",
          'shippingFirstName' => "Thomas",
          'shippingLastName' => "Smithy",
          'shippingLocality' => "Braintree",
          'shippingPostalCode' => "54321",
          'shippingRegion' => "MA",
          'shippingStreetAddress' => "456 Road"
        );

        foreach ($search_criteria AS $criterion => $value) {
            $collection = Braintree_Transaction::search(array(
                Braintree_TransactionSearch::$criterion()->is($value),
                Braintree_TransactionSearch::id()->is($transaction->id)
            ));
            $this->assertEquals(1, $collection->_approximateCount());
            $this->assertEquals($transaction->id, $collection->firstItem()->id);

            $collection = Braintree_Transaction::search(array(
                Braintree_TransactionSearch::$criterion()->is('invalid_attribute'),
                Braintree_TransactionSearch::id()->is($transaction->id)
            ));
            $this->assertEquals(0, $collection->_approximateCount());
        }
    }

    function testIs()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->is('tom smith')
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->is('somebody else')
        ));

        $this->assertEquals(0, $collection->_approximateCount());
    }

    function testIsNot()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->isNot('somebody else')
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->isNot('tom smith')
        ));

        $this->assertEquals(0, $collection->_approximateCount());
    }

    function testEndsWith()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->endsWith('m smith')
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->endsWith('tom s')
        ));

        $this->assertEquals(0, $collection->_approximateCount());
    }

    function testStartsWith()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->startsWith('tom s')
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->startsWith('m smith')
        ));

        $this->assertEquals(0, $collection->_approximateCount());
    }

    function testContains()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012',
                'cardholderName' => 'tom smith'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->contains('m sm')
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardholderName()->contains('something else')
        ));

        $this->assertEquals(0, $collection->_approximateCount());
    }

    function test_multipleValueNode_createdUsing()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::createdUsing()->is(Braintree_Transaction::FULL_INFORMATION)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::createdUsing()->in(
                array(Braintree_Transaction::FULL_INFORMATION, Braintree_Transaction::TOKEN)
            )
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::createdUsing()->in(array(Braintree_Transaction::TOKEN))
        ));
        $this->assertEquals(0, $collection->_approximateCount());
    }

    function test_multipleValueNode_createdUsing_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for created_using: noSuchCreatedUsing');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::createdUsing()->is('noSuchCreatedUsing')
        ));
    }

    function test_multipleValueNode_creditCardCustomerLocation()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCustomerLocation()->is(Braintree_CreditCard::US)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCustomerLocation()->in(
                array(Braintree_CreditCard::US, Braintree_CreditCard::INTERNATIONAL)
            )
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCustomerLocation()->in(array(Braintree_CreditCard::INTERNATIONAL))
        ));
        $this->assertEquals(0, $collection->_approximateCount());
    }

    function test_multipleValueNode_creditCardCustomerLocation_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for credit_card_customer_location: noSuchLocation');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCustomerLocation()->is('noSuchLocation')
        ));
    }

    function test_multipleValueNode_merchantAccountId()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::merchantAccountId()->is($transaction->merchantAccountId)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::merchantAccountId()->in(
                array($transaction->merchantAccountId, "bogus_merchant_account_id")
            )
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::merchantAccountId()->is("bogus_merchant_account_id")
        ));
        $this->assertEquals(0, $collection->_approximateCount());
    }

    function test_multipleValueNode_creditCardType()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardType()->is($transaction->creditCardDetails->cardType)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardType()->in(
                array($transaction->creditCardDetails->cardType, Braintree_CreditCard::CHINA_UNION_PAY)
            )
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::creditCardCardType()->is(Braintree_CreditCard::CHINA_UNION_PAY)
        ));
        $this->assertEquals(0, $collection->_approximateCount());
    }

    function test_multipleValueNode_creditCardType_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for credit_card_card_type: noSuchCardType');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardType()->is('noSuchCardType')
        ));
    }

    function test_multipleValueNode_status()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::status()->is($transaction->status)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::status()->in(
                array($transaction->status, Braintree_Transaction::SETTLED)
            )
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::status()->is(Braintree_Transaction::SETTLED)
        ));
        $this->assertEquals(0, $collection->_approximateCount());
    }

    function test_multipleValueNode_status_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for status: noSuchStatus');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::status()->is('noSuchStatus')
        ));
    }

    function test_multipleValueNode_source()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'creditCard' => array(
                'number'         => Braintree_Test_CreditCardNumbers::$visa,
                'expirationDate' => '05/2012'
            )
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::source()->is(Braintree_Transaction::API)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::source()->in(
                array(Braintree_Transaction::API, Braintree_Transaction::RECURRING)
            )
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::source()->is(Braintree_Transaction::RECURRING)
        ));
        $this->assertEquals(0, $collection->_approximateCount());
    }

    function test_multipleValueNode_source_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for source: noSuchSource');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::source()->is('noSuchSource')
        ));
    }

    function test_multipleValueNode_type()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCard = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Joe Everyman' . rand(),
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $sale = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodToken' => $creditCard->token,
            'options' => array('submitForSettlement' => true)
        ));
        Braintree_Http::put('/transactions/' . $sale->id . '/settle');
        $refund = Braintree_Transaction::refund($sale->id)->transaction;

        $credit = Braintree_Transaction::creditNoValidate(array(
            'amount' => '100.00',
            'paymentMethodToken' => $creditCard->token
        ));


        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::type()->is($sale->type)
        ));
        $this->assertEquals(1, $collection->_approximateCount());


        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::type()->in(
                array($sale->type, $credit->type)
            )
        ));
        $this->assertEquals(3, $collection->_approximateCount());


        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::type()->is($credit->type)
        ));
        $this->assertEquals(2, $collection->_approximateCount());
    }

    function test_multipleValueNode_type_allowedValues()
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid argument(s) for type: noSuchType');
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::type()->is('noSuchType')
        ));
    }

    function test_multipleValueNode_type_withRefund()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCard = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Joe Everyman' . rand(),
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $sale = Braintree_Transaction::saleNoValidate(array(
            'amount' => Braintree_Test_TransactionAmounts::$authorize,
            'paymentMethodToken' => $creditCard->token,
            'options' => array('submitForSettlement' => true)
        ));
        Braintree_Http::put('/transactions/' . $sale->id . '/settle');
        $refund = Braintree_Transaction::refund($sale->id)->transaction;

        $credit = Braintree_Transaction::creditNoValidate(array(
            'amount' => '100.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::type()->is($credit->type),
            Braintree_TransactionSearch::refund()->is(True)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($refund->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::type()->is($credit->type),
            Braintree_TransactionSearch::refund()->is(False)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($credit->id, $collection->firstItem()->id);
    }

    function test_rangeNode_amount()
    {
        $customer = Braintree_Customer::createNoValidate();
        $creditCard = Braintree_CreditCard::create(array(
            'customerId' => $customer->id,
            'cardholderName' => 'Jane Everywoman' . rand(),
            'number' => '5105105105105100',
            'expirationDate' => '05/12'
        ))->creditCard;

        $t_1000 = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $t_1500 = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1500.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $t_1800 = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1800.00',
            'paymentMethodToken' => $creditCard->token
        ));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::amount()->greaterThanOrEqualTo('1700')
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($t_1800->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::amount()->lessThanOrEqualTo('1250')
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($t_1000->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($creditCard->cardholderName),
            Braintree_TransactionSearch::amount()->between('1100', '1600')
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($t_1500->id, $collection->firstItem()->id);
    }

    function test_rangeNode_createdAt_lessThanOrEqualTo()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everywoman' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = date_sub(clone $transaction->createdAt, new DateInterval("PT1H"));
        $now = $transaction->createdAt;
        $future = date_add(clone $transaction->createdAt, new DateInterval("PT1H"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->lessThanOrEqualTo($future)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->lessThanOrEqualTo($now)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->lessThanOrEqualTo($past)
        ));
        $this->assertEquals(0, $collection->_approximateCount());
    }

    function test_rangeNode_createdAt_GreaterThanOrEqualTo()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everyman' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = date_sub(clone $transaction->createdAt, new DateInterval("PT1H"));
        $now = $transaction->createdAt;
        $future = date_add(clone $transaction->createdAt, new DateInterval("PT1H"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->GreaterThanOrEqualTo($future)
        ));
        $this->assertEquals(0, $collection->_approximateCount());

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->GreaterThanOrEqualTo($now)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->GreaterThanOrEqualTo($past)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_createdAt_between()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Ted Everyman' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));
        $past = date_sub(clone $transaction->createdAt, new DateInterval("PT1H"));
        $now = $transaction->createdAt;
        $future = date_add(clone $transaction->createdAt, new DateInterval("PT1H"));
        $future2 = date_add(clone $transaction->createdAt, new DateInterval("P1D"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->between($past, $future)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->between($now, $future)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->between($past, $now)
        ));
        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardCardholderName()->is($transaction->creditCardDetails->cardholderName),
            Braintree_TransactionSearch::createdAt()->between($future, $future2)
        ));
        $this->assertEquals(0, $collection->_approximateCount());
    }

    function test_rangeNode_createdAt_convertLocalToUTC()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Pingu Penguin' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));

        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("US/Pacific"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("US/Pacific"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::createdAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_rangeNode_createdAt_handlesUTCDateTimes()
    {
        $transaction = Braintree_Transaction::saleNoValidate(array(
            'amount' => '1000.00',
            'creditCard' => array(
                'cardholderName' => 'Pingu Penguin' . rand(),
                'number' => '5105105105105100',
                'expirationDate' => '05/12'
            )
        ));

        $ten_min_ago = date_create("now -10 minutes", new DateTimeZone("UTC"));
        $ten_min_from_now = date_create("now +10 minutes", new DateTimeZone("UTC"));

        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::id()->is($transaction->id),
            Braintree_TransactionSearch::createdAt()->between($ten_min_ago, $ten_min_from_now)
        ));

        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals($transaction->id, $collection->firstItem()->id);
    }

    function test_advancedSearchGivesIterableResult()
    {
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::creditCardNumber()->startsWith("411111")
        ));
        $this->assertTrue($collection->_approximateCount() > 100);

        $arr = array();
        foreach($collection as $transaction) {
            array_push($arr, $transaction->id);
        }
        $unique_transaction_ids = array_unique(array_values($arr));
        $this->assertEquals($collection->_approximateCount(), count($unique_transaction_ids));
    }
}
?>
