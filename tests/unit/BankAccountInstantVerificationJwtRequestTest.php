<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree\BankAccountInstantVerificationJwtRequest;

class BankAccountInstantVerificationJwtRequestTest extends Setup
{
    public function testBusinessName_returnsFluentInterface()
    {
        $request = new BankAccountInstantVerificationJwtRequest();
        $result = $request->businessName('Acme Corp');

        $this->assertInstanceOf(BankAccountInstantVerificationJwtRequest::class, $result);
        $this->assertSame($request, $result);
    }

    public function testReturnUrl_returnsFluentInterface()
    {
        $request = new BankAccountInstantVerificationJwtRequest();
        $result = $request->returnUrl('https://example.com/return');

        $this->assertInstanceOf(BankAccountInstantVerificationJwtRequest::class, $result);
        $this->assertSame($request, $result);
    }

    public function testCancelUrl_returnsFluentInterface()
    {
        $request = new BankAccountInstantVerificationJwtRequest();
        $result = $request->cancelUrl('https://example.com/cancel');

        $this->assertInstanceOf(BankAccountInstantVerificationJwtRequest::class, $result);
        $this->assertSame($request, $result);
    }

    public function testGetBusinessName_returnsSetValue()
    {
        $request = new BankAccountInstantVerificationJwtRequest();
        $request->businessName('Acme Corp');

        $this->assertEquals('Acme Corp', $request->getBusinessName());
    }

    public function testGetReturnUrl_returnsSetValue()
    {
        $request = new BankAccountInstantVerificationJwtRequest();
        $request->returnUrl('https://example.com/return');

        $this->assertEquals('https://example.com/return', $request->getReturnUrl());
    }

    public function testGetCancelUrl_returnsSetValue()
    {
        $request = new BankAccountInstantVerificationJwtRequest();
        $request->cancelUrl('https://example.com/cancel');

        $this->assertEquals('https://example.com/cancel', $request->getCancelUrl());
    }

    public function testGetters_returnNullWhenNotSet()
    {
        $request = new BankAccountInstantVerificationJwtRequest();

        $this->assertNull($request->getBusinessName());
        $this->assertNull($request->getReturnUrl());
        $this->assertNull($request->getCancelUrl());
    }

    public function testToGraphQLVariables_includesAllSetFields()
    {
        $request = new BankAccountInstantVerificationJwtRequest();
        $request->businessName('Acme Corp')
                ->returnUrl('https://example.com/return')
                ->cancelUrl('https://example.com/cancel');

        $variables = $request->toGraphQLVariables();

        $this->assertEquals([
            'input' => [
                'businessName' => 'Acme Corp',
                'returnUrl' => 'https://example.com/return',
                'cancelUrl' => 'https://example.com/cancel',
            ],
        ], $variables);
    }

    public function testToGraphQLVariables_omitsUnsetFields()
    {
        $request = new BankAccountInstantVerificationJwtRequest();
        $request->businessName('Acme Corp');

        $variables = $request->toGraphQLVariables();

        $this->assertArrayHasKey('input', $variables);
        $this->assertArrayHasKey('businessName', $variables['input']);
        $this->assertArrayNotHasKey('returnUrl', $variables['input']);
        $this->assertArrayNotHasKey('cancelUrl', $variables['input']);
    }

    public function testToGraphQLVariables_returnsEmptyInputWhenNothingSet()
    {
        $request = new BankAccountInstantVerificationJwtRequest();

        $variables = $request->toGraphQLVariables();

        $this->assertEquals(['input' => []], $variables);
    }

    public function testFluentChaining_setsAllFields()
    {
        $request = (new BankAccountInstantVerificationJwtRequest())
            ->businessName('Acme Corp')
            ->returnUrl('https://example.com/return')
            ->cancelUrl('https://example.com/cancel');

        $this->assertEquals('Acme Corp', $request->getBusinessName());
        $this->assertEquals('https://example.com/return', $request->getReturnUrl());
        $this->assertEquals('https://example.com/cancel', $request->getCancelUrl());
    }
}
