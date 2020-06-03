<?php
namespace Test\HttpHelpers;

use Braintree;

class MockHttpRequest implements Braintree\HttpHelpers\HttpRequest
{
    public $options;
    public $response;
    public $httpStatus;
    public $errorCode;
    public $error;
    public $closed;

    public function __construct()
    {
        $this->closed = false;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function execute()
    {
        return $this->response;
    }

    public function getInfo($name)
    {
        if ($name == CURLINFO_HTTP_CODE) {
            return $this->httpStatus;
        }
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getError()
    {
        return $this->error;
    }

    public function close()
    {
        $this->closed = true;
    }
}
