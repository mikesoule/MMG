<?php
/**
 * Interface for data storage gateways.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 * @filesource
 */
namespace MMG\Model\Gateway;

/**
 * Interface for data storage gateways.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
interface GatewayInterface
{
    
    /**
     * Store new data.
     *
     * @param   string $store The path/table/collection for the data
     * @param   array $data The data to be stored
     * @param   string|null $sequence The storage sequence name
     * @param   boolean $isFunction Flag for calling storage functions
     * @return  interger|string Unique identifier
     */
    public function create($store, array $data, $sequence = null, $isFunction = false);
    
    /**
     * Read data from storage.
     *
     * @param   string $store The path/table/collection to read from
     * @param   array $criteria Criteria for updating
     * @param   boolean $isFunction Flag for calling storage functions
     * @return  array Multi-dimensional array of data read from storage
     */
    public function read($store, $criteria = array(), $isFunction = false);
    
    /**
     * Update data in storage.
     *
     * @param   string $store The path/table/collection for the data
     * @param   array $data The data to be stored
     * @param   array $criteria Criteria for updating
     * @param   boolean $isFunction Flag for calling storage functions
     * @return  integer The number of items updated
     */
    public function update($store, array $data, $criteria = array(), $isFunction = false);
    
    /**
     * Delete data from storage.
     *
     * @param   string $store The path/table/collection to delete from
     * @param   array $criteria Criteria for deletion
     * @param   boolean $isFunction Flag for calling storage functions
     * @return  integer The number of items deleted
     */
    public function delete($store, $criteria = array(), $isFunction = false);
    
} // END interface GatewayInterface
