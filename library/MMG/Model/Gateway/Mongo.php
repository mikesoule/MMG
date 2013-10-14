<?php
/**
 * Mongo Gateway class.
 * 
 * Provides a standard gateway interface to Mongo data sources.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
namespace MMG\Model\Gateway;

/**
 * Require the gateway abstract and interface.
 */
require_once dirname(__FILE__) . '/GatewayAbstract.php';
require_once dirname(__FILE__) . '/GatewayInterface.php';

/**
 * Mongo Gateway class.
 * 
 * Provides a standard gateway interface to Mongo data sources.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class Mongo extends GatewayAbstract implements GatewayInterface
{
    
    /**
     * Name of driver class to use.
     * 
     * NOTE: In this gateway, the driver is an instance of MongoDB but
     * MongoClient must be instantiated first and then provides
     * the MOngoDB instance.
     *
     * @var string
     */
    protected $_driverClass = '\MongoClient';
    
    /**
     * List of required driver options.
     *
     * @var array
     */
    protected $_requiredOptions = array('host', 'name');
    
    /**
     * Store a new record.
     *
     * @param   string $collection The collection for storing data
     * @param   array $data The data to be stored
     * @return  interger|string Unique identifier
     * @todo    Implement calling database functions
     */
    public function create($collection, array $data)
    {
        $data = $this->_setMongoId($data, true);
        
        $this->_driver->$collection->insert($data);
        
        return (string) $data['_id'];
        
    } // END function create
    
    /**
     * Read data from storage.
     *
     * @param   string $collection The collection to read from
     * @param   array $criteria Criteria for updating
     * @return  array Multi-dimensional array of data read from storage
     */
    public function read($collection, $criteria = array())
    {
        $criteria = $this->_setMongoId($criteria);
        
        $cursor = $this->_driver->$collection->find($criteria);
        
        return iterator_to_array($cursor);
        
    } // END function read
    
    /**
     * Update data in storage.
     *
     * @param   string $collection The collection to update
     * @param   array $data The data to be updated
     * @param   array $criteria Criteria for updating
     * @return  integer The number of items updated
     */
    public function update($collection, array $data, $criteria = array())
    {
        $data = $this->_setMongoId($data);
        $criteria = $this->_setMongoId($criteria);
        
        $this->_driver->$collection->update($criteria, $data);
        
        $results = $this->_driver->lastError();
        
        return $results['n'];
        
    } // END function update
    
    /**
     * Delete data from storage.
     *
     * @param   string $collection The collection to delete from
     * @param   array $criteria Criteria for deletion
     * @return  integer The number of items removed
     */
    public function delete($collection, $criteria = array())
    {
        $criteria = $this->_setMongoId($criteria);
        
        $this->_driver->$collection->remove($criteria);
        
        $results = $this->_driver->lastError();
        
        return $results['n'];
        
    } // END function delete
    
    /**
     * Sets the Mongo ID param in an array.
     *
     * @param   array $data
     * @param   boolean $force Force setting Mongo ID
     * @return  array
     */
    protected function _setMongoId(array $data, $force = false)
    {
        $mongoId = isset($data['_id']) ? $data['_id'] : null;
        
        if ($mongoId || $force) {
            unset($data['id']);
            $data = array('_id' => new \MongoId($mongoId)) + $data;
        }
        
        return $data;
        
    } // END function _setMongoId
    
    /**
     * Initialize the driver instance.
     *
     * @param   array $options
     * @return  void
     */
    protected function _initDriver(array $options)
    {
        $dsn = $this->_getDsn($options);
        $db = $options['name'];
        $driverOptions = array();
        
        if (isset($options['driverOptions'])) {
            $driverOptions = $options['driverOptions'];
        }
        
        $class = $this->_driverClass;
        $connection = new $class($dsn, $driverOptions);
        $this->_driver = $connection->$db;
        
    } // END function _initDriver
    
    /**
     * Return a DSN string from array of options.
     *
     * @param   array $options
     * @return  string
     */
    protected function _getDsn(array $options)
    {
        $dsn = 'mongodb://';
        
        if (isset($options['user'], $options['pass'])) {
            $dsn .= "{$options['user']}:{$options['pass']}@";
        }
        
        settype($options['host'], 'array');
        settype($options['port'], 'array');
        $firstKey = key($options['host']);
        
        foreach ($options['host'] as $key => $val) {
            $port = null;
            
            if (isset($options['port'][$key])) {
                $port = ':' . $options['port'][$key];
            }
            
            $dsn .= ($key == $firstKey) ? ',' : null;
            $dsn .= $val . $port;
        }
        
        return "$dsn/{$options['name']}";
        
    } // END function _getDsn
    
} // END class Mongo
