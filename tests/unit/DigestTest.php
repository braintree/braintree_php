<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_DigestTest extends PHPUnit_Framework_TestCase
{
    function testHexDigest()
    {
        Braintree_Configuration::privateKey(str_repeat(chr(0xaa),80));
        $message = 'Test Using Larger Than Block-Size Key - Hash Key First';
        $d =  Braintree_Digest::hexDigest($message);

        $this->assertEquals('aa4ae5e15272d00e95705637ce8a3b55ed402112', $d);
    }

    function testHexDigest_again()
    {
        Braintree_Configuration::privateKey(str_repeat(chr(0xaa),80));
        $message = 'Test Using Larger Than Block-Size Key and Larger Than One Block-Size Data';
        $d =  Braintree_Digest::hexDigest($message);

        $this->assertEquals('e8e99d0f45237d786d6bbaa7965c7808bbff1a91', $d);
    }

    function testBuiltInHmacSha1()
    {
        Braintree_Configuration::privateKey(str_repeat(chr(0xaa),80));
        $message = 'Test Using Larger Than Block-Size Key - Hash Key First';
        $d =  Braintree_Digest::_builtInHmacSha1($message, Braintree_Configuration::privateKey());

        $this->assertEquals('aa4ae5e15272d00e95705637ce8a3b55ed402112', $d);
    }

    function testBuiltInHmacSha1_again()
    {
        Braintree_Configuration::privateKey(str_repeat(chr(0xaa),80));
        $message = 'Test Using Larger Than Block-Size Key and Larger Than One Block-Size Data';
        $d =  Braintree_Digest::_builtInHmacSha1($message, Braintree_Configuration::privateKey());

        $this->assertEquals('e8e99d0f45237d786d6bbaa7965c7808bbff1a91', $d);
    }

    function testHmacSha1()
    {
        Braintree_Configuration::privateKey(str_repeat(chr(0xaa),80));
        $message = 'Test Using Larger Than Block-Size Key - Hash Key First';
        $d =  Braintree_Digest::_hmacSha1($message, Braintree_Configuration::privateKey());

        $this->assertEquals('aa4ae5e15272d00e95705637ce8a3b55ed402112', $d);
    }

    function testHmacSha1_again()
    {
        Braintree_Configuration::privateKey(str_repeat(chr(0xaa),80));
        $message = 'Test Using Larger Than Block-Size Key and Larger Than One Block-Size Data';
        $d =  Braintree_Digest::_hmacSha1($message, Braintree_Configuration::privateKey());

        $this->assertEquals('e8e99d0f45237d786d6bbaa7965c7808bbff1a91', $d);
    }
}
