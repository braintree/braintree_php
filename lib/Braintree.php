<?php
/**
 * Braintree PHP Library
 * Creates class_aliases for old class names replaced by PSR-4 Namespaces
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */

require_once('autoload.php');

class_alias('Braintree\ThreeDSecureInfo', 'Braintree_ThreeDSecureInfo');
class_alias('Braintree\SubscriptionSearch', 'Braintree_SubscriptionSearch');
class_alias('Braintree\TextNode', 'Braintree_TextNode');
class_alias('Braintree\Transaction', 'Braintree_Transaction');
class_alias('Braintree\TransactionGateway', 'Braintree_TransactionGateway');
class_alias('Braintree\TransactionSearch', 'Braintree_TransactionSearch');
class_alias('Braintree\TransparentRedirect', 'Braintree_TransparentRedirect');
class_alias('Braintree\TransparentRedirectGateway', 'Braintree_TransparentRedirectGateway');
class_alias('Braintree\Util', 'Braintree_Util');
class_alias('Braintree\Version', 'Braintree_Version');
class_alias('Braintree\Xml', 'Braintree_Xml');
class_alias('Braintree\WebhookNotification', 'Braintree_WebhookNotification');
class_alias('Braintree\WebhookTesting', 'Braintree_WebhookTesting');
class_alias('Braintree\UnknownPaymentMethod', 'Braintree_UnknownPaymentMethod');
class_alias('Braintree\TestingGateway', 'Braintree_TestingGateway');

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    throw new Braintree_Exception('PHP version >= 5.4.0 required');
}


function requireDependencies() {
    $requiredExtensions = array('xmlwriter', 'openssl', 'dom', 'hash', 'curl');
    foreach ($requiredExtensions AS $ext) {
        if (!extension_loaded($ext)) {
            throw new Braintree_Exception('The Braintree library requires the ' . $ext . ' extension.');
        }
    }
}

requireDependencies();
