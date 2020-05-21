<?php
namespace Test\Unit;

require_once dirname(dirname(__DIR__)) . '/Setup.php';
require_once __DIR__ . '/MockHttpRequest.php';

use Test\Setup;
use Test\HttpHelpers\MockHttpRequest;
use Braintree;

class CurlTest extends Setup
{
    private $_config;
    private $_mockHttpRequest;

    public function setup()
    {
        $this->_config = new Braintree\Configuration();
        $this->_mockHttpRequest = new MockHttpRequest('some-url');
    }

    public function testMakeRequestSetsTimeout()
    {
        $this->_config = new Braintree\Configuration([
            'timeout' => 10
        ]);

        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertSame(10, $this->_mockHttpRequest->options[CURLOPT_TIMEOUT]);
    }

    public function testMakeRequestSetsHttpVerb()
    {
        Braintree\HttpHelpers\Curl::makeRequest('PUT', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertSame('PUT', $this->_mockHttpRequest->options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testMakeRequestSetsUrl()
    {
        Braintree\HttpHelpers\Curl::makeRequest('PUT', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertSame('some-path', $this->_mockHttpRequest->options[CURLOPT_URL]);
    }

    public function testMakeRequestSetsGzipEncoding()
    {
        Braintree\HttpHelpers\Curl::makeRequest('PUT', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertSame('gzip', $this->_mockHttpRequest->options[CURLOPT_ENCODING]);
    }

    public function testMakeRequestDoesNotSetGzipEncodingWhenDisabled()
    {
        $this->_config = new Braintree\Configuration([
            'acceptGzipEncoding' => false
        ]);

        Braintree\HttpHelpers\Curl::makeRequest('PUT', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertArrayNotHasKey(CURLOPT_ENCODING, $this->_mockHttpRequest->options);
    }

    public function testMakeRequestSetsSslVersionWhenConfigured()
    {
        $this->_config = new Braintree\Configuration([
            'sslVersion' => 1.5
        ]);

        Braintree\HttpHelpers\Curl::makeRequest('PUT', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertSame(1.5, $this->_mockHttpRequest->options[CURLOPT_SSLVERSION]);
    }

    public function testMakeRequestDoesNotSetSslVersionWhenNotConfigured()
    {
        Braintree\HttpHelpers\Curl::makeRequest('PUT', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertArrayNotHasKey(CURLOPT_SSLVERSION, $this->_mockHttpRequest->options);
    }
}
