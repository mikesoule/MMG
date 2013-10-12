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
     * Gateway name for this map.
     *
     * @var string
     */
    public $gateway;
    
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
    
    /**
     * Constructor accepts configuration options.
     *
     * @param   array $options
     * @return  void
     */
    public function __construct($options = array())
    {
        settype($options, 'array');
        
        foreach ($options as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
        
    } // END function __construct
    
} // END class DataMap
