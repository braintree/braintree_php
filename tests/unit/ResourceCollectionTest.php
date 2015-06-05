<?php namespace Braintree\Tests\Unit;

use Braintree\ResourceCollection;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class TestResource
{
    public function lookup($id)
    {
        return ResourceCollectionTest::$values[intval($id)];
    }

    public function fetch($ids)
    {

        return array_map("\\BrainTree\\Tests\\Unit\\TestResource::lookup", $ids);
    }
}

class ResourceCollectionTest extends \PHPUnit_Framework_TestCase
{
    public static $values = array("a", "b", "c", "d", "e");

    function testIterateOverResults()
    {

        $response = array(
            'searchResults' => array(
                'pageSize' => 2,
                'ids'      => array('0', '1', '2', '3', '4')
            )
        );

        $object = new TestResource();
        $pager = array(
            'object'     => $object,
            'method'     => 'fetch',
            'methodArgs' => array()
        );

        $collection = new ResourceCollection($response, $pager);

        $count = 0;
        $index = 0;
        foreach ($collection as $value) {
            $this->assertEquals(ResourceCollectionTest::$values[$index], $value);
            $index += 1;
            $count += 1;
        }

        $this->assertEquals(5, $count);
    }

    function testDoesntIterateWhenNoResults()
    {

        $response = array(
            'searchResults' => array(
                'pageSize' => 2,
                'ids'      => array()
            )
        );

        $object = new TestResource();
        $pager = array(
            'object'     => $object,
            'method'     => 'fetch',
            'methodArgs' => array()
        );

        $collection = new ResourceCollection($response, $pager);

        $count = 0;
        $index = 0;
        foreach ($collection as $value) {
            $index += 1;
            $count += 1;
            break;
        }

        $this->assertEquals(0, $count);
        $this->assertEquals(0, $index);
    }
}
