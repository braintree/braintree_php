<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Helper;
use Test\Setup;
use Braintree;

class GraphQLTest extends Setup
{
    private function graphQL()
    {
        return new Braintree\GraphQL(Helper::integrationMerchantGateway()->config);
    }

    public function testGraphQLHeaders_returnsExpectedKeys()
    {
        $headers = $this->graphQL()->graphQLHeaders();

        $this->assertIsArray($headers);
        $this->assertContains('Accept: application/json', $headers);
        $this->assertContains('Content-Type: application/json', $headers);
    }

    public function testGraphQLHeaders_containsBraintreeVersion()
    {
        $headers = $this->graphQL()->graphQLHeaders();

        $versionHeader = 'Braintree-Version: ' . Braintree\Configuration::GRAPHQL_API_VERSION;
        $this->assertContains($versionHeader, $headers);
    }

    public function testGraphQLHeaders_containsUserAgent()
    {
        $headers = $this->graphQL()->graphQLHeaders();

        $matched = array_filter($headers, function ($h) {
            return strpos($h, 'User-Agent: Braintree PHP Library') === 0;
        });
        $this->assertNotEmpty($matched);
    }

    public function testGraphQLHeaders_containsApiVersion()
    {
        $headers = $this->graphQL()->graphQLHeaders();

        $apiVersionHeader = 'X-ApiVersion: ' . Braintree\Configuration::API_VERSION;
        $this->assertContains($apiVersionHeader, $headers);
    }
}
