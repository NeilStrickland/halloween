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
 x.lng,
 u.icon_id
FROM tbl_tracks x
 LEFT JOIN tbl_users u ON u.id = x.user_id
WHERE %s
ORDER BY x.user_id,x.t,x.id
SQL
);

class track extends frog_object {
 function __construct($id = null,$with_defaults=1) {
  global $halloween;

  parent::__construct($halloween,'track',$id,$with_defaults);
 }

 function for_json() {
  $x = new stdClass();
  $x->id = $this->id;
  $x->user_id = $this->user_id;
  $x->t = $this->t;
  $x->lat = $this->lat;
  $x->lng = $this->lng;
  return $x;
 }

 function to_json() {
  return json_encode($this->for_json());
 }
}
