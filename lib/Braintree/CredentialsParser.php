<?php
namespace Braintree;
use Braintree\Logging\CurlAuditLoggerInterface;

/**
 *
 * CredentialsParser registry
 *
 * @package    Braintree
 * @subpackage Utility
 * @copyright  2015 Braintree, a division of PayPal, Inc.
 */

class CredentialsParser
{
    private $_clientId;
    private $_clientSecret;
    private $_accessToken;
    private $_environment;
    private $_merchantId;
    private $_logger;

    public function __construct($attribs)
    {
        foreach ($attribs as $kind => $value) {
            if ($kind == 'clientId') {
                $this->_clientId = $value;
            }
            if ($kind == 'clientSecret') {
                $this->_clientSecret = $value;
            }
            if ($kind == 'accessToken') {
                $this->_accessToken = $value;
            }
            if ($kind == 'logger') {
                $this->_logger = $value;
            }
        }
        $this->parse();
    }

    /**
     *
     * @access protected
     * @static
     * @var array valid environments, used for validation
     */
    private static $_validEnvironments = [
        'development',
        'integration',
        'sandbox',
        'production',
        'qa',
    ];


    public function parse()
    {
        $environments = [];
        if (!empty($this->_clientId)) {
            $environments[] = ['clientId', $this->_parseClientCredential('clientId', $this->_clientId, 'client_id')];
        }
        if (!empty($this->_clientSecret)) {
            $environments[] = ['clientSecret', $this->_parseClientCredential('clientSecret', $this->_clientSecret, 'client_secret')];
        }
        if (!empty($this->_accessToken)) {
            $environments[] = ['accessToken', $this->_parseAccessToken()];
        }
        if (!empty($this->_logger)) {
            $environments[] = ['logger', $this->_parseLogger()];
        }

        $checkEnv = $environments[0];
        foreach ($environments as $env) {
            if ($env[1] !== $checkEnv[1]) {
                throw new Exception\Configuration(
                    'Mismatched credential environments: ' . $checkEnv[0] . ' environment is ' . $checkEnv[1] .
                    ' and ' . $env[0] . ' environment is ' . $env[1]);
            }
        }

        self::assertValidEnvironment($checkEnv[1]);
        $this->_environment = $checkEnv[1];
    }

    public static function assertValidEnvironment($environment) {
        if (!in_array($environment, self::$_validEnvironments)) {
            throw new Exception\Configuration('"' .
                                    $environment . '" is not a valid environment.');
        }
    }

    private function _parseClientCredential($credentialType, $value, $expectedValuePrefix)
    {
        $explodedCredential = explode('$', $value);
        if (sizeof($explodedCredential) != 3) {
            throw new Exception\Configuration('Incorrect ' . $credentialType . ' format. Expected: type$environment$token');
        }

        $gotValuePrefix = $explodedCredential[0];
        $environment = $explodedCredential[1];
        $token = $explodedCredential[2];

        if ($gotValuePrefix != $expectedValuePrefix) {
            throw new Exception\Configuration('Value passed for ' . $credentialType . ' is not a ' . $credentialType);
        }

        return $environment;
    }

    private function _parseAccessToken()
    {
        $accessTokenExploded = explode('$', $this->_accessToken);
        if (sizeof($accessTokenExploded) != 4) {
            throw new Exception\Configuration('Incorrect accessToken syntax. Expected: type$environment$merchant_id$token');
        }

        $gotValuePrefix = $accessTokenExploded[0];
        $environment = $accessTokenExploded[1];
        $merchantId = $accessTokenExploded[2];
        $token = $accessTokenExploded[3];

        if ($gotValuePrefix != 'access_token') {
            throw new Exception\Configuration('Value passed for accessToken is not an accessToken');
        }

        $this->_merchantId = $merchantId;
        return $environment;
    }

    private function _parseLogger()
    {
        $loggerExploded = explode('$', $this->_logger);
        $logger = $loggerExploded[0];
        if (!$logger instanceof CurlAuditLoggerInterface) {
            throw new Exception\Configuration('Logger is not of type CurlAuditLoggerInterface');
        }
        $this->_logger = $logger;
        return $logger;
    }

    public function getClientId()
    {
        return $this->_clientId;
    }

    public function getClientSecret()
    {
        return $this->_clientSecret;
    }

    public function getAccessToken()
    {
        return $this->_accessToken;
    }

    public function getEnvironment()
    {
        return $this->_environment;
    }

    public function getMerchantId()
    {
        return $this->_merchantId;
    }

    public function getLogger()
    {
        return $this->_logger;
    }
}
class_alias('Braintree\CredentialsParser', 'Braintree_CredentialsParser');
