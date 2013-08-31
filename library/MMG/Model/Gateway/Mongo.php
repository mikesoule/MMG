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
 * Require the gateway interface.
 */
require_once dirname(__FILE__) . '/GatewayInterface.php';

use MongoClient;

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
class Mongo implements GatewayInterface
{
    
    /**
     * MongoDB instance
     *
     * @var \MongoDB
     */
    protected $_driver;
    
    /**
     * Name of driver class to use.
     *
     * @var string
     */
    protected $_driverClass = 'MongoDB';
    
    /**
     * Constructor
     *
     * @param   array $options
     * @return  void
     */
    public function __construct($options = array())
    {    
        if (array_key_exists('driverClass', $options)) {
            $this->_driverClass = $options['driverClass'];
        }
        
        if (array_key_exists('driver', $options)) {
            $this->_setDriver($options['driver']);
        }
        
        if (is_object($this->_driver)) {
            $this->_initDriver($options);
        }
        
    } // END function __construct
    
    /**
     * Store a new record.
     *
     * @param   string $store The collection or function for storing data
     * @param   array $data The data to be stored
     * @param   string $sequence The sequence name for a primary/increment key
     * @param   boolean $isFunction Flag for calling database functions
     * @return  interger|string Unique identifier
     * @todo    Implement calling database functions
     */
    public function create($store, array $data, $sequence = null, $isFunction = false)
    {
        return $this->_insert($store, $data, $sequence);
    } // END function create
    
    /**
     * Initialize the driver instance.
     *
     * @param   array $options
     * @return  void
     */
    protected function _initDriver(array $options)
    {
        if (empty($options['host'])) {
            throw new \Exception('STOPPED HERE');
        }
        
        $class = $this->_driverClass;
        $this->_driver = new $class($dsn, $username, $password, $driverOptions);
    } // END function _initPdo
    
} // END class Mongo
