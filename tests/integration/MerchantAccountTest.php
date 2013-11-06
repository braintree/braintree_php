<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_MerchantAccountTest extends PHPUnit_Framework_TestCase
{

    private static $deprecatedValidParams = array(
      'applicantDetails' => array(
        'companyName' => "Robot City",
        'firstName' => "Joe",
        'lastName' => "Bloggs",
        'email' => "joe@bloggs.com",
        'phone' => "555-555-5555",
        'address' => array(
          'streetAddress' => "123 Credibility St.",
          'postalCode' => "60606",
          'locality' => "Chicago",
          'region' => "IL",
        ),
        'dateOfBirth' => "10/9/1980",
        'ssn' => "123-00-1234",
        'taxId' => "123456789",
        'routingNumber' => "122100024",
        'accountNumber' => "43759348798"
      ),
      'tosAccepted' => true,
      'masterMerchantAccountId' => "sandbox_master_merchant_account"
    );

    private static $validParams = array(
      'individual' => array(
        'firstName' => "Joe",
        'lastName' => "Bloggs",
        'email' => "joe@bloggs.com",
        'phone' => "555-555-5555",
        'address' => array(
          'streetAddress' => "123 Credibility St.",
          'postalCode' => "60606",
          'locality' => "Chicago",
          'region' => "IL",
        ),
        'dateOfBirth' => "10/9/1980",
        'ssn' => "123-00-1234",
      ),
      'business' => array(
        'dbaName' => "Robot City",
        'taxId' => "123456789",
      ),
      'funding' => array(
        'routingNumber' => "122100024",
        'accountNumber' => "43759348798"
      ),
      'tosAccepted' => true,
      'masterMerchantAccountId' => "sandbox_master_merchant_account"
    );

    function testCreate()
    {
        $result = Braintree_MerchantAccount::create(self::$validParams);
        $this->assertEquals(true, $result->success);
        $merchantAccount = $result->merchantAccount;
        $this->assertEquals(Braintree_MerchantAccount::STATUS_PENDING, $merchantAccount->status);
        $this->assertEquals("sandbox_master_merchant_account", $merchantAccount->masterMerchantAccount->id);
    }

    function testCreateWithDeprecatedParameters()
    {
        Braintree_TestHelper::suppressDeprecationWarnings();
        $result = Braintree_MerchantAccount::create(self::$deprecatedValidParams);
        $this->assertEquals(true, $result->success);
        $merchantAccount = $result->merchantAccount;
        $this->assertEquals(Braintree_MerchantAccount::STATUS_PENDING, $merchantAccount->status);
        $this->assertEquals("sandbox_master_merchant_account", $merchantAccount->masterMerchantAccount->id);
    }

    function testCreateWithId()
    {
        $rand = rand(1, 1000);
        $subMerchantAccountId = "sub_merchant_account_id" + $rand;
        $validParamsWithId = array_merge(array(), self::$validParams);
        $validParamsWithId['id'] = $subMerchantAccountId;
        $result = Braintree_MerchantAccount::create($validParamsWithId);
        $this->assertEquals(true, $result->success);
        $merchantAccount = $result->merchantAccount;
        $this->assertEquals(Braintree_MerchantAccount::STATUS_PENDING, $merchantAccount->status);
        $this->assertEquals("sandbox_master_merchant_account", $merchantAccount->masterMerchantAccount->id);
        $this->assertEquals("sub_merchant_account_id" + $rand, $merchantAccount->id);
    }

    function testFailedCreate()
    {
        $result = Braintree_MerchantAccount::create(array());
        $this->assertEquals(false, $result->success);
        $errors = $result->errors->forKey('merchantAccount')->onAttribute('masterMerchantAccountId');
        $this->assertEquals(Braintree_Error_Codes::MERCHANT_ACCOUNT_MASTER_MERCHANT_ACCOUNT_ID_IS_REQUIRED, $errors[0]->code);
    }

    function testUpdate()
    {
        $result = Braintree_MerchantAccount::create(self::$validParams);
        $this->assertEquals(true, $result->success);
        $merchantAccount = $result->merchantAccount;

        $updateParams = array('individual' => array('firstName' => "Tye"));

        $result = Braintree_MerchantAccount::update($merchantAccount->id, $updateParams);
        $updatedMerchantAccount = $result->merchantAccount;
        $this->assertEquals("Tye", $updatedMerchantAccount->individualDetails->firstName);
    }
}
?>
