<?php
/**
 * Braintree base class and initialization
 *
 *  PHP version 5
 *
 * @copyright  2010 Braintree Payment Solutions
 */


set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)));

/**
 * Braintree PHP Library
 *
 * Provides methods to child classes. This class cannot be instantiated.
 *
 * @copyright  2010 Braintree Payment Solutions
 */
abstract class Braintree
{
    /**
     * @ignore
     * don't permit an explicit call of the constructor!
     * (like $t = new Braintree_Transaction())
     */
    protected function __construct()
    {
    }
    /**
     * @ignore
     *  don't permit cloning the instances (like $x = clone $v)
     */
    protected function __clone()
    {
    }

    /**
     * returns private/nonexistent instance properties
     * @ignore
     * @access public
     * @param string $name property name
     * @return mixed contents of instance properties
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        }
        else {
            trigger_error('Undefined property on ' . get_class($this) . ': ' . $name, E_USER_NOTICE);
            return null;
        }
    }

    /**
     * used by isset() and empty()
     * @access public
     * @param string $name property name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    public function _set($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

    /**
     *
     * @param string $className
     * @param object $resultObj
     * @return object returns the passed object if successful
     * @throws Braintree_Exception_ValidationsFailed
     */
    public static function returnObjectOrThrowException($className, $resultObj)
    {
        $resultObjName = Braintree_Util::cleanClassName($className);
        if ($resultObj->success) {
            return $resultObj->$resultObjName;
        } else {
            throw new Braintree_Exception_ValidationsFailed();
        }
    }
}
require_once('Braintree/Modification.php');
require_once('Braintree/Instance.php');

require_once('Braintree/Address.php');
require_once('Braintree/AddOn.php');
require_once('Braintree/Collection.php');
require_once('Braintree/Configuration.php');
require_once('Braintree/CreditCard.php');
require_once('Braintree/Customer.php');
require_once('Braintree/CustomerSearch.php');
require_once('Braintree/DisbursementDetails.php');
require_once('Braintree/Descriptor.php');
require_once('Braintree/Digest.php');
require_once('Braintree/Discount.php');
require_once('Braintree/IsNode.php');
require_once('Braintree/EqualityNode.php');
require_once('Braintree/Exception.php');
require_once('Braintree/Http.php');
require_once('Braintree/KeyValueNode.php');
require_once('Braintree/MultipleValueNode.php');
require_once('Braintree/MultipleValueOrTextNode.php');
require_once('Braintree/PartialMatchNode.php');
require_once('Braintree/Plan.php');
require_once('Braintree/RangeNode.php');
require_once('Braintree/ResourceCollection.php');
require_once('Braintree/SettlementBatchSummary.php');
require_once('Braintree/Subscription.php');
require_once('Braintree/SubscriptionSearch.php');
require_once('Braintree/SubscriptionStatus.php');
require_once('Braintree/TextNode.php');
require_once('Braintree/Transaction.php');
require_once('Braintree/TransactionSearch.php');
require_once('Braintree/TransparentRedirect.php');
require_once('Braintree/Util.php');
require_once('Braintree/Version.php');
require_once('Braintree/Xml.php');
require_once('Braintree/Error/Codes.php');
require_once('Braintree/Error/ErrorCollection.php');
require_once('Braintree/Error/Validation.php');
require_once('Braintree/Error/ValidationErrorCollection.php');
require_once('Braintree/Exception/Authentication.php');
require_once('Braintree/Exception/Authorization.php');
require_once('Braintree/Exception/Configuration.php');
require_once('Braintree/Exception/DownForMaintenance.php');
require_once('Braintree/Exception/ForgedQueryString.php');
require_once('Braintree/Exception/InvalidSignature.php');
require_once('Braintree/Exception/NotFound.php');
require_once('Braintree/Exception/ServerError.php');
require_once('Braintree/Exception/SSLCertificate.php');
require_once('Braintree/Exception/SSLCaFileNotFound.php');
require_once('Braintree/Exception/Unexpected.php');
require_once('Braintree/Exception/UpgradeRequired.php');
require_once('Braintree/Exception/ValidationsFailed.php');
require_once('Braintree/Result/CreditCardVerification.php');
require_once('Braintree/Result/Error.php');
require_once('Braintree/Result/Successful.php');
require_once('Braintree/Test/CreditCardNumbers.php');
require_once('Braintree/Test/TransactionAmounts.php');
require_once('Braintree/Test/VenmoSdk.php');
require_once('Braintree/Transaction/AddressDetails.php');
require_once('Braintree/Transaction/CreditCardDetails.php');
require_once('Braintree/Transaction/CustomerDetails.php');
require_once('Braintree/Transaction/StatusDetails.php');
require_once('Braintree/Transaction/SubscriptionDetails.php');
require_once('Braintree/WebhookNotification.php');
require_once('Braintree/WebhookTesting.php');
require_once('Braintree/Xml/Generator.php');
require_once('Braintree/Xml/Parser.php');
require_once('Braintree/CreditCardVerification.php');
require_once('Braintree/CreditCardVerificationSearch.php');

if (version_compare(PHP_VERSION, '5.2.1', '<')) {
    throw new Braintree_Exception('PHP version >= 5.2.1 required');
}


function requireDependencies() {
    $requiredExtensions = array('xmlwriter', 'SimpleXML', 'openssl', 'dom', 'hash', 'curl');
    foreach ($requiredExtensions AS $ext) {
        if (!extension_loaded($ext)) {
            throw new Braintree_Exception('The Braintree library requires the ' . $ext . ' extension.');
        }
    }
}

requireDependencies();
