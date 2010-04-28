<?php

class Braintree_MultipleValueNode
{
    function __construct($name)
    {
        $this->name = $name;
        $this->items = array();
    }

    function in($values)
    {
        $this->items = $values;
        return $this;
    }

    function is($value)
    {
        return $this->in(array($value));
    }

    function toParam()
    {
        return $this->items;
    }
}

?>
