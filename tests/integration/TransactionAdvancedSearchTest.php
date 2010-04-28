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
}
?>

