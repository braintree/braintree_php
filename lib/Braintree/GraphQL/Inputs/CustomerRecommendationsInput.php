<?php

namespace Braintree\GraphQL\Inputs;

use Braintree\Base;
use Braintree\Util;

/**
 * Represents the input to request PayPal customer session recommendations.
 */
class CustomerRecommendationsInput extends Base
{
    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;

        if (isset($attributes['merchantAccountId'])) {
            $this->_set('merchantAccountId', $attributes['merchantAccountId']);
        }
        if (isset($attributes['sessionId'])) {
            $this->_set('sessionId', $attributes['sessionId']);
        }
        if (isset($attributes['recommendations'])) {
            $this->_set('recommendations', $attributes['recommendations']);
        }
        if (isset($attributes['customer'])) {
            $this->_set('customer', $attributes['customer']);
        }
    }

    private static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * Creates a builder instance for fluent construction of CustomerRecommendationsInput objects.
     *
     * @param string $sessionId       The customer session id
     * @param array  $recommendations The types of recommendations to be requested
     *
     * @return CustomerRecommendationsInputBuilder
     */
    public static function builder(string $sessionId, array $recommendations)
    {
        return new CustomerRecommendationsInputBuilder($sessionId, $recommendations, function ($attributes) {
            return self::factory($attributes);
        });
    }

    // phpcs:ignore PEAR.Commenting.FunctionComment.Missing
    public function __toString()
    {
        return __CLASS__ . '[' .
            Util::attributesToString($this->_attributes, true) . ']';
    }
}
