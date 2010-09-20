<?php
/**
 * Braintree Xml parser and generator
 * PHP version 5
 *
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * superclass for Braintree XML parsing and generation
 *
 * @copyright  2010 Braintree Payment Solutions
 */
final class Braintree_Xml
{
    /**
     * @ignore
     */
    protected function  __construct()
    {

    }

    /**
     * 
     * @param string $xml
     * @return array
     */
    public static function buildArrayFromXml($xml)
    {
        return Braintree_Xml_Parser::arrayFromXml($xml);
    }

    /**
     *
     * @param array $array
     * @return string
     */
    public static function buildXmlFromArray($array)
    {
        return Braintree_Xml_Generator::arrayToXml($array);
    }
}
