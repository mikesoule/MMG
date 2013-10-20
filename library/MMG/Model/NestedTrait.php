<?php
/**
 * Nested model trait.
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 * @filesource
 */
namespace MMG\Model;

/**
 * Nested model trait.
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
trait NestedTrait
{
    
    /**
     * Setter override
     *
     * @param   string $name Property name
     * @param   mixed $value Property value
     * @return  Model Provides a fluent interface
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        $this->_data[$name] = $value;

        return $this;

    } // END function __set

    /**
     * Getter override
     *
     * @param   string $name Property name
     * @return  mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        if (!array_key_exists($name, $this->_data)) {
            $this->_data[$name] = new NestedProperty;
        }
        
        return $this->_data[$name];

    } // END function __get
    
} // END trait NestedTrait

/**
 * Nested Property class.
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class NestedProperty
{

    /**
     * Getter override
     *
     * @param   string $name Property name
     * @return  mixed
     */
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            $this->$name = new self;
        }
        
        return $this->$name;

    } // END function __get
    
} // END class NestedProperty
