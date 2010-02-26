<?php
/**
 * Braintree SSL Expiration Check
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2010 Braintree Payment Solutions
 */

/**
 * checks for expired ssl certificate
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2010 Braintree Payment Solutions
 * @property-read boolean $sslExpirationDatesChecked
 */
class Braintree_SSLExpirationCheck
{
    private static $_sslExpirationDatesChecked;

    public function __get($name) {
        $varName = "_$name";
        return $this->$varName;
    }
    public static function checkDates()
    {
        date_default_timezone_set('UTC');

        $dates = array(
            'QA' => self::qaExpirationDate(),
            'Sandbox' => self::sandboxExpirationDate(),
            );
        foreach ($dates AS $host => $expirationDate) {
            if (date('Y-m-d', time('+90 days')) > $expirationDate) {
                Braintree_Configuration::logMessage(
                        "The SSL Certificate for the $host environment " .
                        "will expire on $expirationDate. " .
                        "Please check for an updated client library.");
            }
        }
        self::$_sslExpirationDatesChecked = true;
    }
    private static function sandboxExpirationDate()
    {
        return date('Y-m-d', strtotime('2010-12-1'));
    }
    private static function qaExpirationDate()
    {
        return date('Y-m-d', strtotime('2010-12-1'));
    }
}
?>
