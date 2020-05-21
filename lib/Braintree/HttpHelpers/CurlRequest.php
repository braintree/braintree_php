<?php
namespace Braintree\HttpHelpers;

class CurlRequest implements HttpRequest {
    private $handle = null;

    public function __construct($url) {
        $this->handle = curl_init($url);
    }

    public function setOption($name, $value) {
        curl_setopt($this->handle, $name, $value);
    }

    public function execute() {
        return curl_exec($this->handle);
    }

    public function getInfo($name) {
        return curl_getinfo($this->handle, $name);
    }

    public function getErrorCode() {
        return curl_errno($this->handle);
    }

    public function getError() {
        return curl_error($this->handle);
    }

    public function close() {
        curl_close($this->handle);
    }
}
