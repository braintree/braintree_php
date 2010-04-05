<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_TransparentRedirectTest extends PHPUnit_Framework_TestCase
{
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
}
?>
