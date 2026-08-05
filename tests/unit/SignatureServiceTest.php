<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class SignatureServiceTest extends Setup
{
    private function sha1Service()
    {
        return new Braintree\SignatureService('test-key', ['Braintree\Digest', 'hexDigestSha1']);
    }

    public function testHash_returnsExpectedHmac()
    {
        $service = $this->sha1Service();
        $expected = Braintree\Digest::hexDigestSha1('test-key', 'test-payload');
        $this->assertEquals($expected, $service->hash('test-payload'));
    }

    public function testHash_returnsDifferentValueForDifferentKey()
    {
        $service1 = new Braintree\SignatureService('key-one', ['Braintree\Digest', 'hexDigestSha1']);
        $service2 = new Braintree\SignatureService('key-two', ['Braintree\Digest', 'hexDigestSha1']);
        $this->assertNotEquals($service1->hash('payload'), $service2->hash('payload'));
    }

    public function testSign_returnsHashPipedWithPayload()
    {
        $service = $this->sha1Service();
        $payload = 'test-payload';
        $signed = $service->sign($payload);
        $parts = explode('|', $signed, 2);
        $this->assertCount(2, $parts);
        $this->assertEquals($service->hash($payload), $parts[0]);
        $this->assertEquals($payload, $parts[1]);
    }

    public function testSign_hashIsVerifiable()
    {
        $service = $this->sha1Service();
        $payload = 'merchant_id=abc&public_key=xyz';
        $signed = $service->sign($payload);
        [$hash, $body] = explode('|', $signed, 2);
        $this->assertEquals(Braintree\Digest::hexDigestSha1('test-key', $body), $hash);
    }

    public function testHash_withSha256Digest()
    {
        $service = new Braintree\SignatureService('my-key', ['Braintree\Digest', 'hexDigestSha256']);
        $expected = Braintree\Digest::hexDigestSha256('my-key', 'data');
        $this->assertEquals($expected, $service->hash('data'));
    }
}
