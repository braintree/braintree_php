<?php

class Braintree_EqualityNode
{
    function __construct($name)
    {
        $this->name = $name;
        $this->searchTerms = array();
    }

    function is($value)
    {
        $this->searchTerms['is'] = strval($value);
        return $this;
    }

    function isNot($value)
    {
        $this->searchTerms['is_not'] = strval($value);
        return $this;
    }

    function toParam()
    {
        return $this->searchTerms;
    }
}
