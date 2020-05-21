<?php
namespace Test\HttpHelpers;

use Braintree;

class MockHttpRequest implements Braintree\HttpHelpers\HttpRequest {

    public $options;

    public function __construct($url)
    {

    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function execute()
    {
    }

    public function getInfo($name)
    {
    }

    public function getErrorCode()
    {
    }

    public function getError()
    {
    }

    public function close()
    {
    }
}
