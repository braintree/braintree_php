<?php

class Braintree_SignatureService
{

    public function __construct($key, $digest)
    {
        $this->key = $key;
        $this->digest = $digest;
    }

    public function sign($data)
    {
        $url_encoded_data = http_build_query($data, null, "&");
        return $this->hash($url_encoded_data) . "|" . $url_encoded_data;
    }

    public function hash($data)
    {
        return call_user_func($this->digest, $this->key, $data);
    }

}
