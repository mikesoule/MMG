<?php
array(
    'SELECT' => array('c.*'),
    'FROM' => array('c' => 'calendar'),
    'WHERE' => array(
        array('c.apptdate' => '>=', '2013-08-27'),
        'AND' => array(
            array('c.subject' => 'LIKE', '%Mike Soule%'), 
            'OR' => array('c.calendarid' => 'IN', array(
                'SELECT' => array('cu.calendarid)',
                'FROM' => array('cu' => 'calendarusers'),
                'JOIN' => array('u' => 'users', array(
                    'cu.userid' => 'u.userid'
                )),
                'WHERE' => array("CONCAT(u.userfirst, ' ', u.userlast)" => 'LIKE', '%Mike Soule%'),
            )),
        ),
        'AND' => array(
            array('c.subject' => 'LIKE', '%Trevor Meyer%'), 
            'OR' => array('c.calendarid' => 'IN', array(
                'SELECT' => array('cu.calendarid)',
                'FROM' => array('cu' => 'calendarusers'),
                'JOIN' => array('u' => 'users', array(
                    'cu.userid' => 'u.userid'
                )),
                'WHERE' => array("CONCAT(u.userfirst, ' ', u.userlast)" => 'LIKE', '%Trevor Meyer%'),
            )),
        ),
    ),
    'ORDER BY' => array('apptdate', 'enddate', 'calendarid'),
);

$search = array('%Mike Soule%', '%Trevor Meyer%');

$gateway->select(array('c.*'))->from('calendar', 'c')
    ->where('c.apptdate', '>=', '2013-08-27')
    ->andWhere('c.subject', 'LIKE', '%Mike Soule%', 'where1')
    ->orWhere('c.calendarid', 'IN', $this->getSql('sub1'), 'where1')
    ->andWhere('c.subject', 'LIKE', '%Trevor Meyer%', 'where2')
    ->orWhere('c.calendarid', 'IN', $this->getSql('sub2'), 'where2')
    ->order('c.apptdate', 'c.enddate', 'c.calendarid');

$select1 = $gateway->select(array('cu.calendarid'))->from('calendarusers', 'cu')
    ->join('users', 'u', 'cu.userid', 'u.userid')
    ->where("CONCAT(u.userfirst, ' ', u.userlast)", 'LIKE', '%Mike Soule%');

$select2 = $gateway->select(array('cu.calendarid'))->from('calendarusers', 'cu')
    ->join('users', 'u', 'cu.userid', 'u.userid')
    ->where("CONCAT(u.userfirst, ' ', u.userlast)", 'LIKE', '%Trevor Meyer%');

$gateway->select(array('c.*'))->from('calendar', 'c')
    ->where('c.apptdate', '>=', '2013-08-27')
    ->andNestedWhere(array(
        array(
            array('c.subject', 'LIKE', '%Mike Soule%'),
            'OR',
            array('c.calendarid', 'IN', $select1),
        ),
        'AND', array(
            array('c.subject', 'LIKE', '%Trevor Meyer%'),
            'OR',
            array('c.calendarid', 'IN', $select2),
        ),
    ))
    ->order('c.apptdate', 'c.enddate', 'c.calendarid');
    
    ->group()
    ->having()
    ->limit()
    ->offset()

/*
SELECT * FROM calendar 
WHERE apptdate >= '2013-08-27' AND (
    (subject LIKE '%Mike Soule%' OR calendarid IN (
        SELECT calendarid FROM calendarusers JOIN users ON (calendarusers.userid=users.userid) 
            WHERE CONCAT(users.userfirst, ' ', users.userlast) LIKE '%Mike Soule%'
    )) AND 
    (subject LIKE '%Trevor Meyer%' OR calendarid IN (
        SELECT calendarid FROM calendarusers JOIN users ON (calendarusers.userid=users.userid) 
            WHERE CONCAT(users.userfirst, ' ', users.userlast) LIKE '%Trevor Meyer%'
    ))
) 
ORDER BY apptdate, enddate, calendarid;
*/