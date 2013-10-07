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
     * Returns the driver.
     *
     * @return  mixed
     */
    public function getDriver()
    {
        return $this->_driver;
        
    } // END function getDriver
    
} // END abstract class GatewayAbstract
