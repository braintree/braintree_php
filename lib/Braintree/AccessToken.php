<?php
/**
 * Braintree AccessToken module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 */
class Braintree_AccessToken extends Braintree
{
    protected function _initialize($attribs)
    {
        $this->_attributes = $attribs;
    }

    public function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }
}
