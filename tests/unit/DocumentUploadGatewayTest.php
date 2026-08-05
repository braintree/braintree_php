<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class DocumentUploadGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->documentUpload();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\DocumentUploadGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function documentUploadResponse()
    {
        return ['documentUpload' => [
            'id' => 'doc_123',
            'contentType' => 'application/pdf',
            'size' => 1024,
            'kind' => 'evidence_document',
            'expiresAt' => '2025-01-01',
        ]];
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    public function testGatewayCanBeConstructed()
    {
        $gateway = Helper::integrationMerchantGateway()->documentUpload();
        $this->assertInstanceOf('Braintree\DocumentUploadGateway', $gateway);
    }

    public function testConstruct_throwsWithoutCredentials()
    {
        $this->expectException('Braintree\Exception\Configuration');
        $this->expectExceptionMessage('merchantId needs to be set');
        $gateway = new Braintree\Gateway(['environment' => 'development']);
        $gateway->documentUpload();
    }

    public function testCreateSignature()
    {
        $this->assertEquals(['file', 'kind'], Braintree\DocumentUploadGateway::createSignature());
    }

    public function testCreate_throwsIfInvalidKey()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('invalid keys: invalidKey');
        Helper::integrationMerchantGateway()->documentUpload()->create(['invalidKey' => 'foo']);
    }

    public function testCreate_throwsIfFileIsNotResource()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('file must be a stream resource');
        Helper::integrationMerchantGateway()->documentUpload()->create([
            'kind' => Braintree\DocumentUpload::EVIDENCE_DOCUMENT,
            'file' => 'not-a-resource',
        ]);
    }

    public function testCreate_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('postMultipart', $this->documentUploadResponse());
        $file = fopen('php://memory', 'r');
        $result = $gateway->create([
            'kind' => Braintree\DocumentUpload::EVIDENCE_DOCUMENT,
            'file' => $file,
        ]);
        fclose($file);
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
        $this->assertInstanceOf('Braintree\DocumentUpload', $result->documentUpload);
        $this->assertEquals('doc_123', $result->documentUpload->id);
    }

    public function testCreate_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('postMultipart', $this->errorResponse());
        $file = fopen('php://memory', 'r');
        $result = $gateway->create([
            'kind' => Braintree\DocumentUpload::EVIDENCE_DOCUMENT,
            'file' => $file,
        ]);
        fclose($file);
        $this->assertInstanceOf('Braintree\Result\Error', $result);
        $this->assertFalse($result->success);
    }
}
