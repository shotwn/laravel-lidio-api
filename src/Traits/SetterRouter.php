<?php

namespace Shotwn\LidioAPI\Traits;

use Error;

/**
 * This trait allows us to use setters for properties.
 * This allows us to do extra stuff like sanity checks before setting the property.
 * It is configured to use name + 'Setter' as the setter method name.
 * So if you have a property called $foo, you can define a setter method called fooSetter($value).
 *
 * If setter method is not defined, it will just set the property directly.
 *
 * It will throw error if property is not defined.
 *
 * ! Warning: This trait does not use a whitelist. So it will allow setting any property.
 *
 * @throws Error - If property is not defined
 */
trait SetterRouter
{
    public function __set($name, $value)
    {
        // Check if this class has a setter for the property
        // This way we can define custom setters to do extra stuff like type checking
        $setter = $name . 'Setter';
        if (method_exists($this, $setter)) {
            $this->{$setter}($value);
        } else {
            // Does class have the property?
            if (!property_exists($this, $name)) {
                throw new Error("Property $name does not exist in " . __CLASS__);
            }

            // Set the property directly
            $this->{$name} = $value;
        }

        return $this;
    }
}
