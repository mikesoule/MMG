<?php
/**
 * Model Mapper
 * 
 * Handles the flow of data between models and data sources. Each model
 * should implement its own mapper class.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Mapper
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 * @filesource
 */
namespace MMG\Model\Mapper;

use MMG\Model\Model;
use MMG\Model\Collection;
use MMG\Model\Exception;
use MMG\Mapper\DataMap;
use MMG\Model\Gateway\GatewayInterface;
use DateTime;

/**
 * Require dependencies.
 */
require_once dirname(__FILE__) . '/Gateway/GatewayInterface.php';

/**
 * Model Mapper
 * 
 * Handles the flow of data between models and data sources. Each model
 * should implement its own mapper class.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Mapper
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
abstract class Mapper
{
    
    /**
     * Runtime storage for instantiated models to prevent unnecessary hits to
     * storage backends and prevent inconsistencies between multiple instances
     * of the same model.
     * 
     * Stored as $_modelInstances[Model_Class][Model_Identity] = $modelInstance
     *
     * @var array
     */
    protected static $_modelInstances = array();
    
    /**
     * The model class for this mapper.
     *
     * @var string
     */
    protected $_modelClass;
    
    /**
     * The identity property name for this mapper's model.
     *
     * @var string
     */
    protected $_idProperty = 'id';
    
    /**
     * Gateways, shared among all mappers.
     *
     * @var array
     */
    protected static $_gateways = array();
    
    /**
     * Constructor
     *
     * @param   array $options
     * @return  void
     */
    public function __construct($options = array())
    {
        if (array_key_exists('modelClass', $options)) {
            $this->_modelClass = $options['modelClass'];
        }
        
        if (array_key_exists('idProperty', $options)) {
            $this->_idProperty = $options['idProperty'];
        }
    } // END function __construct
    
    /**
     * Store the model.
     *
     * @param   Model $model
     * @param   boolean $cascade
     * @return  void
     * @todo    Implement cascading
     */
    public function save(Model $model, $cascade = false)
    {
        $isNew = !$model->getIdentity();
        $maps = $this->_mapToGateways($model->toArray());
        
        foreach ($maps as $map) {
            $gateway = $this->_getGateway($map->gateway);
            
            if ($isNew) {
                $map->id = $gateway->create(
                    $map->store, 
                    $map->data, 
                    $map->sequence
                );
                
                if ($idProperty = $map->idProperty) {
                    $model->$idProperty = $map->id;
                }
            } else {
                $gateway->update($map->store, $map->data, $map->criteria);
            }
        }
        
        $this->_addModelInstance($model);
    } // END function save
    
    /**
     * Delete the model.
     *
     * @param   Model $model
     * @param   boolean $cascade
     * @return  void
     * @todo    Implement cascading
     */
    public function delete(Model $model, $cascade = false)
    {
        if ($model->getIdentity()) {
            $maps = $this->_mapToGateways($model->toArray());

            foreach ($maps as $map) {
                $gateway = $this->_getGateway($map->gateway);
                $gateway->delete($map->store, $map->criteria);
            }
        }
        
    } // END function delete
    
    /**
     * Find models matching the specific criteria. A scalar value can be
     * passed as the identity.
     *
     * @param   mixed $criteria Search criteria or scalar identity
     * @return  Collection
     */
    public function find($criteria = array())
    {
        if ($model = $this->_getModelInstance($this->_modelClass, $criteria)) {
            return new Collection(array($model));
        }
        
        $map = $this->_mapToSearchGateway($criteria);
        $gateway = $this->_getGateway($map->gateway);
        $results = $gateway->read($map->store, $map->criteria);
        
        return $this->_getCollection($results);
        
    } // END function find
    
    /**
     * Find one model matching the specific criteria.
     *
     * @param   mixed $criteria Search criteria or scalar identity
     * @return  Model|void
     */
    public function findOne($criteria = array())
    {
        $collection = $this->find($criteria);
        
        if ($collection->count()) {
            return $collection->shift();
        }
        
    } // END function findOne
    
    /**
     * Place a model in the model stack.
     *
     * @param   Model $model
     * @return  void
     */
    protected function _addModelInstance(Model $model)
    {
        $identity = $model->getIdentity();
        
        self::$_modelInstances[$this->_modelClass][$identity] = $model;
        
    } // END function _addModelInstance
    
    /**
     * Get a model instance
     *
     * @param   string $class Model class name
     * @param   string|integer|array $identity Model identity
     * @return  Model|void
     */
    protected function _getModelInstance($class, $identity)
    {
        if (is_array($identity)) {
            if (count($identity) != 1) {
                return;
            }
            
            $identity = $this->_getIdentityFromCriteria($identity);
        }
        
        if (isset(self::$_modelInstances[$class][$identity])) {
            return self::$_modelInstances[$class][$identity];
        }
        
    } // END function _getModelInstance
    
    /**
     * Return the identity from criteria array.
     *
     * @param   array $criteria
     * @return  scalar|null
     */
    protected function _getIdentityFromCriteria(array $criteria)
    {
        if (isset($criteria[$this->_idProperty])) {
            return $criteria[$this->_idProperty];
        }
        
        return null;
        
    } // END function _getIdentityFromCriteria
    
    /**
     * Add a gateway for all mappers to use.
     *
     * @param   string $name
     * @param   GatewayInterface $gateway
     * @return  void
     */
    public static function addGateway($name, GatewayInterface $gateway)
    {
        self::$_gateways[$name] = $gateway;
        
    } // END function addGateway
    
    /**
     * Returns the named gateway.
     *
     * @param   string $name
     * @return  GatewayInterface
     */
    protected function _getGateway($name)
    {
        if (!array_key_exists($name, self::$_gateways)) {
            throw new Exception(
                "Gateway '$name' does not exist."
            );
        }
        
        return self::$_gateways[$name];
        
    } // END function _getGateway
    
    /**
     * Returns a collection of models from the given data.
     *
     * @param   array $results Result set as associative array
     * @return  Collection
     */
    protected function _getCollection(array $results)
    {
        $class = $this->_modelClass;
        $collection = new Collection;
        
        foreach ($results as $data) {
            $map = $this->_mapToModel($data);
            $model = new $class(array(
                'data' => $map->data
            ));
            $this->_addModelInstance($model);
            $collection->push($model);
        }
        
        return $collection;
    } // END function _getCollection
    
    /**
     * Map search criteria to gateway
     *
     * @param   array $criteria
     * @return  DataMap
     */
    abstract protected function _mapToSearchGateway(array $criteria);
    
    /**
     * Map model data to gateway.
     *
     * @param   array $data
     * @return  array Array of DataMap instances
     */
    abstract protected function _mapToGateways(array $data);
    
    /**
     * Map gateway data to model.
     *
     * @param   array $data
     * @return  DataMap
     */
    abstract protected function _mapToModel(array $data);

} // END class Mapper
