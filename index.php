<?php

require_once('include/halloween.inc');

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
} else if ($params->cmd == 'show_params') {
 show_params($params);
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

 $params->is_mobile =  preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$_SERVER['HTTP_USER_AGENT'])||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($_SERVER['HTTP_USER_AGENT'],0,4));

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

 $cmds = array('show_map','send_data','preregister','register','show_history','show_params');
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
  $h = md5_file('halloween.js');
  
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
<script src="halloween.js?{$h}"></script>

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
<a href="javascript:halloween.init({$params->id},{$params->is_mobile})">Click to start</a>
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

function show_params($params) {
 echo "<pre>";
 var_dump($params);
 exit;
}

//////////////////////////////////////////////////////////////////////

function show_users($params) {
 // Stub
 exit;
}


?>
