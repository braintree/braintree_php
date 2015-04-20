<?php

namespace Braintree;

class KeyValueNode
{
    public function __construct($name)
    {
        $this->name = $name;
        $this->searchTerm = true;
    }

    public function is($value)
    {
        $this->searchTerm = $value;

        return $this;
    }

    public function toParam()
    {
        return $this->searchTerm;
    }
}
