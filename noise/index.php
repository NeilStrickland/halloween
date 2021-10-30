<?php

echo <<<HTML
<html>
<head>
<script type="text/javascript">

function noise(f) {
 var a = document.getElementById('a');

 if (a.canPlayType('audio/mpeg')) {  
   a.type = "audio/mpeg";
   a.src = f + '.mp3';
 } else if (a.canPlayType('audio/aac')) {
   a.type = "audio/aac";
   a.src = f + '.aac';
 } else {
  alert('Using nothing');
 }

 a.src = f + '.mp3';
 a.autoplay = true;
 a.load();
}

</script>
</head>
<body>
<audio id="a" type="audio/mp3" src="run.mp3" controls>
<source type="audio/mp3" src="run.mp3"></source>
No audio.
</audio>

<ul>

HTML;

foreach (glob("*.mp3") as $f) {
 $g = substr($f,0,-4);

 echo <<<HTML
 <li><a onclick="noise('$g')">$g</a></li>

HTML;
}

echo <<<HTML
</ul>
</body>
</html>

HTML;

?>