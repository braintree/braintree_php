<?php
namespace Braintree;

/**
 * Braintree OAuthCredentials module
 *
 * @package    Braintree
 * @category   Resources
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 *
 */
class OAuthCredentials extends Braintree
{
    protected function _initialize($attribs)
    {
        $this->_attributes = $attribs;
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * returns a string representation of the access token
     * @return string
     */
    public function __toString()
    {
        return __CLASS__.'['.Util::attributesToString($this->_attributes).']';
    }
}
