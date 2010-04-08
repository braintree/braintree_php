<?php

class Braintree_TextNode
{
    function __construct($name)
    {
        $this->name = $name;
        $this->searchTerms = array();
    }

    function is($value)
    {
        $this->searchTerms["is"] = $value;
        return $this;
    }

    function toParam()
    {
        return $this->searchTerms;
    }
}
?>
