<?php

namespace Test;

require_once __DIR__.'/autoload.php';
require_once dirname(__DIR__).'/src/autoload.php';

date_default_timezone_set('UTC');

use Braintree\Configuration;
use PHPUnit_Framework_TestCase;

class Setup extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        self::integrationMerchantConfig();
    }

    public static function integrationMerchantConfig()
    {
        Configuration::reset();

        Configuration::environment('development');
        Configuration::merchantId('integration_merchant_id');
        Configuration::publicKey('integration_public_key');
        Configuration::privateKey('integration_private_key');
    }

    public static function testMerchantConfig()
    {
        Configuration::reset();

        Configuration::environment('development');
        Configuration::merchantId('test_merchant_id');
        Configuration::publicKey('test_public_key');
        Configuration::privateKey('test_private_key');
    }
}

