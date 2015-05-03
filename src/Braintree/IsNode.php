<?php
namespace Braintree;

class IsNode
{
    public function __construct($name)
    {
        $this->name = $name;
        $this->searchTerms = array();
    }

    public function is($value)
    {
        $this->searchTerms['is'] = strval($value);

        return $this;
    }

    public function toParam()
    {
        return $this->searchTerms;
    }
}
