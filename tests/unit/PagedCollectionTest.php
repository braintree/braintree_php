<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_PagedCollectionTest extends PHPUnit_Framework_TestCase
{
    function testPageWithZeroResults()
    {
        $collection = new Braintree_PagedCollection(array(
            "items" => array(),
            "currentPageNumber" => 1,
            "pageSize" => 50,
            "totalItems" => 0
        ), array(
            "className" => "Transaction",
            "classMethod" => "search",
            "methodArgs" => array()
            ));


        $this->assertEquals(0, $collection->_approximateCount());
        $this->assertNull($collection->firstItem());
    }

    function testFirstItemWithResults()
    {
        $collection = new Braintree_PagedCollection(array(
            "items" => array("one"),
            "currentPageNumber" => 1,
            "pageSize" => 50,
            "totalItems" => 1
        ), array(
            "className" => "Transaction",
            "classMethod" => "search",
            "methodArgs" => array()
            ));


        $this->assertEquals(1, $collection->_approximateCount());
        $this->assertEquals("one", $collection->firstItem());
    }
}
?>
