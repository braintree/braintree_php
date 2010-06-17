<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_TransparentRedirectTest extends PHPUnit_Framework_TestCase
{
    function testRedirectUrl()
    {
        $trData = Braintree_TransparentRedirect::createCustomerData(
            array("redirectUrl" => "http://www.example.com?foo=bar")
        );
        $queryString = Braintree_TestHelper::submitTrRequest(
            Braintree_Configuration::merchantUrl() . '/test/maintenance',
            array(),
            $trData
        );
        $this->setExpectedException('Braintree_Exception_DownForMaintenance');
        Braintree_Customer::createFromTransparentRedirect($queryString);
    }

    function testParseAndValidateQueryString_throwsDownForMaintenanceErrorIfDownForMaintenance()
    {
        $trData = Braintree_TransparentRedirect::createCustomerData(
            array("redirectUrl" => "http://www.example.com")
        );
        $queryString = Braintree_TestHelper::submitTrRequest(
            Braintree_Configuration::merchantUrl() . '/test/maintenance',
            array(),
            $trData
        );
        $this->setExpectedException('Braintree_Exception_DownForMaintenance');
        Braintree_Customer::createFromTransparentRedirect($queryString);
    }

    function testParseAndValidateQueryString_throwsAuthenticationErrorIfBadCredentials()
    {
        $privateKey = Braintree_Configuration::privateKey();
        Braintree_Configuration::privateKey('incorrect');
        try {
            $trData = Braintree_TransparentRedirect::createCustomerData(
                array("redirectUrl" => "http://www.example.com")
            );
            $queryString = Braintree_TestHelper::submitTrRequest(
                Braintree_Customer::createCustomerUrl(),
                array(),
                $trData
            );
            $this->setExpectedException('Braintree_Exception_Authentication');
            Braintree_Customer::createFromTransparentRedirect($queryString);
        } catch(Exception $e) {
        }
        $privateKey = Braintree_Configuration::privateKey($privateKey);
        if (isset($e)) throw $e;
    }

    function testCreateTransactionFromTransparentRedirect()
    {
        $params = array(
            'transaction' => array(
                'customer' => array(
                    'first_name' => 'First'
                ),
                'credit_card' => array(
                    'number' => '5105105105105100',
                    'expiration_date' => '05/12'
                )
            )
        );
        $trParams = array(
            'transaction' => array(
                'type' => Braintree_Transaction::SALE,
                'amount' => '100.00'
            )
        );

        $trData = Braintree_TransparentRedirect::transactionData(
            array_merge($trParams, array("redirectUrl" => "http://www.example.com"))
        );

        $queryString = Braintree_TestHelper::submitTrRequest(
            Braintree_TransparentRedirect::url(),
            $params,
            $trData
        );

        $result = Braintree_Transaction::createFromTransparentRedirect($queryString);
        $this->assertTrue($result->success);
        $this->assertEquals('100.00', $result->transaction->amount);
        $this->assertEquals(Braintree_Transaction::SALE, $result->transaction->type);
        $this->assertEquals(Braintree_Transaction::AUTHORIZED, $result->transaction->status);
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

    function testUrl()
    {
        $url = Braintree_TransparentRedirect::url();
        $this->assertEquals("http://localhost:3000/merchants/integration_merchant_id/transparent_redirect_requests", $url);
    }
}
?>
