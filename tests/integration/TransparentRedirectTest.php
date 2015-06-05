<?php namespace Braintree\Tests\Integration;

use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\Customer;
use Braintree\Gateway;
use Braintree\Transaction;
use Braintree\TransparentRedirect;
use Braintree\Tests\TestHelper;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class TransparentRedirectTest extends \PHPUnit_Framework_TestCase
{
    function testRedirectUrl()
    {
        TestHelper::suppressDeprecationWarnings();
        $trData = TransparentRedirect::createCustomerData(
            array("redirectUrl" => "http://www.example.com?foo=bar")
        );
        $config = Configuration::$global;
        $queryString = TestHelper::submitTrRequest(
            $config->baseUrl() . $config->merchantPath() . '/test/maintenance',
            array(),
            $trData
        );
        $this->setExpectedException('Exception_DownForMaintenance');
        Customer::createFromTransparentRedirect($queryString);
    }

    function testParseAndValidateQueryString_throwsDownForMaintenanceErrorIfDownForMaintenance()
    {
        TestHelper::suppressDeprecationWarnings();
        $trData = TransparentRedirect::createCustomerData(
            array("redirectUrl" => "http://www.example.com")
        );
        $config = Configuration::$global;
        $queryString = TestHelper::submitTrRequest(
            $config->baseUrl() . $config->merchantPath() . '/test/maintenance',
            array(),
            $trData
        );
        $this->setExpectedException('Exception_DownForMaintenance');
        Customer::createFromTransparentRedirect($queryString);
    }

    function testParseAndValidateQueryString_throwsAuthenticationErrorIfBadCredentials()
    {
        TestHelper::suppressDeprecationWarnings();
        $privateKey = Configuration::privateKey();
        Configuration::privateKey('incorrect');
        try {
            $trData = TransparentRedirect::createCustomerData(
                array("redirectUrl" => "http://www.example.com")
            );
            $queryString = TestHelper::submitTrRequest(
                Customer::createCustomerUrl(),
                array(),
                $trData
            );
            $this->setExpectedException('Exception_Authentication');
            Customer::createFromTransparentRedirect($queryString);
        } catch (\Exception $e) {
        }
        $privateKey = Configuration::privateKey($privateKey);
        if (isset($e)) {
            throw $e;
        }
    }

    function testCreateTransactionFromTransparentRedirect()
    {
        $params = array(
            'transaction' => array(
                'customer'    => array(
                    'first_name' => 'First'
                ),
                'credit_card' => array(
                    'number'          => '5105105105105100',
                    'expiration_date' => '05/12'
                )
            )
        );
        $trParams = array(
            'transaction' => array(
                'type'   => Transaction::SALE,
                'amount' => '100.00'
            )
        );

        $trData = TransparentRedirect::transactionData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );

        $queryString = TestHelper::submitTrRequest(
            TransparentRedirect::url(),
            $params,
            $trData
        );

        $result = TransparentRedirect::confirm($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Transaction::SALE, $result->transaction->type);
        $this->assertEquals(Transaction::AUTHORIZED, $result->transaction->status);
        $creditCard = $result->transaction->creditCardDetails;
        $this->assertEquals('510510', $creditCard->bin);
        $this->assertEquals('5100', $creditCard->last4);
        $this->assertEquals('US', $creditCard->customerLocation);
        $this->assertEquals('MasterCard', $creditCard->cardType);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('510510******5100', $creditCard->maskedNumber);
        $customer = $result->transaction->customerDetails;
        $this->assertequals('First', $customer->firstName);
    }

    function testGatewayCreateTransactionFromTransparentRedirect()
    {
        $params = array(
            'transaction' => array(
                'customer'    => array(
                    'first_name' => 'First'
                ),
                'credit_card' => array(
                    'number'          => '5105105105105100',
                    'expiration_date' => '05/12'
                )
            )
        );
        $trParams = array(
            'transaction' => array(
                'type'   => Transaction::SALE,
                'amount' => '100.00'
            )
        );

        $gateway = new Gateway(array(
            'environment' => 'development',
            'merchantId'  => 'integration_merchant_id',
            'publicKey'   => 'integration_public_key',
            'privateKey'  => 'integration_private_key'
        ));
        $trData = $gateway->transparentRedirect()->transactionData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );

        $queryString = TestHelper::submitTrRequest(
            $gateway->transparentRedirect()->url(),
            $params,
            $trData
        );

        $result = $gateway->transparentRedirect()->confirm($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Transaction::SALE, $result->transaction->type);
        $this->assertEquals(Transaction::AUTHORIZED, $result->transaction->status);
        $creditCard = $result->transaction->creditCardDetails;
        $this->assertEquals('US', $creditCard->customerLocation);
        $this->assertEquals('05/2012', $creditCard->expirationDate);
        $this->assertEquals('510510******5100', $creditCard->maskedNumber);
        $customer = $result->transaction->customerDetails;
        $this->assertequals('First', $customer->firstName);
    }

    function testCreateTransactionWithServiceFeesFromTransparentRedirect()
    {
        $params = array(
            'transaction' => array(
                'customer'            => array(
                    'first_name' => 'First'
                ),
                'credit_card'         => array(
                    'number'          => '5105105105105100',
                    'expiration_date' => '05/12'
                ),
                'service_fee_amount'  => '1.00',
                'merchant_account_id' => TestHelper::nonDefaultSubMerchantAccountId()
            )
        );
        $trParams = array(
            'transaction' => array(
                'type'   => Transaction::SALE,
                'amount' => '100.00'
            )
        );

        $trData = TransparentRedirect::transactionData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );

        $queryString = TestHelper::submitTrRequest(
            TransparentRedirect::url(),
            $params,
            $trData
        );

        $result = TransparentRedirect::confirm($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('1.00', $result->transaction->serviceFeeAmount);
    }

    function testCreateCustomerFromTransparentRedirect()
    {
        $params = array(
            'customer' => array(
                'first_name' => 'Second'
            )
        );
        $trParams = array(
            'customer' => array(
                'lastName' => 'Penultimate'
            )
        );

        $trData = TransparentRedirect::createCustomerData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );

        $queryString = TestHelper::submitTrRequest(
            TransparentRedirect::url(),
            $params,
            $trData
        );

        $result = TransparentRedirect::confirm($queryString);
        $this->assertTrue($result->success);

        $customer = $result->customer;
        $this->assertequals('Second', $customer->firstName);
        $this->assertequals('Penultimate', $customer->lastName);
    }

    function testUpdateCustomerFromTransparentRedirect()
    {
        $customer = Customer::create(array(
            'firstName' => 'Mike',
            'lastName'  => 'Jonez'
        ))->customer;
        $params = array(
            'customer' => array(
                'first_name' => 'Second'
            )
        );
        $trParams = array(
            'customerId' => $customer->id,
            'customer'   => array(
                'lastName' => 'Penultimate'
            )
        );

        $trData = TransparentRedirect::updateCustomerData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );

        $queryString = TestHelper::submitTrRequest(
            TransparentRedirect::url(),
            $params,
            $trData
        );

        $result = TransparentRedirect::confirm($queryString);
        $this->assertTrue($result->success);

        $customer = $result->customer;
        $this->assertequals('Second', $customer->firstName);
        $this->assertequals('Penultimate', $customer->lastName);
    }

    function testCreateCreditCardFromTransparentRedirect()
    {
        $customer = Customer::create(array(
            'firstName' => 'Mike',
            'lastName'  => 'Jonez'
        ))->customer;

        $params = array(
            'credit_card' => array(
                'number' => Test_CreditCardNumbers::$visa
            )
        );
        $trParams = array(
            'creditCard' => array(
                'customerId'      => $customer->id,
                'expirationMonth' => '01',
                'expirationYear'  => '10'
            )
        );

        $trData = TransparentRedirect::createCreditCardData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );

        $queryString = TestHelper::submitTrRequest(
            TransparentRedirect::url(),
            $params,
            $trData
        );

        $result = TransparentRedirect::confirm($queryString);
        $this->assertTrue($result->success);

        $creditCard = $result->creditCard;
        $this->assertequals('401288', $creditCard->bin);
        $this->assertequals('1881', $creditCard->last4);
        $this->assertequals('01/2010', $creditCard->expirationDate);
    }

    function testUpdateCreditCardFromTransparentRedirect()
    {
        $customer = Customer::create(array(
            'firstName' => 'Mike',
            'lastName'  => 'Jonez'
        ))->customer;
        $creditCard = CreditCard::create(array(
            'customerId'      => $customer->id,
            'number'          => Test_CreditCardNumbers::$masterCard,
            'expirationMonth' => '10',
            'expirationYear'  => '10'
        ))->creditCard;

        $params = array(
            'credit_card' => array(
                'number' => Test_CreditCardNumbers::$visa
            )
        );
        $trParams = array(
            'paymentMethodToken' => $creditCard->token,
            'creditCard'         => array(
                'expirationMonth' => '11',
                'expirationYear'  => '11'
            )
        );

        $trData = TransparentRedirect::updateCreditCardData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );

        $queryString = TestHelper::submitTrRequest(
            TransparentRedirect::url(),
            $params,
            $trData
        );

        TransparentRedirect::confirm($queryString);

        $creditCard = CreditCard::find($creditCard->token);
        $this->assertequals('401288', $creditCard->bin);
        $this->assertequals('1881', $creditCard->last4);
        $this->assertequals('11/2011', $creditCard->expirationDate);
    }

    function testUrl()
    {
        $url = TransparentRedirect::url();
        $developmentPort = getenv("GATEWAY_PORT") ? getenv("GATEWAY_PORT") : 3000;
        $this->assertEquals("http://localhost:" . $developmentPort . "/merchants/integration_merchant_id/transparent_redirect_requests",
            $url);
    }
}
