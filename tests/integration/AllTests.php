<?php

require_once 'AddressTest.php';
require_once 'CreditCardTest.php';
require_once 'CustomerTest.php';
require_once 'HttpTest.php';
require_once 'SubscriptionTest.php';
require_once 'SubscriptionSearchTest.php';
require_once 'TransactionTest.php';
require_once 'TransactionAdvancedSearchTest.php';
require_once 'TransparentRedirectTest.php';
require_once 'Error/ErrorCollectionTest.php';
require_once 'Error/ValidationErrorCollectionTest.php';
require_once 'Result/ErrorTest.php';

class Braintree_AllTests {

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite(), array());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->setName('Braintree Library');
        $suite->addTestSuite('Braintree_AddressTest');
        $suite->addTestSuite('Braintree_CreditCardTest');
        $suite->addTestSuite('Braintree_CustomerTest');
        $suite->addTestSuite('Braintree_Error_ErrorCollectionTest');
        $suite->addTestSuite('Braintree_Error_ValidationErrorCollectionTest');
        $suite->addTestSuite('Braintree_HttpTest');
        $suite->addTestSuite('Braintree_Result_ErrorTest');
        $suite->addTestSuite('Braintree_SubscriptionSearchTest');
        $suite->addTestSuite('Braintree_SubscriptionTest');
        $suite->addTestSuite('Braintree_TransactionTest');
        $suite->addTestSuite('Braintree_TransactionAdvancedSearchTest');
        $suite->addTestSuite('Braintree_TransparentRedirectTest');
        return $suite;
    }
}

?>
