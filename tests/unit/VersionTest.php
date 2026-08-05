<?php

namespace Test\Unit;

require_once dirname(__DIR__) . '/Setup.php';

use Test\Setup;
use Braintree;

class VersionTest extends Setup
{
    public function testGet_returnsVersionString()
    {
        $version = Braintree\Version::get();
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $version);
    }

    public function testGet_includesMajorMinorTiny()
    {
        $version = Braintree\Version::get();
        $expected = Braintree\Version::MAJOR . '.' . Braintree\Version::MINOR . '.' . Braintree\Version::TINY;
        $this->assertEquals($expected, $version);
    }
}
