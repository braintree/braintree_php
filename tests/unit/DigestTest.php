<?php namespace Braintree\Tests\Unit;

use Braintree\Configuration;
use Braintree\Digest;

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class DigestTest extends \PHPUnit_Framework_TestCase
{

    function testSecureCompareReturnsTrueForMatches()
    {
        $this->assertTrue(Digest::secureCompare("a_string", "a_string"));
    }

    function testSecureCompareReturnsFalseForDifferentLengths()
    {
        $this->assertFalse(Digest::secureCompare("a_string", "a_string_that_is_longer"));
    }

    function testSecureCompareReturnsFalseForNonmatchingSameLengthStrings()
    {
        $this->assertFalse(Digest::secureCompare("a_string", "a_strong"));
    }

    function testHexDigestSha1()
    {
        $key = str_repeat(chr(0xaa), 80);
        $message = 'Test Using Larger Than Block-Size Key - Hash Key First';
        $d = Digest::hexDigestSha1($key, $message);

        $this->assertEquals('aa4ae5e15272d00e95705637ce8a3b55ed402112', $d);
    }

    function testHexDigestSha1_again()
    {
        $key = str_repeat(chr(0xaa), 80);
        $message = 'Test Using Larger Than Block-Size Key and Larger Than One Block-Size Data';
        $d = Digest::hexDigestSha1($key, $message);

        $this->assertEquals('e8e99d0f45237d786d6bbaa7965c7808bbff1a91', $d);
    }

    function testHexDigestSha256()
    {
        $key = str_repeat(chr(0xaa), 80);
        $message = 'Test Using Larger Than Block-Size Key - Hash Key First';
        $d = Digest::hexDigestSha256($key, $message);

        $this->assertEquals('6953025ed96f0c09f80a96f78e6538dbe2e7b820e3dd970e7ddd39091b32352f', $d);
    }

    function testHexDigestSha256_again()
    {
        $key = str_repeat(chr(0xaa), 80);
        $message = 'Test Using Larger Than Block-Size Key and Larger Than One Block-Size Data';
        $d = Digest::hexDigestSha256($key, $message);

        $this->assertEquals('6355ac22e890d0a3c8481a5ca4825bc884d3e7a1ff98a2fc2ac7d8e064c3b2e6', $d);
    }

    function testBuiltInHmacSha1()
    {
        Configuration::privateKey(str_repeat(chr(0xaa), 80));
        $message = 'Test Using Larger Than Block-Size Key - Hash Key First';
        $d = Digest::_builtInHmacSha1($message, Configuration::privateKey());

        $this->assertEquals('aa4ae5e15272d00e95705637ce8a3b55ed402112', $d);
    }

    function testBuiltInHmacSha1_again()
    {
        Configuration::privateKey(str_repeat(chr(0xaa), 80));
        $message = 'Test Using Larger Than Block-Size Key and Larger Than One Block-Size Data';
        $d = Digest::_builtInHmacSha1($message, Configuration::privateKey());

        $this->assertEquals('e8e99d0f45237d786d6bbaa7965c7808bbff1a91', $d);
    }

    function testHmacSha1()
    {
        Configuration::privateKey(str_repeat(chr(0xaa), 80));
        $message = 'Test Using Larger Than Block-Size Key - Hash Key First';
        $d = Digest::_hmacSha1($message, Configuration::privateKey());

        $this->assertEquals('aa4ae5e15272d00e95705637ce8a3b55ed402112', $d);
    }

    function testHmacSha1_again()
    {
        Configuration::privateKey(str_repeat(chr(0xaa), 80));
        $message = 'Test Using Larger Than Block-Size Key and Larger Than One Block-Size Data';
        $d = Digest::_hmacSha1($message, Configuration::privateKey());

        $this->assertEquals('e8e99d0f45237d786d6bbaa7965c7808bbff1a91', $d);
    }
}
