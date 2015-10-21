<?php
namespace Braintree;

use InvalidArgumentException;

class MultipleValueNode
{
    public function __construct($name, $allowedValues = array())
    {
        $this->name = $name;
        $this->items = array();
		$this->allowedValues = $allowedValues;
    }

    public function in($values)
    {
		$bad_values = array_diff($values, $this->allowedValues);
		if (count($this->allowedValues) > 0 && count($bad_values) > 0) {
			$message = 'Invalid argument(s) for ' . $this->name . ':';
			foreach ($bad_values AS $bad_value) {
				$message .= ' ' . $bad_value;
			}

			throw new InvalidArgumentException($message);
		}

        $this->items = $values;
        return $this;
    }

    public function is($value)
    {
        return $this->in(array($value));
    }

    public function toParam()
    {
        return $this->items;
    }
}
class_alias('Braintree\MultipleValueNode', 'Braintree_MultipleValueNode');
