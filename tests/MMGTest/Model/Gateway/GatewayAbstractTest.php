<?php
/**
 * Tests for the gateway abstract.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
namespace MMGTest\Model\Gateway;

/**
 * @todo    Setup autoloading and remove this include.
 */
require_once dirname(__FILE__) . '/../../../../library/MMG/Model/Gateway/GatewayAbstract.php';

use PHPUnit_Framework_TestCase as TestCase;
use MMG\Model\Gateway;
use stdClass;
use ReflectionProperty;

/**
 * Tests for the gateway abstract.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class GatewayAbstractTest extends TestCase
{
    
    /**
     * Set up the test
     */
    public function setUp ()
    {
    }

    /**
     * Tear down the test
     */
    public function tearDown ()
    {}
    
    /**
     * Ensure the driver is returned as expected.
     *
     * @return  mixed
     */
    public function testGetDriver()
    {
        $gateway = $this->getMockForAbstractClass('MMG\Model\Gateway\GatewayAbstract');
        
        $expected = new stdClass;
        $expected->name = 'testDriver';
        $expected->options = array(
            'host'  => 'storage.example.com',
            'user'  => 'someone',
            'pass'  => 'mypass',
        );
        
        $property = new ReflectionProperty('MMG\Model\Gateway\GatewayAbstract', '_driver');
        $property->setAccessible(true);
        $property->setValue($gateway, $expected);
        
        $this->assertSame($expected, $gateway->getDriver());
        
    } // END function testGetDriver
    
} // END class GatewayAbstractTest extends TestCase