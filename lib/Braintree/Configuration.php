<?php
/**
 *
 * Configuration registry
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

class Braintree_Configuration
{
    public static $global;

    private $_environment = null;
    private $_merchantId = null;
    private $_publicKey = null;
    private $_privateKey = null;

    /**
     * Braintree API version to use
     * @access public
     */
     const API_VERSION =  4;

    public function __construct($attribs = array())
    {
        foreach ($attribs as $kind => $value) {
            if ($kind == 'environment') {
                $this->setEnvironment($value);
            }
            if ($kind == 'merchantId') {
                $this->setMerchantId($value);
            }
            if ($kind == 'publicKey') {
                $this->setPublicKey($value);
            }
            if ($kind == 'privateKey') {
                $this->setPrivateKey($value);
            }
        }
    }

    /**
     * resets configuration to default
     * @access public
     */
    public static function reset()
    {
        self::$global = new Braintree_Configuration();
    }

    public static function gateway()
    {
        return new Braintree_Gateway(self::$global);
    }

    public function http()
    {
        return new Braintree_Http($this);
    }

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
    public static function environment($value=null)
    {
        if (empty($value)) {
            return self::$global->getEnvironment();
        }
        self::$global->setEnvironment($value);
    }

    public static function merchantId($value=null)
    {
        if (empty($value)) {
            return self::$global->getMerchantId();
        }
        self::$global->setMerchantId($value);
    }

    public static function publicKey($value=null)
    {
        if (empty($value)) {
            return self::$global->getPublicKey();
        }
        self::$global->setPublicKey($value);
    }

    public static function privateKey($value=null)
    {
        if (empty($value)) {
            return self::$global->getPrivateKey();
        }
        self::$global->setPrivateKey($value);
    }

    public function assertValid()
    {
        if (empty($this->_environment)) {
            throw new Braintree_Exception_Configuration('environment needs to be set.');
        } else if (empty($this->_merchantId)) {
            throw new Braintree_Exception_Configuration('merchantId needs to be set.');
        } else if (empty($this->_publicKey)) {
            throw new Braintree_Exception_Configuration('publicKey needs to be set.');
        } else if (empty($this->_privateKey)) {
            throw new Braintree_Exception_Configuration('privateKey needs to be set.');
        }
    }


    public function getEnvironment()
    {
        return $this->_environment;
    }

    public function setEnvironment($value)
    {
        if (!in_array($value, self::$_validEnvironments)) {
            throw new Braintree_Exception_Configuration('"' .
                                    $value . '" is not a valid environment.');
        }
        $this->_environment = $value;
    }

    public function getMerchantId()
    {
        return $this->_merchantId;
    }

    public function setMerchantId($value)
    {
        $this->_merchantId = $value;
    }

    public function getPublicKey()
    {
        return $this->_publicKey;
    }

    public function setPublicKey($value)
    {
        $this->_publicKey = $value;
    }

    public function getPrivateKey()
    {
        return $this->_privateKey;
    }

    public function setPrivateKey($value)
    {
        $this->_privateKey = $value;
    }

    /**
     * returns the full merchant URL based on config values
     *
     * @access public
     * @param none
     * @return string merchant URL
     */
    public function merchantUrl()
    {
        return $this->baseUrl() .
               $this->merchantPath();
    }

    /**
     * returns the base braintree gateway URL based on config values
     *
     * @access public
     * @param none
     * @return string braintree gateway URL
     */
    public function baseUrl()
    {
        return $this->protocol() . '://' .
                  $this->serverName() . ':' .
                  $this->portNumber();
    }

    /**
     * sets the merchant path based on merchant ID
     *
     * @access protected
     * @param none
     * @return string merchant path uri
     */
    public function merchantPath()
    {
        return '/merchants/'.$this->_merchantId;
    }

    /**
     * sets the physical path for the location of the CA certs
     *
     * @access public
     * @param none
     * @return string filepath
     */
    public function caFile($sslPath = NULL)
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
     * @param none
     * @return int portnumber
     */
    public function portNumber()
    {
        if ($this->sslOn()) {
            return 443;
        }
        return getenv("GATEWAY_PORT") ? getenv("GATEWAY_PORT") : 3000;
    }

    /**
     * returns http protocol depending on environment
     *
     * @access public
     * @param none
     * @return string http || https
     */
    public function protocol()
    {
        return $this->sslOn() ? 'https' : 'http';
    }

    /**
     * returns gateway server name depending on environment
     *
     * @access public
     * @param none
     * @return string server domain name
     */
    public function serverName()
    {
        switch($this->_environment) {
         case 'production':
             $serverName = 'api.braintreegateway.com';
             break;
         case 'qa':
             $serverName = 'gateway.qa.braintreepayments.com';
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

    public function authUrl()
    {
        switch($this->_environment) {
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
     * @param none
     * @return boolean
     */
    public function sslOn()
    {
        switch($this->_environment) {
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
    public function logMessage($message)
    {
        error_log('[Braintree] ' . $message);
    }
}
