<?php

// http://goo.gl/2uzuuQ

db_connect();
get_params();

if ($cmd == 'show_history') {
 show_history();
} else if ($cmd == 'show_users') {
 show_users();
} else if ($cmd == 'send_tracks') {
 send_tracks();
} else {
 control_page();
}

exit;

//////////////////////////////////////////////////////////////////////

function db_connect() {
 global $db;

 if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'aim.shef.ac.uk') {
  $db = mysql_connect('localhost:3306','halloween_user','Cd24eqTQYdayAzvc');
 } else {
  $db = mysql_connect('localhost:3306','pm1nps_halloween','Cd24eqTQYdayAzvc');
 }

 if (! $db) { 
  trigger_error("Could not connect to database: "  . mysql_error(),E_USER_ERROR);
 }

 if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'aim.shef.ac.uk') {
  $db_name = 'halloween';
 } else {
  $db_name = 'pm1nps_halloween';
 }

 if (! mysql_select_db($db_name)) {
  trigger_error('Could not select database',E_USER_ERROR);
 }
}

//////////////////////////////////////////////////////////////////////

function get_params() {
 global $t,$cmd,$mobile;

 $t = time();

 $mobile = preg_match("/Mobile|iP(hone|od|ad)|Android|BlackBerry|IEMobile/",
		      $_SERVER['HTTP_USER_AGENT']);

 $cmd = 'control';

 if (isset($_REQUEST['cmd'])) {
  $cmd = $_REQUEST['cmd'];

  if ($cmd != 'show_users' &&
      $cmd != 'show_history' &&
      $cmd != 'send_tracks') {
   $cmd = 'control';
  }
 }
}

//////////////////////////////////////////////////////////////////////

function show_history() {
 $x = mysql_query("SELECT * FROM users");

 if ($x === FALSE) {
  trigger_error("Could not load from database: "  . mysql_error(),E_USER_ERROR);
 }

 $users = array();

 while($a = mysql_fetch_object($x)) {
  $a->tracks = array();
  $users[$a->id] = $a;
 }

 $sql = <<<SQL
SELECT * FROM tracks 
WHERE id <> 0 
 AND lat >= 53.334 AND lat <= 53.350
 AND lng >= -1.535 AND lng <= -1.506
SQL;

 $x = mysql_query($sql);
 
 if ($x === FALSE) {
  trigger_error("Could not load from database: "  . mysql_error(),E_USER_ERROR);
 }

 while($a = mysql_fetch_object($x)) {
  if (isset($users[$a->user_id])) {
   $users[$a->user_id]->tracks[] = array($a->t,$a->lat,$a->lng);
  }
 }
 
 $users_json = json_encode($users);

 echo <<<HTML
<html>
<head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" ></script>
<script src="//maps.googleapis.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">

var map;
var markers = {};
var paths = {};
var initial_users = $users_json;
var users = {};
var map_centre = {};
var map_home = {};

var cols = [
 'violet','red','green','blue','cyan','magenta','yellow','purple','orange'
];

function fetch() {
 $.ajax({
  type : 'GET',
  url : 'control.php',
  data : {cmd : 'send_tracks'},
  success : add_users
//  ,error : function(j,s,e) {alert('Ajax error: ' + s);}
 }).always(setTimeout(fetch,10000));
}

function add_users(U) {
 var i,u,v,P,PP;

 for (i in U) {
  u = U[i];

  if (users[u.id]) {
   v = users[u.id];
   v.tracks = u.tracks;

   PP = [];
   P = map_home;

   for (j in v.tracks) {
    P = new google.maps.LatLng(v.tracks[j][1],v.tracks[j][2]);
    P.time = v.tracks[j][0];
    PP.push(P);
   }

   v.path.setPath(PP);
   v.marker.setPosition(P);
  } else {
   users[u.id] = u;
   PP = [];
   P = map_home;

   for (j in u.tracks) {
    P = new google.maps.LatLng(u.tracks[j][1],u.tracks[j][2]);
    P.time = u.tracks[j][0];
    PP.push(P);
   }

   u.path = new google.maps.Polyline({
    map : map,
    strokeColor : cols[u.icon_id %9],
    path : PP
   });

   u.icon = new google.maps.MarkerImage('icons/' + u.icon_id + '.png',null,null,null,new google.maps.Size(30,30));

   u.marker = new google.maps.Marker({
    position : P,
    map: map,
    icon : u.icon,
    title : u.name + ' (' + u.id + ')' 
   });
  }
 }
}

$(document).ready(function() {

 $("#map").css({"width":$(document).width(),"height":$(document).height()});
 map_centre = new google.maps.LatLng(53.343809,-1.514333);
 map_home   = new google.maps.LatLng(53.346158,-1.525993);

 var map_opts = {
     zoom: 16,
     center: map_centre,
     MapTypeId: google.maps.MapTypeId.ROADMAP,
     zoomControl: true,
     streetViewControl: false
 };

 map = new google.maps.Map(document.getElementById("map"), map_opts);

// add_users(initial_users);
 fetch();
});

</script>
</head>
<body style="margin:0px">
<div id="map" style="margin:0px; width:1px; height: 1px"></div>
</body>
</html>

HTML;
}

