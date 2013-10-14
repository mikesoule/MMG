<?php
/**
 * Abstract for data storage gateways.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 * @filesource
 */
namespace MMG\Model\Gateway;

require_once(dirname(__FILE__) . '/Exception.php');

use MMG\Model\Gateway\Exception;

/**
 * Abstract for data storage gateways.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
abstract class GatewayAbstract
{
    
    /**
     * Driver to be used by gateway.
     * No restrictions on what this can be (resource, class, etc.).
     *
     * @var mixed
     */
    protected $_driver;
    
    /**
     * List of required driver options.
     *
     * @var array
     */
    protected $_requiredOptions = array();
    
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
        
        $this->_requireOptions($options);
        $this->_initDriver($options);
        
    } // END function __construct
    
    /**
     * Returns the driver.
     *
     * @return  mixed
     */
    public function getDriver()
    {
        return $this->_driver;
        
    } // END function getDriver
    
    /**
     * Require driver options.
     *
     * @param   array $options
     * @return  void
     * @throws  Exception When required option not present
     */
    protected function _requireOptions(array $options)
    {
        foreach ($this->_requiredOptions as $name) {
            if (empty($options[$name])) {
                throw new Exception("A '$name' option is required for this gateway");
            }
        }
    } // END function _requireOptions
    
    /**
     * Enforced abstract method for initializing drivers.
     *
     * @param   array $options Array of options for the driver
     * @return  void
     */
    protected abstract function _initDriver(array $options);
    
} // END abstract class GatewayAbstract
