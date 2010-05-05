<?php

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_Xml_ParserTest extends PHPUnit_Framework_TestCase
{
    function testTypeCastIntegers()
    {
        $array = Braintree_Xml::buildArrayFromXml('<root><foo type="integer">123</foo></root>');
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

        $array = Braintree_Xml::buildArrayFromXml($xml);
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
        $array = Braintree_Xml::buildArrayFromXml($xml);
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
        $array = Braintree_Xml::buildArrayFromXml($xml);
        date_default_timezone_set('UTC');
        $dateTime = new DateTime('2009-10-28T10:19:49', new DateTimeZone('UTC'));
        $this->assertEquals(array('root' => array('createdAt' => $dateTime)), $array);
        $this->assertType('DateTime', $array['root']['createdAt']);
    }

    function testTypeCastsDates()
    {
        $xml = <<<XML
        <root>
          <some-date type="date">2009-10-28</some-date>
        </root>
XML;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        date_default_timezone_set('UTC');
        $dateTime = new DateTime('2009-10-28', new DateTimeZone('UTC'));
        $this->assertEquals(array('root' => array('someDate' => $dateTime)), $array);
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
        $array = Braintree_Xml::buildArrayFromXml($xml);
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
         $array = Braintree_Xml::buildArrayFromXml($xml);
         $this->assertEquals(
            array('root' =>
              array('castedTrue' => true,
                    'castedOne' => true,
                    'castedFalse' => false,
                    'castedAnything' => false,
                    'uncastedTrue' => 'true')
        ), $array);

    }

    function testEmptyArrayAndNestedElements()
    {
        $xml = <<<XML
        <root>
          <nested-values>
            <value>1</value>
          </nested-values>
          <no-values type="array"/>
        </root>
XML;

         $array = Braintree_Xml::buildArrayFromXml($xml);
         $this->assertEquals(
              array('root' => array(
                  'noValues' => array(),
                   'nestedValues' => array(
                       'value' => 1
                   )
              )
         ), $array);
    }

    function testParsingNilEqualsTrueAfterArray()
    {
        $xml = <<<XML
        <root>
          <customer>
            <first-name>Dan</first-name>
          </customer>
          <blank nil="true" />
        </root>
XML;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        $this->assertEquals(null, $array['root']['blank']);

    }

    function testTransactionParsingNil()
    {
        $xml = <<<XML
<transaction>
  <id>8ysndw</id>
  <status>settled</status>
  <type>sale</type>
  <currency>USD</currency>
  <amount>1.00</amount>
  <merchant-account-id>default</merchant-account-id>
  <order-id nil="true"></order-id>
  <created-at type="datetime">2010-04-01T19:32:23Z</created-at>
  <updated-at type="datetime">2010-04-02T08:05:35Z</updated-at>
  <customer>
    <id nil="true"></id>
    <first-name>First</first-name>
    <last-name>Last</last-name>
    <company nil="true"></company>
    <email></email>
    <website nil="true"></website>
    <phone nil="true"></phone>
    <fax nil="true"></fax>
  </customer>
  <billing>
    <id nil="true"></id>
    <first-name nil="true"></first-name>
    <last-name nil="true"></last-name>
    <company>Widgets Inc</company>
    <street-address>1234 My Street</street-address>
    <extended-address>Apt 1</extended-address>
    <locality>Ottawa</locality>
    <region>ON</region>
    <postal-code>K1C2N6</postal-code>
    <country-name>Canada</country-name>
  </billing>
  <refund-id nil="true"></refund-id>
  <shipping>
    <id nil="true"></id>
    <first-name nil="true"></first-name>
    <last-name nil="true"></last-name>
    <company nil="true"></company>
    <street-address nil="true"></street-address>
    <extended-address nil="true"></extended-address>
    <locality nil="true"></locality>
    <region nil="true"></region>
    <postal-code nil="true"></postal-code>
    <country-name nil="true"></country-name>
  </shipping>
  <custom-fields>
  </custom-fields>
  <avs-error-response-code nil="true"></avs-error-response-code>
  <avs-postal-code-response-code>M</avs-postal-code-response-code>
  <avs-street-address-response-code>M</avs-street-address-response-code>
  <cvv-response-code>M</cvv-response-code>
  <processor-authorization-code>13390</processor-authorization-code>
  <processor-response-code>1000</processor-response-code>
  <processor-response-text>Approved</processor-response-text>
  <credit-card>
    <token nil="true"></token>
    <bin>510510</bin>
    <last-4>5100</last-4>
    <card-type>MasterCard</card-type>
    <expiration-month>09</expiration-month>
    <expiration-year>2011</expiration-year>
    <customer-location>US</customer-location>
    <cardholder-name nil="true"></cardholder-name>
  </credit-card>
  <status-history type="array">
    <status-event>
      <timestamp type="datetime">2010-04-01T19:32:24Z</timestamp>
      <status>authorized</status>
      <amount>1.00</amount>
      <user>dmanges-am</user>
      <transaction-source>API</transaction-source>
    </status-event>
    <status-event>
      <timestamp type="datetime">2010-04-01T19:32:25Z</timestamp>
      <status>submitted_for_settlement</status>
      <amount>1.00</amount>
      <user>dmanges-am</user>
      <transaction-source>API</transaction-source>
    </status-event>
    <status-event>
      <timestamp type="datetime">2010-04-02T08:05:36Z</timestamp>
      <status>settled</status>
      <amount>1.00</amount>
      <user nil="true"></user>
      <transaction-source></transaction-source>
    </status-event>
  </status-history>
</transaction>
XML;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        $this->assertEquals(null, $array['transaction']['avsErrorResponseCode']);
        $this->assertEquals(null, $array['transaction']['refundId']);
        $this->assertEquals(null, $array['transaction']['orderId']);
        $this->assertEquals(null, $array['transaction']['customer']['fax']);
        $this->assertEquals(null, $array['transaction']['creditCard']['token']);
        $this->assertEquals(null, $array['transaction']['creditCard']['cardholderName']);
        $this->assertEquals('First', $array['transaction']['customer']['firstName']);
        $this->assertEquals('Approved', $array['transaction']['processorResponseText']);

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