//////////////////////////////////////////////////////////////////////

function send_tracks() {
 $x = mysql_query("SELECT * FROM users");

 if ($x === FALSE) {
  trigger_error("Could not load from database: "  . mysql_error(),E_USER_ERROR);
 }

 $users = array();

 while($a = mysql_fetch_object($x)) {
  $a->tracks = array();
  $users[$a->id] = $a;
 }

 $sql = <<<SQL
SELECT * FROM tracks 
WHERE id <> 0 
 AND lat >= 53.334 AND lat <= 53.350
 AND lng >= -1.535 AND lng <= -1.506
SQL;

 $x = mysql_query($sql);
 
 if ($x === FALSE) {
  trigger_error("Could not load from database: "  . mysql_error(),E_USER_ERROR);
 }

 while($a = mysql_fetch_object($x)) {
  if (isset($users[$a->user_id])) {
   $users[$a->user_id]->tracks[] = array($a->t,$a->lat,$a->lng);
  }
 }
 
 header('Content-Type: application/json');
 echo json_encode($users);
}

//////////////////////////////////////////////////////////////////////

function show_users() {
 global $mobile;

 $show_ua = isset($_REQUEST['ua']) && $_REQUEST['ua'];

 $x = mysql_query("SELECT * FROM users");

 if ($x === FALSE) {
  trigger_error("Could not load from database: "  . mysql_error(),E_USER_ERROR);
 }

 $users = array();

 while($a = mysql_fetch_object($x)) {
  $users[$a->id] = $a;
 }

 echo <<<HTML
<html>
<head>
<style type="text/css">

table.edged { 
 border-collapse: collapse;
}

table.edged td {
 padding: 3px 0.5em;
 margin-left: 3px;
 border: 1px solid #CCCCCC; 
}

</style>
</head>
<body>
<h1>Pumpkin hunters</h1>
<table class="edged">

HTML;

 foreach($users as $u) {

  $ua = '';
  if ($show_ua) {
   $ua = <<<HTML
    <td>{$u->user_agent}</td>

HTML;
  }

  echo <<<HTML
 <tr>
  <td width="20">{$u->id}</td>
  <td width="300">{$u->name}</td>
  <td width="20">{$u->icon_id}</td>
  <td><img src="icons/{$u->icon_id}.png"/></td>
$ua </tr>

HTML;
 }

 echo <<<HTML
</table>
<br/><br/>
<a href="control.php?cmd=show_history">Show tracks</a>

HTML;

 if ($show_ua) {
 echo <<<HTML
<br/><br/>
<a href="control.php?cmd=show_users">Hide browser information</a>

HTML;

 } else {
 echo <<<HTML
<br/><br/>
<a href="control.php?cmd=show_users&ua=1">Show browser information</a>

HTML;
 }

 echo <<<HTML
</body>
</html>

HTML;

}

//////////////////////////////////////////////////////////////////////

function control_page() {
 echo <<<HTML
<html>
<head>
</head>
<body>
<h1>Pumpkin hunt</h1>
<br/><br/>
<a href="control.php?cmd=show_users">Show users</a>
<br/><br/>
<a href="control.php?cmd=show_history">Show tracks</a>

</body>
</html>

HTML;
}

?>
