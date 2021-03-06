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
     * Name of the gateway for this mapper (corresponds to a key in the 
     * $_gateways stack).
     *
     * @var string
     */
    protected $_gateway;
    
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
        
        if (array_key_exists('gateway', $options)) {
            $this->_gateway = $options['gateway'];
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
                $gateway->update(
                    $map->store, 
                    $map->data, 
                    $map->criteria
                );
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
                
                $gateway->delete(
                    $map->store,
                    $map->criteria
                );
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
        $class = $this->_modelClass;
        $collection = new Collection();
        
        if ($model = $this->_getModelInstance($class, $criteria)) {
            $collection->push($model);
            return $collection;
        }
        
        $map = $this->_mapToSearchGateway($criteria);
        $gateway = $this->_getGateway($map->gateway);
        $results = $gateway->read($map->store, $map->criteria);
        
        foreach ($results as $data) {
            $map = $this->_mapToModel($data);
            $model = new $class(array(
                'data' => $map->data
            ));
            $this->_addModelInstance($model);
            $collection->push($model);
        }
        
        return $collection;
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
        $class = get_class($model);
        $identity = $model->getIdentity();
        
        self::$_modelInstances[$class][$identity] = $model;
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
            if (
                !array_key_exists($this->_idProperty, $identity) || 
                count($identity) != 1
            ) {
                return;
            }
            
            $identity = $identity[$this->_idProperty];
        }
        
        if (array_key_exists($class, self::$_modelInstances)) {
            if (array_key_exists($identity, self::$_modelInstances[$class])) {
                return self::$_modelInstances[$class][$identity];
            }
        }
        
    } // END function _getModelInstance
    
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
     * Convert DateTime object to string.
     *
     * @param   DateTime $date
     * @return  string
     */
    protected function _convertDateTime(DateTime $date)
    {
        if ($date->format('H:i:s') == '00:00:00') {
            return $date->format('Y-m-d');
        }
        
        return $date->format('Y-m-d H:i:s');
        
    } // END function _convertDateTime
    
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
