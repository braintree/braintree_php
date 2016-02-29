<?php

namespace Braintree;

abstract class Serializable
{
    protected $_attributes = array();
    
    public function serialize($as_array = false, $max_depth = PHP_INT_MAX)
    {
        if ($max_depth <= 0) return null;
        if (!isset($this->_attributes)) return array();
        if (!is_array($this->_attributes)) return array();
    
        $attributes = array();
        foreach ($this->_attributes as $k => $attribute)
        {
            if ($attribute instanceof Serializable)
                 $attributes[$k] = $attribute->serialize(true);
            else $attributes[$k] = $attribute;
        }
        
        if ($as_array)
             return $attributes;
        else return json_encode($attributes);
    } 
}

class_alias('Braintree\Serializable', 'Braintree_Serializable');