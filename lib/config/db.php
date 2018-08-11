<?php
return array(
    'cash_event' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'contact_id' => array('int', 11, 'null' => 0),
        'status' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        'comment' => array('text', 'null' => 0),
        'pub_date' => array('datetime', 'null' => 0),
        'total' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'cash_event_operation' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'event_id' => array('int', 11, 'null' => 0),
        'contact_id' => array('int', 11, 'null' => 0),
        'amount' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
);
