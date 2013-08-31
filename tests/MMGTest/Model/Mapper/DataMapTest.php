<?php
/**
 * Tests for the DataMap.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Mapper
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
namespace MMGTest\Model\Mapper;

/**
 * @todo    Setup autoloading and remove this include.
 */
require_once dirname(__FILE__) . '/../../../../library/MMG/Model/Mapper/DataMap.php';

use PHPUnit_Framework_TestCase as TestCase;
use MMG\Model\Mapper\DataMap;

/**
 * Tests for the DataMap.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Mapper
 * @copyright   Copyright (c) 2012 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class DataMapTest extends TestCase
{
    /**
     * Set up the test.
     *
     * @return  void
     */
    public function setUp()
    {
    } // END function setUp
    
    /**
     * Tear down the test
     *
     * @return  void
     */
    public function tearDown()
    {
    } // END function tearDown
    
    /**
     * Test setting properties via the contstructor.
     *
     * @return  void
     */
    public function test__construct()
    {
        $options = array(
            'gateway'   => 'mysql',
            'store'     => 'mytable',
            'data'      => array(
                'id'        => 1234,
                'name'      => 'Some Name',
                'desc'      => 'Description of data',
            ),
            'sequence'  => 'mytable_id_seq',
            'criteria'  => array(
                'id'        => 1234,
                'name'      => 'Some Name',
            ),
        );
        
        $result1 = new DataMap;
        $result2 = new DataMap($options);
        
        $this->assertNull($result1->gateway);
        $this->assertNull($result1->store);
        $this->assertEmpty($result1->data);
        $this->assertNull($result1->sequence);
        $this->assertEmpty($result1->criteria);
        
        $this->assertEquals($options['gateway'], $result2->gateway);
        $this->assertEquals($options['store'], $result2->store);
        $this->assertEquals($options['data'], $result2->data);
        $this->assertEquals($options['sequence'], $result2->sequence);
        $this->assertEquals($options['criteria'], $result2->criteria);
        
    } // END function testSetPropertiesViaConstructor
} // END class DataMapTest