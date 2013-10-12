<?php
/**
 * Model collection class.
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 * @filesource
 */
namespace MMG\Model;

require_once dirname(__FILE__) . '/../Model.php';

use MMG\Model\Model;
use Iterator, Countable;

/**
 * Model collection class.
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class Collection
    implements Iterator, Countable
{
    
    /**
     * Array of model instances
     *
     * @var array
     */
    protected $_models = array();
    
    /**
     * Constructor
     *
     * @param   array $models Array of Model instances
     * @return  void
     */
    public function __construct(array $models = array())
    {
        foreach ($models as $model) {
            $this->push($model);
        }
    } // END function __construct
    
    /**
     * Find an object in the collection by its identity.
     *
     * @return  Model|null
     */
    public function getByIdentity($identity)
    {
        foreach ($this as $model) {
            if ($model->getIdentity() == $identity) {
                return $model;
            }
        }
        
        return null;
    } // END function findByIdentity
    
    /**
     * Push a model onto the end of the collection.
     *
     * @return  Collection Provides a fluent interface
     */
    public function push(Model $model)
    {
        $this->_models[] = $model;
        
        return $this;
    } // END function push
    
    /**
     * Pop a model off the end of the collection and return it.
     *
     * @return  Model
     */
    public function pop()
    {
        return array_pop($this->_models);
    } // END function pop
    
    /**
     * Prepends a model onto the collection.
     *
     * @return  Collection Provides a fluent interface
     */
    public function unshift(Model $model)
    {
        array_unshift($this->_models, $model);
        
        return $this;
    } // END function unshift
    
    /**
     * Shift a model off the beginning of the collection and return it.
     *
     * @return  Model
     */
    public function shift()
    {
        return array_shift($this->_models);
    } // END function shift
    
    /**
     * Returns the current model.
     *
     * @return  Model
     */
    public function current()
    {
        return current($this->_models);
    } // END function current
    
    /**
     * Returns the key/hash of the current model.
     *
     * @return  string
     */
    public function key()
    {
        return key($this->_models);
    } // END function key
    
    /**
     * Moves the current position to the next model.
     *
     * @return  void
     */
    public function next()
    {
        return next($this->_models);
    } // END function next
    
    /**
     * Resets the position to the first model and returns it.
     *
     * @return  Model
     */
    public function rewind()
    {
        return reset($this->_models);
    } // END function rewind
    
    /**
     * Checks if the current position is valid.
     *
     * @return  boolean
     */
    public function valid()
    {
        return (current($this->_models) !== false);
    } // END function valid
    
    /**
     * Return the number of models in the collection.
     *
     * @return  integer
     */
    public function count()
    {
        return count($this->_models);
    } // END function count
    
    /**
     * Sets a property on all models.
     *
     * @param   string $name
     * @param   mixed $value
     * @return  Collection Provides a fluent interface.
     */
    public function __set($name, $value)
    {
        foreach ($this as $model) {
            $model->$name = $value;
        }
        
        return $this;
    } // END function __set
    
    /**
     * Return an array of a property value from all models.
     *
     * @param   string $name
     * @return  array Array of values keyed by identity
     */
    public function __get($name)
    {
        $values = array();
        
        foreach ($this as $model) {
            $values[$model->getIdentity()] = $model->$name;
        }
        
        return $values;
        
    } // END function __get
    
    /**
     * Pass method calls down to each model in the collection.
     *
     * @return  mixed The result of the call on the last model
     */
    public function __call($method, $args)
    {
        $results = array();
        
        foreach ($this as $model) {
            $id = $model->getIdentity();
            $results[$id] = $this->_callModelMethod($model, $method, $args);
        }
        
        return $results;
        
    } // END function __call
    
    /**
     * Calls the specified method on the model and returns the result.
     * 
     * Note: Attempts to avoid the inefficient call_user_func_array().
     *
     * @return  mixed The results of the method call from the model.
     */
    protected function _callModelMethod(Model $model, $method, array $args = array())
    {
        switch (count($args)) {
            case 0:
                return $model->$method();
            case 1:
                return $model->$method($args[0]);
            case 2:
                return $model->$method($args[0], $args[1]);
            case 3:
                return $model->$method($args[0], $args[1], $args[2]);
            case 4:
                return $model->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array(array($model, $method), $args);
        }
    } // END function _callModelMethod
    
} // END class Collection