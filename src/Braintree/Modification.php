<?php
namespace Braintree;

class Modification extends Braintree
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

    public function __toString()
    {
        return get_called_class().'['.Util::attributesToString($this->_attributes).']';
    }
}
