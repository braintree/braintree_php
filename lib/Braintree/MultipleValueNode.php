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

    function toParam()
    {
        return $this->items;
    }
}

?>
