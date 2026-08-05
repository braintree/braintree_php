<?php

namespace Test\Unit\Test;

require_once dirname(__DIR__, 2) . '/Setup.php';

use Test\Setup;
use Braintree\Test\CreditCardNumbers;

class CreditCardNumbersTest extends Setup
{
    public function testAmExes_areNonEmpty()
    {
        $this->assertNotEmpty(CreditCardNumbers::$amExes);
    }

    public function testVisas_containKnownNumber()
    {
        $this->assertContains('4111111111111111', CreditCardNumbers::$visas);
    }

    public function testMasterCards_containKnownNumber()
    {
        $this->assertContains('5555555555554444', CreditCardNumbers::$masterCards);
    }

    public function testGetAll_returnsNonEmptyArray()
    {
        $all = CreditCardNumbers::getAll();
        $this->assertIsArray($all);
        $this->assertNotEmpty($all);
    }

    public function testGetAll_includesVisas()
    {
        $all = CreditCardNumbers::getAll();
        foreach (CreditCardNumbers::$visas as $visa) {
            $this->assertContains($visa, $all);
        }
    }

    public function testGetAll_includesMasterCards()
    {
        $all = CreditCardNumbers::getAll();
        foreach (CreditCardNumbers::$masterCards as $mc) {
            $this->assertContains($mc, $all);
        }
    }

    public function testFailsSandboxVerification_hasExpectedBrands()
    {
        $this->assertArrayHasKey('AmEx', CreditCardNumbers::$failsSandboxVerification);
        $this->assertArrayHasKey('Visa', CreditCardNumbers::$failsSandboxVerification);
        $this->assertArrayHasKey('MasterCard', CreditCardNumbers::$failsSandboxVerification);
        $this->assertArrayHasKey('Discover', CreditCardNumbers::$failsSandboxVerification);
    }

    public function testAmexPayWithPoints_hasExpectedKeys()
    {
        $this->assertArrayHasKey('Success', CreditCardNumbers::$amexPayWithPoints);
        $this->assertArrayHasKey('IneligibleCard', CreditCardNumbers::$amexPayWithPoints);
        $this->assertArrayHasKey('InsufficientPoints', CreditCardNumbers::$amexPayWithPoints);
    }
}
