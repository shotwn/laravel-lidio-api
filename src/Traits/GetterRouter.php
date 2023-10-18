<?php

namespace Shotwn\LidioAPI\Traits;

use Error;

/**
 * This trait allows us to use getters for properties via a whitelist.
 * Whitelist is defined via $visibleProperties array.
 * So we do not give access to all the properties.
 *
 * There is also special case where you can set $visibleProperties to a boolean true.
 *
 * This allows us to do extra stuff like type casting before returning the property.
 * It is configured to use name + 'Getter' as the getter method name.
 * So if you have a property called $foo, you can define a getter method called fooGetter().
 *
 * If getter method is not defined, it will just return the property directly.
 *
 * It will throw error if property is not defined or not whitelisted.
 *
 * @throws Error - If property is not whitelisted or not defined
 */

trait GetterRouter
{
    public function __get($name)
    {
        // Check if whitelist exists
        if (!isset($this->visibleProperties)) {
            throw new Error('No \visibleProperties was defined in ' . __CLASS__);
        }

        // Check the whitelist
        if (is_array($this->visibleProperties)) {
            // Check if property is whitelisted in the array
            if (!in_array($name, $this->visibleProperties)) {
                throw new Error("Property $name is not whitelisted in " . __CLASS__);
            }
        } else if (is_bool($this->visibleProperties)) {
            // Check if whitelist is allowing all properties
            if ($this->visibleProperties !== true) { // Bool needs to be true to be enabled
                throw new Error("Property $name is not whitelisted in " . __CLASS__);
            }
        } else {
            // Invalid \visibleProperties was defined
            throw new Error('Invalid \visibleProperties was defined in ' . __CLASS__);
        }


        // Check if this class has a getter for the property
        $getter = $name . 'Getter';
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        } else {
            // Does class have the property?
            if (!property_exists($this, $name)) {
                throw new Error("Property $name does not exist in " . __CLASS__);
            }

            // Return the property directly
            return $this->{$name};
        }
    }
}
