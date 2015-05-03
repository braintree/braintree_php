<?php
namespace Braintree;

class Plan extends Braintree
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

        $addOnArray = array();
        if (isset($attributes['addOns'])) {
            foreach ($attributes['addOns'] as $addOn) {
                $addOnArray[] = AddOn::factory($addOn);
            }
        }
        $this->_attributes['addOns'] = $addOnArray;

        $discountArray = array();
        if (isset($attributes['discounts'])) {
            foreach ($attributes['discounts'] as $discount) {
                $discountArray[] = Discount::factory($discount);
            }
        }
        $this->_attributes['discounts'] = $discountArray;

        $planArray = array();
        if (isset($attributes['plans'])) {
            foreach ($attributes['plans'] as $plan) {
                $planArray[] = self::factory($plan);
            }
        }
        $this->_attributes['plans'] = $planArray;
    }

    // static methods redirecting to gateway

    public static function all()
    {
        return Configuration::gateway()->plan()->all();
    }
}
