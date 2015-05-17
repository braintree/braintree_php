<?php
namespace Braintree;

class ThreeDSecureInfo extends Braintree
{
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);

        return $instance;
    }

    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
    }

    /**
     * returns a string representation of the three d secure info
     * @return string
     */
    public function  __toString()
    {
        return __CLASS__.'['.Util::attributesToString($this->_attributes).']';
    }
}