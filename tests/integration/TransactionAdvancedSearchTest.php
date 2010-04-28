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
      // it "searches on credit_card_customer_location" do
      //   transaction = Braintree::Transaction.sale!(
      //     :amount => Braintree::Test::TransactionAmounts::Authorize,
      //     :credit_card => {
      //     :number => Braintree::Test::CreditCardNumbers::Visa,
      //     :expiration_date => "05/12"
      //   }
      //   )

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.credit_card_customer_location.is Braintree::CreditCard::CustomerLocation::US
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.credit_card_customer_location.in Braintree::CreditCard::CustomerLocation::US, Braintree::CreditCard::CustomerLocation::International
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.credit_card_customer_location.is Braintree::CreditCard::CustomerLocation::International
      //   end

      //   collection._approximate_size.should == 0
      // end

      // it "searches on merchant_account_id" do
      //   transaction = Braintree::Transaction.sale!(
      //     :amount => Braintree::Test::TransactionAmounts::Authorize,
      //     :credit_card => {
      //     :number => Braintree::Test::CreditCardNumbers::Visa,
      //     :expiration_date => "05/12"
      //   }
      //   )

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.merchant_account_id.is transaction.merchant_account_id
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.merchant_account_id.in transaction.merchant_account_id, "bogus_merchant_account_id"
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.merchant_account_id.is "bogus_merchant_account_id"
      //   end

      //   collection._approximate_size.should == 0
      // end

      // it "searches on credit_card_card_type" do
      //   transaction = Braintree::Transaction.sale!(
      //     :amount => Braintree::Test::TransactionAmounts::Authorize,
      //     :credit_card => {
      //     :number => Braintree::Test::CreditCardNumbers::Visa,
      //     :expiration_date => "05/12"
      //   }
      //   )

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.credit_card_card_type.is Braintree::CreditCard::CardType::Visa
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.credit_card_card_type.is transaction.credit_card_details.card_type
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.credit_card_card_type.in Braintree::CreditCard::CardType::Visa, Braintree::CreditCard::CardType::MasterCard
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.credit_card_card_type.is Braintree::CreditCard::CardType::MasterCard
      //   end

      //   collection._approximate_size.should == 0
      // end

      // it "searches on status" do
      //   transaction = Braintree::Transaction.sale!(
      //     :amount => Braintree::Test::TransactionAmounts::Authorize,
      //     :credit_card => {
      //     :number => Braintree::Test::CreditCardNumbers::Visa,
      //     :expiration_date => "05/12"
      //   }
      //   )

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.status.is Braintree::Transaction::Status::Authorized
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.status.in Braintree::Transaction::Status::Authorized, Braintree::Transaction::Status::ProcessorDeclined
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.status.is Braintree::Transaction::Status::ProcessorDeclined
      //   end

      //   collection._approximate_size.should == 0
      // end

      // it "searches on source" do
      //   transaction = Braintree::Transaction.sale!(
      //     :amount => Braintree::Test::TransactionAmounts::Authorize,
      //     :credit_card => {
      //       :number => Braintree::Test::CreditCardNumbers::Visa,
      //       :expiration_date => "05/12"
      //     }
      //   )

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.source.is Braintree::Transaction::Source::Api
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.source.in Braintree::Transaction::Source::Api, Braintree::Transaction::Source::ControlPanel
      //   end

      //   collection._approximate_size.should == 1

      //   collection = Braintree::Transaction.search do |search|
      //     search.id.is transaction.id
      //     search.source.is Braintree::Transaction::Source::ControlPanel
      //   end

      //   collection._approximate_size.should == 0
      // end

      // it "searches on transaction_type" do
      //   cardholder_name = "refunds#{rand(10000)}"
      //   credit_transaction = Braintree::Transaction.credit!(
      //     :amount => Braintree::Test::TransactionAmounts::Authorize,
      //     :credit_card => {
      //     :cardholder_name => cardholder_name,
      //     :number => Braintree::Test::CreditCardNumbers::Visa,
      //     :expiration_date => "05/12"
      //   }
      //   )

      //   transaction = Braintree::Transaction.sale!(
      //     :amount => Braintree::Test::TransactionAmounts::Authorize,
      //     :credit_card => {
      //     :cardholder_name => cardholder_name,
      //     :number => Braintree::Test::CreditCardNumbers::Visa,
      //     :expiration_date => "05/12"
      //   },
      //   :options => { :submit_for_settlement => true }
      //   )
      //   Braintree::Http.put "/transactions/#{transaction.id}/settle"

      //   refund_transaction = transaction.refund.new_transaction

      //   collection = Braintree::Transaction.search do |search|
      //     search.credit_card_cardholder_name.is cardholder_name
      //     search.type.is Braintree::Transaction::Type::Credit
      //   end

      //   collection._approximate_size.should == 2

      //   collection = Braintree::Transaction.search do |search|
      //     search.credit_card_cardholder_name.is cardholder_name
      //     search.type.is Braintree::Transaction::Type::Credit
      //     search.refund.is true
      //   end

      //   collection._approximate_size.should == 1
      //   collection.first.id.should == refund_transaction.id

      //   collection = Braintree::Transaction.search do |search|
      //     search.credit_card_cardholder_name.is cardholder_name
      //     search.type.is Braintree::Transaction::Type::Credit
      //     search.refund.is false
      //   end

      //   collection._approximate_size.should == 1
      //   collection.first.id.should == credit_transaction.id
      // end
}
?>

