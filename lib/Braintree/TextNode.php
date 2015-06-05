<?php namespace Braintree;

class TextNode extends PartialMatchNode
{
    function contains($value)
    {
        $this->searchTerms["contains"] = strval($value);
        return $this;
    }
}
