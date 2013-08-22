<?php
/**
 * DataMap class for mapping data to storage end-points.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Mapper
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 * @filesource
 */
namespace MMG\model\Mapper;

/**
 * DataMap class for mapping data to storage end-points.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Mapper
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class DataMap
{
    
    /**
     * Name of the storage end-point.
     *
     * @var string
     */
    public $store;
    
    /**
     * Array of data to be stored (as key => value pairs).
     *
     * @var array
     */
    public $data = array();
    
    /**
     * Name of the sequence associated with this storage end-point.
     *
     * @var string
     */
    public $sequence = null;
    
    /**
     * Array of criteria (as key => value pairs) for 
     * saving data in storage end-point.
     *
     * @var array
     */
    public $criteria = array();
    
} // END class DataMap
