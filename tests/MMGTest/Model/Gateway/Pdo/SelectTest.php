<?php
/**
 * Tests for the Select class.
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
require_once dirname(__FILE__) . '/../../../../../library/MMG/Model/Gateway/Pdo/Select.php';

use PHPUnit_Framework_TestCase as TestCase;
use MMG\Model\Gateway\Pdo\Select;
use ReflectionClass, ReflectionProperty;

/**
 * Tests for the Select class.
 *
 * @category    MMG
 * @package     MMG/Model
 * @subpackage  MMG/Model/Gateway
 * @copyright   Copyright (c) 2013 Mike Soule
 * @license     http://mikesoule.github.com/license.html New BSD License
 * @version     Release: 0.0.1
 */
class SelectTest extends TestCase
{

    /**
     * Set up the test
     */
    public function setUp ()
    {}

    /**
     * Tear down the test
     */
    public function tearDown ()
    {}
    
    /**
     * Provides data for testing the constructor.
     *
     * @return  array
     */
    public function provideTest__construct()
    {
        return array(
            'no-from' => array(
                'SELECT CURRENT_TIMESTAMP',
                array('CURRENT_TIMESTAMP'),
            ),
            'basic' => array(
                'SELECT id, first_name, last_name FROM authors',
                array('id', 'first_name', 'last_name'),
                'authors',
            ),
            'alias' => array(
                'SELECT a.id, a.first_name, a.last_name FROM authors AS a',
                array('a.id', 'a.first_name', 'a.last_name'),
                'authors',
                'a',
            ),
        );
    } // END function provideTest__construct
    
    /**
     * Test the constructor.
     *
     * @param   string $expected The expected SQL string
     * @param   array|null $columns
     * @param   string|null $table
     * @param   string|null $alias
     * @return  void
     * @dataProvider    provideTest__construct
     */
    public function test__construct($expected, $columns = null, $table = null, $alias = null)
    {
        $select = new Select($columns, $table, $alias);
        $result = $select->getSql();
        
        $this->assertEquals($expected, $result);
        
    } // END function test__construct
    
    /**
     * Tests the from method.
     *
     * @return  void
     */
    public function testFrom()
    {
        $select = new Select;
        $select->from('books');
        
        $expected = 'SELECT * FROM books';
        $result = $select->getSql();
        
        $this->assertEquals($expected, $result);
        
    } // END function testFrom
    
    /**
     * Provides data for testing the getSql method.
     *
     * @return  array
     */
    public function provideGetSql()
    {
        return array(
            'basic' => array(
                "SELECT * FROM users",
                array(
                    'SELECT'    => array('*'),
                    'FROM'      => 'users',
                    'JOINS'     => array(),
                    'WHERE'     => array(),
                    'GROUP'     => array(),
                    'HAVING'    => array(),
                    'ORDER'     => array(),
                    'LIMIT'     => null,
                    'OFFSET'    => null,
                ),
            ),
            'aliased' => array(
                "SELECT u.id, u.email FROM users AS u WHERE u.id = 1234",
                array(
                    'SELECT'    => array('u.id', 'u.email'),
                    'FROM'      => array('u' => 'users'),
                    'JOINS'     => array(),
                    'WHERE'     => array(array('u.id', '=', 1234)),
                    'GROUP'     => array(),
                    'HAVING'    => array(),
                    'ORDER'     => array(),
                    'LIMIT'     => null,
                    'OFFSET'    => null,
                ),
            ),
            'where' => array(
                "SELECT * FROM users WHERE id = 1234",
                array(
                    'SELECT'    => array('*'),
                    'FROM'      => 'users',
                    'JOINS'     => array(),
                    'WHERE'     => array(array('id', '=', 1234)),
                    'GROUP'     => array(),
                    'HAVING'    => array(),
                    'ORDER'     => array(),
                    'LIMIT'     => null,
                    'OFFSET'    => null,
                ),
            ),
            'single-join' => array(
                "SELECT u.id, u.email, a.zip FROM users AS u JOIN addresses a ON a.user_id = u.id WHERE u.id = 1234",
                array(
                    'SELECT'    => array('u.id', 'u.email', 'a.zip'),
                    'FROM'      => array('u' => 'users'),
                    'JOINS'     => array(array('JOIN', array('a' => 'addresses'), array('a.user_id' => 'u.id'))),
                    'WHERE'     => array(array('u.id', '=', 1234)),
                    'GROUP'     => array(),
                    'HAVING'    => array(),
                    'ORDER'     => array(),
                    'LIMIT'     => null,
                    'OFFSET'    => null,
                ),
            ),
            'multi-join' => array(
                "SELECT u.id, u.email, a.zip, m.allow_email FROM users AS u INNER JOIN addresses a ON a.user_id = u.id LEFT JOIN marketing m ON m.user_id = u.id WHERE u.id = 1234",
                array(
                    'SELECT'    => array('u.id', 'u.email', 'a.zip', 'm.allow_email'),
                    'FROM'      => array('u' => 'users'),
                    'JOINS'     => array(
                        array('INNER JOIN', array('a' => 'addresses'), array('a.user_id' => 'u.id')),
                        array('LEFT JOIN', array('m' => 'marketing'), array('m.user_id' => 'u.id')),
                    ),
                    'WHERE'     => array(array('u.id', '=', 1234)),
                    'GROUP'     => array(),
                    'HAVING'    => array(),
                    'ORDER'     => array(),
                    'LIMIT'     => null,
                    'OFFSET'    => null,
                ),
            ),
            'group-having-order' => array(
                "SELECT a.first_name, a.last_name, count(*) AS num_books FROM authors AS a INNER JOIN books b ON b.author_id = a.id GROUP BY a.first_name, a.last_name HAVING num_books > 0 ORDER BY a.last_name ASC, a.first_name",
                array(
                    'SELECT'    => array(
                        'a.first_name',
                        'a.last_name', 
                        array('num_books' => 'count(*)'),
                    ),
                    'FROM'      => array('a' => 'authors'),
                    'JOINS'     => array(
                        array(
                            'INNER JOIN', 
                            array('b' => 'books'), 
                            array('b.author_id' => 'a.id'),
                        ),
                    ),
                    'WHERE'     => array(),
                    'GROUP'     => array('a.first_name', 'a.last_name'),
                    'HAVING'    => array(array('num_books', '>', 0)),
                    'ORDER'     => array('a.last_name ASC', 'a.first_name'),
                    'LIMIT'     => null,
                    'OFFSET'    => null,
                ),
            ),
        );
        
    } // END function provideGetSql
    
    /**
     * Ensure that the correct SQL string is returned.
     *
     * @param   string|Exception $expected The wxpected SQL string
     * @param   array $spec The specifications for the SQL statement
     * @return  void
     * @dataProvider    provideGetSql
     */
    public function testGetSql($expected, array $spec)
    {
        $select = new Select;
        
        $reflection = new ReflectionClass($select);
        $property = $reflection->getProperty('_spec');
        $property->setAccessible(true);
        $property->setValue($select, $spec);
        
        if ($expected instanceof Exception) {
            $this->setExpectedException($expected);
        }
        
        $result = $select->getSql();
        
        $this->assertEquals($expected, $result);
        
    } // END function testGetSql
    
} // END class SelectTest
