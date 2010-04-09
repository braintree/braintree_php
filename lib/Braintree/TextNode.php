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
        $this->searchTerms["is"] = strval($value);
        return $this;
    }

    function isNot($value)
    {
        $this->searchTerms["is_not"] = strval($value);
        return $this;
    }

    function startsWith($value)
    {
        $this->searchTerms["starts_with"] = strval($value);
        return $this;
    }

    function endsWith($value)
    {
        $this->searchTerms["ends_with"] = strval($value);
        return $this;
    }

    function contains($value)
    {
        $this->searchTerms["contains"] = strval($value);
        return $this;
    }

    function toParam()
    {
        return $this->searchTerms;
    }
}
?>
