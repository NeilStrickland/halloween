<?php

require_once('include/halloween.inc');

// http://goo.gl/2uzuuQ

$params = get_params();

if (1 || ($params->id && $params->lat && $params->lng)) {
 record_track($params);
}

if ($params->cmd == 'send_data') {
 send_data($params);
} else if ($params->cmd == 'show_map') {
 show_map($params);
} else if ($params->cmd == 'show_history') {
 show_history($params);
} else if ($params->cmd == 'register') {
 register($params);
} else if ($params->cmd == 'preregister' || ! $params->id) {
 registration_page($params);
} else {
 show_map($params);
}

exit;

//////////////////////////////////////////////////////////////////////

function get_params() {
 global $halloween;

 $params = new stdClass();
 
 $params->id = 0;
 $params->user = null;
 $params->lat = 0;
 $params->lng = 0;
 $params->t = time();

 $params->is_mobile = preg_match("/Mobile|iP(hone|od|ad)|Android|BlackBerry|IEMobile/",
	   	                 $_SERVER['HTTP_USER_AGENT']);

 if (array_key_exists('halloween_id',$_SESSION)) {
  $params->id = $_SESSION['halloween_id'];
 }

 $params->id = (int) get_optional_parameter('id',$params->id);

 if ($params->id) {
  $params->user = $halloween->load('user',$params->id);
 }

 if ($params->user && ! $params->user->user_agent) {
  $params->user->user_agent = $_SERVER['HTTP_USER_AGENT'];
  $params->user->save();
 }

 $params->lat = (float) get_optional_parameter('lat',0);
 $params->lng = (float) get_optional_parameter('lng',0);
 $params->is_fake = get_optional_parameter('is_fake',0) ? 1 : 0;

 $cmds = array('show_map','send_data','preregister','register','show_history');
 $params->cmd = $params->id ? 'show_map' : 'preregister';
 $params->cmd = get_restricted_parameter('cmd',$cmds,$params->cmd);

 return $params;
}

//////////////////////////////////////////////////////////////////////

function record_track($params) {
 global $halloween;

 $x = $halloween->new_object('track');
 $x->user_id = $params->id;
 $x->t = $params->t;
 $x->lat = $params->lat;
 $x->lng = $params->lng;
 $x->save();
}

//////////////////////////////////////////////////////////////////////

function send_data($params) {
 global $halloween;
 
 $t0 = $params->t - 3000;
 $w = "x.t >= {$t0} AND x.lat <> 0 AND x.lng <> 0";
 $tracks = $halloween->load_where('tracks',$w);

 $tracks0 = array();

 foreach($tracks as $t) {
  $tracks0[$t->user_id] = array($t->lat,$t->lng,$t->icon_id); 
 }

 header('Content-Type: application/json');
 echo json_encode($tracks0);
 exit;
}

//////////////////////////////////////////////////////////////////////
 
 function scripts() {
  $s = <<<HTML
<script 
  src="https://code.jquery.com/jquery-3.6.0.js"
  integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
  crossorigin="anonymous">
</script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
  integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
  crossorigin=""
/>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
  integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
  crossorigin="">
</script>
<script src="halloween.js"></script>

HTML
 ;

  return $s;
 }

//////////////////////////////////////////////////////////////////////

function show_map($params) {
 global $halloween;

 $noises = array();

 chdir("noise");
 foreach(glob("*.mp3") as $f) {
  $noises[] = substr($f,0,-4);
 } 
 $noises_json = json_encode($noises);
 $scripts = scripts();

 echo <<<HTML
<html>
<head>
$scripts
</head>
<body style="margin:0px">
<audio id="noise">
<source src="" type="audio/mp3"/>
</audio>
<div id="start" style="font-size: 64px;">
<a href="javascript:halloween.init({$params->id})">Click to start</a>
</div>
<div id="map" style="margin:0px; width:1px; height: 1px"></div>
</body>
</html>

HTML;
}

//////////////////////////////////////////////////////////////////////

