<?php
namespace Test\Unit;

require_once dirname(dirname(__DIR__)) . '/Setup.php';

use Test\Setup;
use Braintree;

class CurlTest extends Setup
{

    public function testMakeRequestAddsTimeout()
    {
        $config = new Braintree\Configuration([
            'timeout' => 10
        ]);

        $mockHttpRequest = $this->createMock(Braintree\HttpHelpers\HttpRequest::class);
        $mockHttpRequest->expects($this->once())
                        ->method('setOption')
                        ->with(
                            $this->equalTo(CURLOPT_TIMEOUT),
                            $this->equalTo(10)
                        );

        Braintree\HttpHelpers\Curl::makeRequest('GET', 'some-path', $config, $mockHttpRequest);
    } 

}
