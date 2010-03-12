<?php

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_Xml_GeneratorTest extends PHPUnit_Framework_TestCase
{
    function testSetsTypeAttributeForBooleans()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
 <yes type="boolean">true</yes>
 <no type="boolean">false</no>
</root>

XML;
        $xml = Braintree_Xml::buildXmlFromArray(array(
            'root' => array('yes' => true, 'no' => false)
        ));
        $this->assertEquals($expected, $xml);
    }
}
