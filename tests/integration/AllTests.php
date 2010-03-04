<?php

require_once 'AddressTest.php';
require_once 'CreditCardTest.php';
require_once 'CustomerTest.php';
require_once 'TransactionTest.php';
require_once 'Error/ErrorCollectionTest.php';

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
        $suite->addTestSuite('Braintree_TransactionTest');
        $suite->addTestSuite('Braintree_Error_ErrorCollectionTest');
        return $suite;
    }
}

?>
