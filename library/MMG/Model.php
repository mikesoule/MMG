<?php
/**
 * Model class.
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 * @filesource
 */
namespace MMG\Model;

/**
 * Require dependencies.
 */
require_once dirname(__FILE__) . '/Model/Exception.php';
require_once dirname(__FILE__) . '/Model/Mapper.php';

use MMG\Model\Exception;
use MMG\Model\Mapper\Mapper;

/**
 * Model class.
 *
 * @category    MMG
 * @package     MMG/Model
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class Model
{
    
    /**
     * Mapper instance
     *
     * @var Mapper
     */
    protected $_mapper;

    /**
     * Name of mapper class
     *
     * @var string
     */
    protected $_mapperClass;

    /**
     * Array of model data
     *
     * @var array
     */
    protected $_data = array();
    
    /**
     * Model identity property
     *
     * @var string
     */
    protected $_idProperty = 'id';
    
    /**
     * Constructor
     * 
     * Accepts model data directly or as a property of options called 'data'.
     *
     * @param   array|null $options
     * @return  void
     */
    public function __construct($options = array())
    {
        $this->_setOptions($options);
    } // END function __construct
    
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

        if (!array_key_exists($name, $this->_data)) {
            throw new Exception(
                'You cannot set new properties on this object'
            );
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
            throw new Exception("Undefined property: $name");
        }
        
        return $this->_data[$name];

    } // END function __get

    /**
     * Isset override
     *
     * @param   string $name Property name
     * @return  boolean
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    } // END function __isset

    /**
     * Unset override
     *
     * @param   string $name Property name
     * @return  void
     */
    public function __unset($name)
    {
        if (isset($this->_data[$name])) {
            $this->$name = null;
        }
    } // END function __unset
    
    /**
     * Pass the model to the mapper for storage
     *
     * @return  Model
     */
    public function save($cascadeSave = false)
    {
        $this->getMapper()->save($this, $cascadeSave);

        return $this;

    } // END function save
    
    /**
     * Pass the model to the mapper for deletion.
     *
     * @return  Model Provides a fluent interface
     */
    public function delete()
    {
        return (bool) $this->getMapper()->delete($this);
        
        return $this;
        
    } // END function delete
    
    /**
     * Set the model's identity.
     *
     * @param   scalar $identity
     * @return  Model Provides a fluent interface
     */
    public function setIdentity($identity)
    {
        $property = $this->_idProperty;
        $this->$property = $identity;
        
        return $this;
        
    } // END function setIdentity
    
    /**
     * Return the model's identity
     *
     * @return  scalar
     */
    public function getIdentity()
    {
        $id = $this->_idProperty;
        
        return $this->$id;
        
    } // END function getIdentity
    
    /**
     * Set the model data from an array
     *
     * @param   array $data Array of model data
     * @param   boolean $updateSchema Add keys to $this->_data
     * @return  Model Provides a fluent interface
     */
    public function fromArray(array $data = array(), $updateSchema = false)
    {
        if ($updateSchema) {
            $keyFill = array_fill_keys(array_keys($data), null);
            $this->_data = array_merge($data, $this->_data);
        }
        
        foreach ($data as $name => $value) {
            if (!array_key_exists($name, $this->_data)) {
                continue;
            }
            
            $this->$name = $value;
        }

        return $this;

    } // END public function fromArray
    
    /**
     * Return model data as an array
     *
     * @return  array
     */
    public function toArray()
    {
        $data = array();

        foreach ($this->_data as $key => $val) {
            $data[$key] = $this->$key; // Preserve __get behavior
        }
        
        return $data;

    } // END function toArray
    
    /**
     * Set the mapper instance.
     *
     * @return  Mapper Provides a fluent interface
     */
    public function setMapper(Mapper $mapper)
    {
        $this->_mapper = $mapper;
        $this->setMapperClass(get_class($mapper));
        
        return $this;
        
    } // END function setMapper
    
    /**
     * Returns mapper instance
     *
     * @return  Mapper
     */
    public function getMapper()
    {
        if (!($this->_mapper instanceof Mapper)) {
            $mapperClass = $this->getMapperClass();
            $this->_mapper = new $mapperClass;
        }

        return $this->_mapper;

    } // END function getMapper
    
    /**
     * Sets the mapper class name for this model.
     *
     * @param   string $mapperClass
     * @return  Model Provides a fluent interface
     */
    public function setMapperClass($mapperClass)
    {
        $this->_mapperClass = (string) $mapperClass;
        
        return $this;
    } // END function setMapperClass
    
    /**
     * Return the mapper class name
     *
     * @return  string
     */
    public function getMapperClass()
    {
        if (empty($this->_mapperClass)) {
            $this->_mapperClass = get_class($this) . 'Mapper';
        }

        return $this->_mapperClass;

    } // END function getMapperClass
    
    /**
     * Setup class from options passed to constructor.
     *
     * @param   array $options Options passed to constructor
     * @return  void
     */
    protected function _setOptions(array $options)
    {
        $keys = array_fill_keys(
            array('mapperClass', 'mapper', 'data'), 
            null
        );
        
        if (!isset($options['data'])) {
            $options['data'] = array_diff_key($options, $keys);
        }
        
        $options = array_intersect_key($options, $keys);
        
        $data = $options['data'];
        unset($options['data']);
        
        if (!is_array($data)) {
            throw new Exception('Data must be an array.');
        }
        
        foreach ($options as $name => $value) {
            $this->$name = $value;
        }
        
        $this->fromArray($data, empty($this->_data));
        
    } // END function _setOptions
    
} // END class Model
