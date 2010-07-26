<?php
class Braintree_Modification extends Braintree
{
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
