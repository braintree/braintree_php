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
        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertSame('GET', $this->_mockHttpRequest->options[CURLOPT_CUSTOMREQUEST]);
    }

    public function testMakeRequestSetsUrl()
    {
        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertSame('some-path', $this->_mockHttpRequest->options[CURLOPT_URL]);
    }

    public function testMakeRequestSetsGzipEncoding()
    {
        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertSame('gzip', $this->_mockHttpRequest->options[CURLOPT_ENCODING]);
    }

    public function testMakeRequestDoesNotSetGzipEncodingWhenDisabled()
    {
        $this->_config = new Braintree\Configuration([
            'acceptGzipEncoding' => false
        ]);

        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertArrayNotHasKey(CURLOPT_ENCODING, $this->_mockHttpRequest->options);
    }

    public function testMakeRequestSetsSslVersionWhenConfigured()
    {
        $this->_config = new Braintree\Configuration([
            'sslVersion' => 1.5
        ]);

        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertSame(1.5, $this->_mockHttpRequest->options[CURLOPT_SSLVERSION]);
    }

    public function testMakeRequestDoesNotSetSslVersionWhenNotConfigured()
    {
        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertArrayNotHasKey(CURLOPT_SSLVERSION, $this->_mockHttpRequest->options);
    }

    public function testMakeRequestSetsCustomHeaders()
    {
        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest, null, null, ['custom: header']);
        $this->assertContains('custom: header', $this->_mockHttpRequest->options[CURLOPT_HTTPHEADER]);
    }

    public function testMakeRequestSetsDefaultHeadersWhenCustomHeadersAreNotPresent()
    {
        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);

        $this->assertContains('Accept: application/xml', $this->_mockHttpRequest->options[CURLOPT_HTTPHEADER]);
        $this->assertContains('User-Agent: Braintree PHP Library ' . Braintree\Version::get(), $this->_mockHttpRequest->options[CURLOPT_HTTPHEADER]);
        $this->assertContains('X-ApiVersion: ' . Braintree\Configuration::API_VERSION, $this->_mockHttpRequest->options[CURLOPT_HTTPHEADER]);
        $this->assertContains('Content-Type: application/xml', $this->_mockHttpRequest->options[CURLOPT_HTTPHEADER]);
    }

    public function testMakeRequestUsesClientIdAndSecretWhenUseClientCredentialsIsTrue()
    {
        $this->_config = new Braintree\Configuration([
            'clientId' => 'client_id$development$id',
            'clientSecret' => 'client_secret$development$secret'
        ]);

        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest, null, null, null, true);

        $this->assertSame(CURLAUTH_BASIC, $this->_mockHttpRequest->options[CURLOPT_HTTPAUTH]);
        $this->assertSame('client_id$development$id:client_secret$development$secret', $this->_mockHttpRequest->options[CURLOPT_USERPWD]);
    }

    public function testMakeRequestUsesAccessTokenWhenUseClientCredentialsIsFalseAndAccessTokenIsPresent()
    {
        $this->_config = new Braintree\Configuration([
            'accessToken' => 'access_token$development$id$token',
        ]);

        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);
        $this->assertContains('Authorization: Bearer access_token$development$id$token', $this->_mockHttpRequest->options[CURLOPT_HTTPHEADER]);
    }

    public function testMakeRequestUsesPublicAndPrivateKeysWhenUseClientCredentialsIsFalseAndAccessTokenIsNotPresent()
    {
        $this->_config = new Braintree\Configuration([
            'publicKey' => 'public_key',
            'privateKey' => 'private_key'
        ]);

        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);

        $this->assertSame(CURLAUTH_BASIC, $this->_mockHttpRequest->options[CURLOPT_HTTPAUTH]);
        $this->assertSame('public_key:private_key', $this->_mockHttpRequest->options[CURLOPT_USERPWD]);
    }

    public function testMakeRequestSetsSslOptionsWhenSslIsOn()
    {
        $this->_config = new Braintree\Configuration([
            'environment' => 'production',
        ]);

        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);

        $this->assertSame(true, $this->_mockHttpRequest->options[CURLOPT_SSL_VERIFYPEER]);
        $this->assertSame(2, $this->_mockHttpRequest->options[CURLOPT_SSL_VERIFYHOST]);
        $this->assertNotNull($this->_mockHttpRequest->options[CURLOPT_CAINFO]);
    }

    public function testMakeRequestDoesNotSetSslOptionsWhenSslIsOff()
    {
        $this->_config = new Braintree\Configuration([
            'environment' => 'development',
        ]);

        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $this->_config, $this->_mockHttpRequest);

        $this->assertArrayNotHasKey(CURLOPT_SSL_VERIFYPEER, $this->_mockHttpRequest->options);
        $this->assertArrayNotHasKey(CURLOPT_SSL_VERIFYHOST, $this->_mockHttpRequest->options);
        $this->assertArrayNotHasKey(CURLOPT_CAINFO, $this->_mockHttpRequest->options);
    }

    public function testMakeRequestSetsHeaderAndBodyForMultipartFormDataIfFileIsPresent()
    {
        $requestBody = [
            'document_upload[kind]' => 'evidence_document'
        ];

        $file = fopen(dirname(dirname(__DIR__)) . '/fixtures/bt_logo.png', 'rb');

        Braintree\HttpHelpers\Curl::makeRequest('POST', 'some-path', $this->_config, $this->_mockHttpRequest, $requestBody, $file, null, null);

        $this->assertSame(true, $this->_mockHttpRequest->options[CURLOPT_POST]);
        $this->assertNotEmpty($this->_mockHttpRequest->options[CURLOPT_POSTFIELDS]);
        $this->assertNotEmpty(preg_grep('~Content-Type: multipart/form-data; boundary=(.*)~', $this->_mockHttpRequest->options[CURLOPT_HTTPHEADER]));
    }

    public function testMakeRequestDoesNotSetHeaderAndBodyForMultipartFormDataIfFileIsNotPresent()
    {
        $requestBody = [
            'some-key' => 'some-value'
        ];

        Braintree\HttpHelpers\Curl::makeRequest('POST', 'some-path', $this->_config, $this->_mockHttpRequest, $requestBody, null, null, null);

        $this->assertArrayNotHasKey(CURLOPT_POST, $this->_mockHttpRequest->options);
        $this->assertArrayNotHasKey(CURLOPT_POSTFIELDS, $this->_mockHttpRequest->options);
        $this->assertEmpty(preg_grep('~Content-Type: multipart/form-data; boundary=(.*)~', $this->_mockHttpRequest->options[CURLOPT_HTTPHEADER]));
    }

    // TODO
    // request body
    // proxy server
    // return transfer option
    //
    // timeout exception
    // ssl cert exception
    // connection exception
    // successful response
}