function show_history() {
 global $halloween;

 $w = <<<SQL
 id <> 0 
 AND lat >= 53.334 AND lat <= 53.350
 AND lng >= -1.535 AND lng <= -1.506
SQL;

 $tracks = $halloween->load_where('tracks',$w); 
 $tracks_json = json_encode($tracks);
 $scripts = scripts();

 echo <<<HTML
<html>
<head>
<meta http-equiv="refresh" content="30">
$scripts
</head>
<body style="margin:0px">
<div id="map" style="margin:0px; width:1px; height: 1px"></div>
</body>
</html>

HTML;
}

//////////////////////////////////////////////////////////////////////

function registration_page($params) {
 global $halloween;

 $users = $halloween->load_all('users');
 $icons_used = array();
 foreach($users as $u) { $icons_used[$u->icon_id] = 1; }

 $scripts = scripts();

 $css = <<<CSS
td.selected { border : 2px solid blue }
td.unselected { border : 2px solid white }

CSS;
 $image_width = 48;

 if ($params->is_mobile) {
  $css .= <<<CSS

body { font-size: 40px; }
td  { font-size: 40px; }
div #geo { font-size: 40px; }
input[type="text"] { font-size: 40px; }
input[type="submit"] { font-size: 40px; }

CSS;
  $image_width = 96;
 }

 echo <<<HTML
<html>
<head>
<style type="text/css">
{$css}
</style>
{$scripts}
<script type="text/javascript">

$(document).ready(function() {
 if (navigator.geolocation) {
  $("#geo").html("A box should pop up asking you whether to allow this site to track your location.  Please say yes.");
  navigator.geolocation.getCurrentPosition(function(){});
 } else {
  $("#geo").html("This device does not seem to have GPS, so you will not be able to use it for the pumpkin hunt.");
 }
});

</script>
</head>
<body>
<form name="reg_form" method="POST">
Please enter your name:<br/>
<input type="text" name="name" size="25"></input><br/>
and choose an icon below.
<div id="geo"></div>
<table>
<tr>

HTML;

 $j = 0;
 for ($i = 1; $i <= 32; $i++) {
  if (isset($icons_used[$i])) { continue; }

  echo <<<HTML
 <td id="image_td_{$i}" class="unselected">
  <input type="radio" id="icon_id_{$i}" onclick="halloween.reg_button_handler($i)" name="icon_id" value="$i">
  <img id="icon_img_{$i}" onclick="halloween.reg_image_handler($i)" width="$image_width" src="icons/$i.png"/>
 </td>

HTML;
  
  $j++;

  if ($j == 5) {
   echo <<<HTML
</tr>
<tr>

HTML;

   $j = 0;
  }
 }

 echo <<<HTML
</tr>
</table>
<input type="hidden" name="cmd" value="register"/>
<input type="submit" value="Submit"/>
</form>
</body>
</html>
HTML;
}

//////////////////////////////////////////////////////////////////////

function register($params) {
 global $halloween;

 $css = '';

 if ($params->is_mobile) {
  $css = <<<CSS

body { font-size: 40px; }
td  { font-size: 40px; }

CSS;
 }

 $head = <<<HTML
<html>
<head>
<style type="text/css">
$css
</style>
</head>
<body>

HTML;

 $foot = <<<HTML
</body>
</html>

HTML;

 $u = $halloween->new_object('user');
 $u->name = get_optional_parameter('name','');
 $u->icon_id = (int) get_optional_parameter('icon_id',0);
 if (! $u->name) {
  echo "{$head}You did not enter your name!{$foot}";
  exit;
 }

 if (! $u->icon_id) {
  echo "{$head}You did not choose an icon!{$foot}";
  exit;
 }

 $u->save();
 $_SESSION['halloween_id'] = $u->id;

 echo <<<HTML
$head
<img width="96" src="icons/{$u->icon_id}.png"/>
&nbsp;&nbsp;
Welcome, {$u->name}!  Your id number is {$u->id}.
<br/><br/>
You can click below to view the map.  Please check that it shows you in the 
right place.  It might take a minute to work out your position.  If it shows 
you in completely the wrong place, it may be getting confused by the WiFi.
Try turning off the WiFi and refreshing the page.
<br/><br/>
<a href="index.php?cmd=show_map">View the map</a>
$foot

HTML;
}

//////////////////////////////////////////////////////////////////////

function show_users($params) {
 // Stub
 exit;
}


?>
