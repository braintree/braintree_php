<?php namespace Braintree\Xml;

    /**
     * PHP version 5
     *
     * @copyright  2014 Braintree, a division of PayPal, Inc.
     */
use Braintree\Util;
use XMLWriter;

/**
 * Generates XML output from arrays using PHP's
 * built-in XMLWriter
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Generator
{
    /**
     * arrays passed to this method should have a single root element
     * with an array as its value
     *
     * @param array $aData the array of data
     * @return var XML string
     */
    public static function arrayToXml($aData)
    {
        $aData = Util::camelCaseToDelimiterArray($aData, '-');
        // set up the XMLWriter
        $writer = new XMLWriter();
        $writer->openMemory();

        $writer->setIndent(true);
        $writer->setIndentString(' ');
        $writer->startDocument('1.0', 'UTF-8');

        // get the root element name
        $aKeys = array_keys($aData);
        $rootElementName = $aKeys[0];
        // open the root element
        $writer->startElement($rootElementName);
        // create the body
        self::_createElementsFromArray($writer, $aData[$rootElementName], $rootElementName);

        // close the root element and document
        $writer->endElement();
        $writer->endDocument();

        // send the output as string
        return $writer->outputMemory();
    }

    /**
     * Construct XML elements with attributes from an associative array.
     *
     * @access protected
     * @static
     * @param object $writer XMLWriter object
     * @param array $aData contains attributes and values
     * @return none
     */
    private static function _createElementsFromArray(&$writer, $aData)
    {
        if (!is_array($aData)) {
            if (is_bool($aData)) {
                $writer->text($aData ? 'true' : 'false');
            } else {
                $writer->text($aData);
            }
            return;
        }
        foreach ($aData AS $elementName => $element) {
            // handle child elements
            $writer->startElement($elementName);
            if (is_array($element)) {
                if (array_key_exists(0, $element) || empty($element)) {
                    $writer->writeAttribute('type', 'array');
                    foreach ($element AS $ignored => $itemInArray) {
                        $writer->startElement('item');
                        self::_createElementsFromArray($writer, $itemInArray);
                        $writer->endElement();
                    }
                } else {
                    self::_createElementsFromArray($writer, $element);
                }
            } else {
                // generate attributes as needed
                $attribute = self::_generateXmlAttribute($element);
                if (is_array($attribute)) {
                    $writer->writeAttribute($attribute[0], $attribute[1]);
                    $element = $attribute[2];
                }
                $writer->text($element);
            }
            $writer->endElement();
        }
    }

    /**
     * convert passed data into an array of attributeType, attributeName, and value
     * dates sent as \DateTime objects will be converted to strings
     *
     * @access protected
     * @param mixed $value
     * @return array attributes and element value
     */
    private static function _generateXmlAttribute($value)
    {
        if ($value instanceof \DateTime) {
            return array('type', '\DateTime', self::_DateTimeToXmlTimestamp($value));
        }
        if (is_int($value)) {
            return array('type', 'integer', $value);
        }
        if (is_bool($value)) {
            return array('type', 'boolean', ($value ? 'true' : 'false'));
        }
        if ($value === null) {
            return array('nil', 'true', $value);
        }
    }

    /**
     * converts \DateTime back to xml schema format
     *
     * @access protected
     * @param object $DateTime
     * @return var XML schema formatted timestamp
     */
    private static function _DateTimeToXmlTimestamp($DateTime)
    {
        $DateTime->setTimeZone(new \DateTimeZone('UTC'));
        return ($DateTime->format('Y-m-d\TH:i:s') . 'Z');
    }

    private static function _castDateTime($string)
    {
        try {
            if (empty($string)) {
                return false;
            }
            $DateTime = new \DateTime($string);
            return self::_DateTimeToXmlTimestamp($DateTime);
        } catch (\Exception $e) {
            // not a \DateTime
            return false;
        }
    }
}
