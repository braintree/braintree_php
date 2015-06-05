<?php namespace Braintree;

use Braintree\Xml\Generator;
use Braintree\Xml\Parser;

/**
 * Braintree Xml parser and generator
 * PHP version 5
 * superclass for Braintree XML parsing and generation
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
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
        return Parser::arrayFromXml($xml);
    }

    /**
     *
     * @param array $array
     * @return string
     */
    public static function buildXmlFromArray($array)
    {
        return Generator::arrayToXml($array);
    }
}
