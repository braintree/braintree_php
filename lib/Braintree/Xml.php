<?php

namespace Braintree;

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
final class Xml
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
        return Xml\Parser::arrayFromXml($xml);
    }

    /**
     *
     * @param array $array
     * @return string
     */
    public static function buildXmlFromArray($array)
    {
        return Xml\Generator::arrayToXml($array);
    }
}
