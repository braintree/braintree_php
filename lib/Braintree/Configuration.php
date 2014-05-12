<?php
/**
 *
 * Configuration registry
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

/**
 * acts as a registry for config data.
 *
 *
 * @package    Braintree
 * @subpackage Utility
 *
 *  */

class Braintree_Configuration extends Braintree
{
    /**
     * Braintree API version to use
     * @access public
     */
     const API_VERSION =  3;

    /**
     * @var array array of config properties
     * @access protected
     * @static
     */
    private static $_cache = array(
                    'environment'   => '',
                    'merchantId'    => '',
                    'publicKey'     => '',
                    'privateKey'    => '',
                   );
    /**
     *
     * @access protected
     * @static
     * @var array valid environments, used for validation
     */
    private static $_validEnvironments = array(
                    'development',
                    'sandbox',
                    'production',
                    'qa',
                    );

    /**
     * resets configuration to default
     * @access public
     * @static
     */
    public static function reset()
    {
        self::$_cache = array (
            'environment' => '',
            'merchantId'  => '',
            'publicKey' => '',
            'privateKey' => '',
        );
    }

    /**
     * performs sanity checks when config settings are being set
     *
     * @ignore
     * @access protected
     * @param string $key name of config setting
     * @param string $value value to set
     * @throws InvalidArgumentException
     * @throws Braintree_Exception_Configuration
     * @static
     * @return boolean
     */
    private static function validate($key=null, $value=null)
    {
        if (empty($key) && empty($value)) {
             throw new InvalidArgumentException('nothing to validate');
        }

        if ($key === 'environment' &&
           !in_array($value, self::$_validEnvironments) ) {
            throw new Braintree_Exception_Configuration('"' .
                                    $value . '" is not a valid environment.');
        }

        if (!isset(self::$_cache[$key])) {
             throw new Braintree_Exception_Configuration($key .
                                    ' is not a valid configuration setting.');
        }

        if (empty($value)) {
             throw new InvalidArgumentException($key . ' cannot be empty.');
        }

        return true;
    }

    private static function set($key, $value)
    {
        // this method will raise an exception on invalid data
        self::validate($key, $value);
        // set the value in the cache
        self::$_cache[$key] = $value;

    }

    private static function get($key)
    {
        // throw an exception if the value hasn't been set
        if (isset(self::$_cache[$key]) &&
           (empty(self::$_cache[$key]))) {
            throw new Braintree_Exception_Configuration(
                      $key.' needs to be set.'
                      );
        }

        if (array_key_exists($key, self::$_cache)) {
            return self::$_cache[$key];
        }

        // return null by default to prevent __set from overloading
        return null;
    }


    private static function setOrGet($name, $value = null)
    {
        if (!empty($value) && is_array($value)) {
            $value = $value[0];
        }
        if (!empty($value)) {
            self::set($name, $value);
        } else {
            return self::get($name);
        }
        return true;
    }
    /**#@+
     * sets or returns the property after validation
     * @access public
     * @static
     * @param string $value pass a string to set, empty to get
     * @return mixed returns true on set
     */
    public static function environment($value = null)
    {
        return self::setOrGet(__FUNCTION__, $value);
    }

    public static function merchantId($value = null)
    {
        return self::setOrGet(__FUNCTION__, $value);
    }

    public static function publicKey($value = null)
    {
        return self::setOrGet(__FUNCTION__, $value);
    }

    public static function privateKey($value = null)
    {
        return self::setOrGet(__FUNCTION__, $value);
    }
    /**#@-*/

    /**
     * returns the full merchant URL based on config values
     *
     * @access public
     * @static
     * @param none
     * @return string merchant URL
     */
    public static function merchantUrl()
    {
        return self::baseUrl() .
               self::merchantPath();
    }

    /**
     * returns the base braintree gateway URL based on config values
     *
     * @access public
     * @static
     * @param none
     * @return string braintree gateway URL
     */
    public static function baseUrl()
    {
        return self::protocol() . '://' .
                  self::serverName() . ':' .
                  self::portNumber();
    }

    /**
     * sets the merchant path based on merchant ID
     *
     * @access protected
     * @static
     * @param none
     * @return string merchant path uri
     */
    public static function merchantPath()
    {
        return '/merchants/'.self::merchantId();
    }

    /**
     * sets the physical path for the location of the CA certs
     *
     * @access public
     * @static
     * @param none
     * @return string filepath
     */
    public static function caFile($sslPath = NULL)
    {
        $sslPath = $sslPath ? $sslPath : DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
                   'ssl' . DIRECTORY_SEPARATOR;

        $caPath = realpath(
            dirname(__FILE__) .
            $sslPath .  'api_braintreegateway_com.ca.crt'
        );

        if (!file_exists($caPath))
        {
            throw new Braintree_Exception_SSLCaFileNotFound();
        }

        return $caPath;
    }

    /**
     * returns the port number depending on environment
     *
     * @access public
     * @static
     * @param none
     * @return int portnumber
     */
    public static function portNumber()
    {
        if (self::sslOn()) {
            return 443;
        }
        return getenv("GATEWAY_PORT") ? getenv("GATEWAY_PORT") : 3000;
    }

    /**
     * returns http protocol depending on environment
     *
     * @access public
     * @static
     * @param none
     * @return string http || https
     */
    public static function protocol()
    {
        return self::sslOn() ? 'https' : 'http';
    }

    /**
     * returns gateway server name depending on environment
     *
     * @access public
     * @static
     * @param none
     * @return string server domain name
     */
    public static function serverName()
    {
        switch(self::environment()) {
         case 'production':
             $serverName = 'api.braintreegateway.com';
             break;
         case 'qa':
             $serverName = 'qa.braintreegateway.com';
             break;
         case 'sandbox':
             $serverName = 'api.sandbox.braintreegateway.com';
             break;
         case 'development':
         default:
             $serverName = 'localhost';
             break;
        }

        return $serverName;
    }

    public static function authUrl()
    {
        switch(self::environment()) {
         case 'production':
             $serverName = 'https://auth.venmo.com';
             break;
         case 'qa':
             $serverName = 'https://auth.qa.venmo.com';
             break;
         case 'sandbox':
             $serverName = 'https://auth.sandbox.venmo.com';
             break;
         case 'development':
         default:
             $serverName = 'http://auth.venmo.dev:9292';
             break;
        }

        return $serverName;
    }

    /**
     * returns boolean indicating SSL is on or off for this session,
     * depending on environment
     *
     * @access public
     * @static
     * @param none
     * @return boolean
     */
    public static function sslOn()
    {
        switch(self::environment()) {
         case 'development':
             $ssl = false;
             break;
         case 'production':
         case 'qa':
         case 'sandbox':
         default:
             $ssl = true;
             break;
        }

       return $ssl;
    }

    /**
     * log message to default logger
     *
     * @param string $message
     *
     */
    public static function logMessage($message)
    {
        error_log('[Braintree] ' . $message);
    }

}
