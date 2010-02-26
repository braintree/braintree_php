<?php

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_Xml_ParserTest extends PHPUnit_Framework_TestCase
{
    function testTypeCastIntegers()
    {
        $array = Braintree_XML::buildArrayFromXml('<root><foo type="integer">123</foo></root>');
        $this->assertEquals($array, array('root' => array('foo' => 123)));

    }

    function testDashesUnderscores()
    {
        $xml =<<<XML
        <root>
          <dash-es />
          <under_scores />
        </root>
XML;

        $array = Braintree_XML::buildArrayFromXml($xml);
        $this->assertEquals(array('root' =>
            array('dashEs' => '', 'underScores' => '')), $array);
    }

    function testNullOrEmptyString()
    {
        $xml = <<<XML
        <root>
          <a_nil_value nil="true"></a_nil_value>
          <an_empty_string></an_empty_string>
        </root>
XML;
        $array = Braintree_XML::buildArrayFromXml($xml);
        $this->assertEquals(array('root' =>
            array('aNilValue' => null, 'anEmptyString' => '')), $array);
        
    }

    function testTypeCastsDatetimes()
    {
        $xml = <<<XML
        <root>
          <created-at type="datetime">2009-10-28T10:19:49Z</created-at>
        </root>
XML;
        $array = Braintree_XML::buildArrayFromXml($xml);
        date_default_timezone_set('UTC');
        $dateTime = date('D M d H:i:s e Y', strtotime('2009-10-28T10:19:49Z'));
        $this->assertEquals($array, array('root' => array('createdAt' => $dateTime)));
  
    }

    function testBuildsArray()
    {
        $xml = <<<XML
        <root>
          <customers type="array">
            <customer><name>Adam</name></customer>
            <customer><name>Ben</name></customer>
          </customers>
        </root>
XML;
        $array = Braintree_XML::buildArrayFromXml($xml);
        $this->assertEquals(array('root' =>
            array('customers' =>
                    array(array('name' => 'Adam'),
                           array('name' => 'Ben'))
                    )
            ), $array
        );

    }

    function testReturnsBoolean()
    {
        $xml = <<<XML
        <root>
          <casted-true type="boolean">true</casted-true>
          <casted-one type="boolean">1</casted-one>
          <casted-false type="boolean">false</casted-false>
          <casted-anything type="boolean">anything</casted-anything>
          <uncasted-true>true</uncasted-true>
        </root>
XML;
         $array = Braintree_XML::buildArrayFromXml($xml);
         $this->assertEquals(
            array('root' =>
              array('castedTrue' => true,
                    'castedOne' => true,
                    'castedFalse' => false,
                    'castedAnything' => false,
                    'uncastedTrue' => 'true')
        ), $array);

    }

    function xmlAndBack($array)
    {
        $xml = Braintree_Xml::buildXmlFromArray($array);
        return Braintree_Xml::buildArrayFromXml($xml);

    }

    function testSimpleCaseRoundtrip()
    {
        $array = array('root' => array(
            'foo' => 'fooValue',
            'bar' => 'barValue')
            );

        $array2 = $this->xmlAndBack($array);
        $this->assertEquals($array, $array2);
    }

    function testArrayRoundtrip()
    {
        $array = array('root' => array (
            'items' => array(
                array('name' => 'first'),
                array('name' => 'second'),
            )
        ));
        $array2 = $this->xmlAndBack($array);
        $this->assertEquals($array, $array2);
    }

    function testBooleanRoundtrip()
    {
        $array = array('root' => array(
            'stringTrue' => true,
            'boolTrue' => true,
            'stringFalse' => false,
            'boolFalse' => false,
        ));
        $array2 = $this->xmlAndBack($array);
        $this->assertEquals($array, $array2);

    }
    function testTimestampRoundtrip()
    {
        date_default_timezone_set('UTC');
        $array = array('root' => array(
           'aTimestamp' => date('D M d H:i:s e Y', mktime(1, 2, 3, 10, 28, 2009)),
        ));
        $array2 = $this->xmlAndBack($array);
        $this->assertEquals($array, $array2);

    }

    function testNullvsEmptyStringToXml()
    {
        $array = array('root' => array(
            'anEmptyString' => '',
            'aNullValue' => null,
            ));
        $xml = Braintree_Xml::buildXmlFromArray($array);
        $xml2 =<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
 <an-empty-string></an-empty-string>
 <a-null-value nil="true"></a-null-value>
</root>

XML;

        $this->assertEquals($xml, $xml2);
    }
    
    function testIncludesTheEncodingRoundtrip()
    {
        $array = array('root' => array(
           'root' => 'bar',
        ));
        $xml = Braintree_Xml::buildXmlFromArray($array);
        $this->assertRegExp('<\?xml version=\"1.0\" encoding=\"UTF-8\"\?>', $xml);

    }
    
    function testRootNodeAndStringRoundtrip()
    {
        $array = array('id' => '123');
        $array2 = $this->xmlAndBack($array);
        $this->assertEquals($array, $array2);
    }
}
?>
