<?php

namespace Braintree\HttpHelpers;

trait HttpClientAware
{
    protected HttpClient $_http;

    public function setHttpClient(HttpClient $http): self
    {
        $this->_http = $http;
        return $this;
    }
}