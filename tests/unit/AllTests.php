<?php

require_once 'AddressTest.php';
require_once 'BraintreeTest.php';
require_once 'ConfigurationTest.php';
require_once 'CreditCardTest.php';
require_once 'CustomerTest.php';
require_once 'DigestTest.php';
require_once 'TransactionTest.php';
require_once 'TransparentRedirectTest.php';
require_once 'Xml_GeneratorTest.php';
require_once 'Xml_ParserTest.php';
require_once 'UtilTest.php';

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
        $suite->addTestSuite('Braintree_BraintreeTest');
        $suite->addTestSuite('Braintree_ConfigurationTest');
        $suite->addTestSuite('Braintree_CustomerTest');
        $suite->addTestSuite('Braintree_CreditCardTest');
        $suite->addTestSuite('Braintree_DigestTest');
        $suite->addTestSuite('Braintree_TransactionTest');
        $suite->addTestSuite('Braintree_TransparentRedirectTest');
        $suite->addTestSuite('Braintree_UtilTest');
        $suite->addTestSuite('Braintree_Xml_GeneratorTest');
        $suite->addTestSuite('Braintree_Xml_ParserTest');
        return $suite;
    }
}

// Braintree_AllTests::main();

?>
