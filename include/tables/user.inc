<?php

global $halloween;

$halloween->add_table(
 'user','users',
 array(
  'id' => array('type' => 'integer', 'notnull' => true),
  'icon_id' => array('type' => 'integer'),
  'user_agent' => array('type' => 'text'),
  'last_seen' => array('type' => 'integer')
 ),
 array(),<<<SQL
SELECT
 x.id,
 x.icon_id,
 x.user_agent,
 x.last_seen
FROM tbl_users x
WHERE %s
ORDER BY x.id
SQL
);

class user extends frog_object {
 function __construct($id = null,$with_defaults=1) {
  global $halloween;

  parent::__construct($halloween,'user',$id,$with_defaults);
 }
}
