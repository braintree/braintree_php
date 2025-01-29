<?php

namespace Test\Unit;

use Braintree\GraphQLClient;
use Braintree\Error;
use PHPUnit\Framework\TestCase;

class GraphQLClientTest extends TestCase
{
    public function testQuery_makesRequestToGraphQLService()
    {
        $mockConfig = $this->getMockBuilder('\Braintree\Configuration')->disableOriginalConstructor()->getMock();
        $graphQLClient = new GraphQLClient($mockConfig);

        $mockGraphQLService = $this->getMockBuilder('\Braintree\GraphQL')->disableOriginalConstructor()->getMock();
        $mockGraphQLService->expects($this->once())
            ->method('request')
            ->with('query { customer }', ['id' => '123'])
            ->willReturn(['data' => ['customer' => []]]);

        $reflection = new \ReflectionClass('Braintree\GraphQLClient');
        $property = $reflection->getProperty('_service');
        $property->setAccessible(true);
        $property->setValue($graphQLClient, $mockGraphQLService);

        $result = $graphQLClient->query('query { customer }', ['id' => '123']);
        $this->assertEquals(['data' => ['customer' => []]], $result);
    }


    public function testgetValidationErrors_returnsNullWhenNoErrorsPresent()
    {
        $this->assertNull(GraphQLClient::getValidationErrors([]));
        $this->assertNull(GraphQLClient::getValidationErrors(['errors' => 'string']));
    }

    public function testgetValidationErrors_returnsErrorsWhenErrorsPresent()
    {
        $errors = ['errors' => [['message' => 'some error']]];
        $expectedErrors = ['errors' => [['attribute' => '', 'code' => null, 'message' => 'some error']]];
        $this->assertEquals($expectedErrors, GraphQLClient::getValidationErrors($errors));
    }


    public function testgetValidationErrors_returnsErrorsWithLegacyCodeWhenPresent()
    {
        $errors = ['errors' => [['message' => 'some error', 'extensions' => ['legacyCode' => '91564']]]];
        $expectedErrors = ['errors' => [['attribute' => '', 'code' => '91564', 'message' => 'some error']]];

        $this->assertEquals($expectedErrors, GraphQLClient::getValidationErrors($errors));
    }

    public function testgetValidationErrors_returnsErrorsWithoutLegacyCodeWhenNotPresent()
    {
        $errors = ['errors' => [['message' => 'some error', 'extensions' => []]]];
        $expectedErrors = ['errors' => [['attribute' => '', 'code' => null, 'message' => 'some error']]];

        $this->assertEquals($expectedErrors, GraphQLClient::getValidationErrors($errors));
    }
}
