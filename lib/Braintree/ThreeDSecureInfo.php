<?php
namespace Braintree;

/**
 * @property-read boolean $liabilityShiftPossible
 * @property-read boolean $liabilityShifted
 * @property-read mixed $authentication contains $transStatus and $transStatusReason
 * @property-read mixed $lookup contains $transStatus and $transStatusReason
 * @property-read string $acsTransactionId
 * @property-read string $cavv
 * @property-read string $dsTransactionId
 * @property-read string $eciFlag
 * @property-read string $enrolled
 * @property-read string $paresStatus
 * @property-read string $status
 * @property-read string $threeDSecureAuthenticationId
 * @property-read string $threeDSecureServerTransactionId
 * @property-read string $threeDSecureVersion
 * @property-read string $xid
 */
class ThreeDSecureInfo extends Base
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
        return __CLASS__ . '[' .
                Util::attributesToString($this->_attributes) .']';
    }

}
