<?php
/**
 * DB Table trait.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Mapper
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 * @filesource
 */
namespace MMG\Model\Mapper;

require_once dirname(__FILE__) . '/../Exception.php';
require_once dirname(__FILE__) . '/DataMap.php';

use MMG\Model\Exception;
use MMG\Model\Mapper\DataMap;

/**
 * DB Table trait.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Mapper
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
trait DbTableTrait
{
    
    /**
     * Map search criteria to gateway
     *
     * @param   array $criteria
     * @return  DataMap
     */
    protected function _mapToSearchGateway(array $criteria)
    {
        $map = new DataMap;
        $map->gateway = $this->_dbTableGateway;
        $map->store = $this->_dbTable;
        
        foreach ($criteria as $key => $val) {
            if (!array_key_exists($key, $this->_modelFields)) {
                throw new Exception("'$key' is not a valid search field.");
            }
            
            $field = $this->_modelFields[$key];
            $map->criteria[$field] = $val;
        }
        
        return $map;
        
    } // END function _mapToSearchGateway
    
    /**
     * Map model data to gateway.
     *
     * @param   array $data
     * @return  array Array of DataMap instances
     */
    protected function _mapToGateways(array $data)
    {
        $map = new DataMap;
        $map->gateway = $this->_dbTableGateway;
        $map->store = $this->_dbTable;
        
        foreach ($data as $key => $val) {
            if (array_key_exists($key, $this->_modelFields)) {
                $field = $this->_modelFields[$key];
                $map->data[$field] = $val;
            }
        }
        
        return array($map);
        
    } // END function _mapToGateway
    
    /**
     * Map gateway data to model.
     *
     * @param   array $data
     * @return  DataMap
     */
    protected function _mapToModel(array $data)
    {
        $fields = array_flip($this->_modelFields);
        $map = new DataMap;
        
        foreach ($data as $key => $val) {
            if (array_key_exists($key, $fields)) {
                $field = $fields[$key];
                $map->data[$field] = $val;
            }
        }
        
        return $map;
        
    } // END function _mapToModel
    
} // END trait DbTableTrait
