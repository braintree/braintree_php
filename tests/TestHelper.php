<?php

// function __autoload($className)
// {
//     if (!class_exists($className)) {
//         $path = str_replace('_', '/', $className);
//         $file = $path . '.php';
//         include $file;
//
//     }
//
// }
// $libPath = realpath(dirname(__FILE__).'/../lib');
//
// define('LIB_DIR', $libPath);
//
// set_include_path($libPath.PATH_SEPARATOR.get_include_path());
require_once 'PHPUnit/Framework.php';

set_include_path(
  get_include_path() . PATH_SEPARATOR .
  realpath(dirname(__FILE__)) . '/../vendor/ZendFramework-1.10.2-minimal/library'
);

set_include_path(
  get_include_path() . PATH_SEPARATOR .
  realpath(dirname(__FILE__)) . '/../lib'
);

require_once "Braintree.php";

Braintree_Configuration::environment('development');
Braintree_Configuration::merchantId('integration_merchant_id');
Braintree_Configuration::publicKey('integration_public_key');
Braintree_Configuration::privateKey('integration_private_key');

class Braintree_TestHelper
{
    public static function submitTrRequest($url, $regularParams, $trData)
    {
        $connection = new Zend_Http_Client();
        $connection->setConfig(array('maxredirects' => 0));
        $connection->setUri($url);
        $connection->setMethod('POST');
        $connection->setRawData(
            http_build_query(array_merge($regularParams, array('tr_data' => $trData))),
            'application/x-www-form-urlencoded'
        );
        $response = $connection->request();
        $location = $response->getHeader('Location');
        $parsedUrl = parse_url($location);
        return $parsedUrl['query'];
    }
}

?>
