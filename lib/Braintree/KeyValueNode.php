<?php namespace Braintree;

class KeyValueNode
{
    function __construct($name)
    {
        $this->name = $name;
        $this->searchTerm = true;

    }

    function is($value)
    {
        $this->searchTerm = $value;
        return $this;
    }

    function toParam()
    {
        return $this->searchTerm;
    }
}
