<?php

global $halloween;

$halloween->add_table(
 'track','tracks',
 array(
  'id' => array('type' => 'integer', 'notnull' => true),
  'user_id' => array('type' => 'integer'),
  't' => array('type' => 'integer'),
  'lat' => array('type' => 'float'),
  'lng' => array('type' => 'float')
 ),
 array(
  'icon_id' => array('type' => 'integer'),
 ),<<<SQL
SELECT
 x.id,
 x.user_id,
 u.icon_id,
 x.t,
 x.lat,
 x.lng
FROM tbl_tracks x
 LEFT JOIN tbl_users u ON u.id = x.user_id
WHERE %s
ORDER BY x.id
SQL
);

class track extends frog_object {
 function __construct($id = null,$with_defaults=1) {
  global $halloween;

  parent::__construct($halloween,'track',$id,$with_defaults);
 }
}
