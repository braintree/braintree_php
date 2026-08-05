<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Test\Helper;
use Braintree;

class DisputeGatewayTest extends Setup
{
    private function gatewayWithMock(string $httpMethod, array $response)
    {
        $gateway = Helper::integrationMerchantGateway()->dispute();
        $mock = $this->createMock('\Braintree\Http');
        $mock->method($httpMethod)->willReturn($response);
        $prop = new \ReflectionProperty('Braintree\DisputeGateway', '_http');
        $prop->setAccessible(true);
        $prop->setValue($gateway, $mock);
        return $gateway;
    }

    private function errorResponse()
    {
        return ['apiErrorResponse' => ['errors' => []]];
    }

    private function evidenceResponse()
    {
        return ['evidence' => ['id' => 'ev_123', 'comment' => 'text', 'createdAt' => '2024-01-01']];
    }

    public function testConstruct_throwsWithoutCredentials()
    {
        $this->expectException('Braintree\Exception\Configuration');
        $this->expectExceptionMessage('merchantId needs to be set');
        $gateway = new Braintree\Gateway(['environment' => 'development']);
        $gateway->dispute();
    }

    public function testAccept_throwsNotFoundWhenIdIsEmpty()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "" not found');
        Helper::integrationMerchantGateway()->dispute()->accept('');
    }

    public function testAccept_throwsNotFoundWhenIdIsNull()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "" not found');
        Helper::integrationMerchantGateway()->dispute()->accept(null);
    }

    public function testAccept_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', []);
        $result = $gateway->accept('dispute_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testAccept_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->errorResponse());
        $result = $gateway->accept('dispute_123');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testFinalize_throwsNotFoundWhenIdIsEmpty()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "" not found');
        Helper::integrationMerchantGateway()->dispute()->finalize('');
    }

    public function testFinalize_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('put', []);
        $result = $gateway->finalize('dispute_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testFinalize_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('put', $this->errorResponse());
        $result = $gateway->finalize('dispute_123');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testFind_throwsNotFoundWhenIdIsEmpty()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "" not found');
        Helper::integrationMerchantGateway()->dispute()->find('');
    }

    public function testAddTextEvidence_throwsIfContentIsBlank()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('content cannot be blank');
        Helper::integrationMerchantGateway()->dispute()->addTextEvidence('dispute_123', '');
    }

    public function testAddTextEvidence_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->evidenceResponse());
        $result = $gateway->addTextEvidence('dispute_123', 'My evidence text');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testAddTextEvidence_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->addTextEvidence('dispute_123', 'My evidence text');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testAddTextEvidence_throwsNotFoundWhenDisputeIdIsEmpty()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "" not found');
        Helper::integrationMerchantGateway()->dispute()->addTextEvidence('', 'content');
    }

    public function testAddTextEvidence_throwsIfCategoryIsBlank()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('category cannot be blank');
        $gateway = $this->gatewayWithMock('post', $this->evidenceResponse());
        $gateway->addTextEvidence('dispute_123', ['content' => 'text', 'category' => '']);
    }

    public function testAddTextEvidence_throwsIfSequenceNumberIsBlank()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('sequenceNumber cannot be blank');
        $gateway = $this->gatewayWithMock('post', $this->evidenceResponse());
        $gateway->addTextEvidence('dispute_123', ['content' => 'text', 'sequenceNumber' => '']);
    }

    public function testAddTextEvidence_throwsIfSequenceNumberIsNotInteger()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('sequenceNumber must be an integer');
        $gateway = $this->gatewayWithMock('post', $this->evidenceResponse());
        $gateway->addTextEvidence('dispute_123', ['content' => 'text', 'sequenceNumber' => 'abc']);
    }

    public function testAddFileEvidence_throwsNotFoundWhenDisputeIdIsEmpty()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "" not found');
        Helper::integrationMerchantGateway()->dispute()->addFileEvidence('', 'doc_123');
    }

    public function testAddFileEvidence_throwsNotFoundWhenDocumentIdIsEmpty()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('document with id "" not found');
        Helper::integrationMerchantGateway()->dispute()->addFileEvidence('dispute_123', '');
    }

    public function testAddFileEvidence_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->evidenceResponse());
        $result = $gateway->addFileEvidence('dispute_123', 'doc_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testAddFileEvidence_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('post', $this->errorResponse());
        $result = $gateway->addFileEvidence('dispute_123', 'doc_123');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testAddFileEvidence_throwsIfCategoryIsBlank()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('category cannot be blank');
        $gateway = $this->gatewayWithMock('post', $this->evidenceResponse());
        $gateway->addFileEvidence('dispute_123', ['documentId' => 'doc_123', 'category' => '']);
    }

    public function testRemoveEvidence_throwsNotFoundWhenIdsAreEmpty()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('evidence with id "" for dispute with id "" not found');
        Helper::integrationMerchantGateway()->dispute()->removeEvidence('', '');
    }

    public function testAccept_throwsNotFoundWhenIdIsTraversal()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "../../foo" not found');
        Helper::integrationMerchantGateway()->dispute()->accept('../../foo');
    }

    public function testFinalize_throwsNotFoundWhenIdIsTraversal()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "../../foo" not found');
        Helper::integrationMerchantGateway()->dispute()->finalize('../../foo');
    }

    public function testFind_throwsNotFoundWhenIdIsTraversal()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "../../foo" not found');
        Helper::integrationMerchantGateway()->dispute()->find('../../foo');
    }

    public function testAddTextEvidence_throwsNotFoundWhenDisputeIdIsTraversal()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "../../foo" not found');
        Helper::integrationMerchantGateway()->dispute()->addTextEvidence('../../foo', 'content');
    }

    public function testAddFileEvidence_throwsNotFoundWhenDisputeIdIsTraversal()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('dispute with id "../../foo" not found');
        Helper::integrationMerchantGateway()->dispute()->addFileEvidence('../../foo', 'doc_123');
    }

    public function testRemoveEvidence_throwsNotFoundWhenIdsAreTraversal()
    {
        $this->expectException('Braintree\Exception\NotFound');
        $this->expectExceptionMessage('evidence with id "evidence" for dispute with id "../../foo" not found');
        Helper::integrationMerchantGateway()->dispute()->removeEvidence('../../foo', 'evidence');
    }

    public function testRemoveEvidence_returnsSuccessfulResult()
    {
        $gateway = $this->gatewayWithMock('delete', []);
        $result = $gateway->removeEvidence('dispute_123', 'ev_123');
        $this->assertInstanceOf('Braintree\Result\Successful', $result);
    }

    public function testRemoveEvidence_returnsErrorResult()
    {
        $gateway = $this->gatewayWithMock('delete', $this->errorResponse());
        $result = $gateway->removeEvidence('dispute_123', 'ev_123');
        $this->assertInstanceOf('Braintree\Result\Error', $result);
    }

    public function testSearch_returnsPaginatedCollection()
    {
        $gateway = Helper::integrationMerchantGateway()->dispute();
        $result = $gateway->search([Braintree\DisputeSearch::id()->is('dispute_123')]);
        $this->assertInstanceOf('Braintree\PaginatedCollection', $result);
    }

    public function testFetchDisputes_returnsPaginatedResult()
    {
        $gateway = $this->gatewayWithMock('post', [
            'disputes' => [
                'dispute' => [],
                'totalItems' => [0],
                'pageSize' => [50],
            ],
        ]);
        $result = $gateway->fetchDisputes([], 1);
        $this->assertInstanceOf('Braintree\PaginatedResult', $result);
        $this->assertEquals(0, $result->getTotalItems());
    }
}
