<?php

$cols = array(
 array(255,0,0),
 array(0,255,0),
 array(0,0,255),
 array(0,255,255),
 array(255,0,255),
 array(255,255,0),
 array(160,32,240),
 array(255,165,0),
 array(238,130,238)
);

for ($i = 1; $i <= 9; $i++) {
 $c = $cols[$i-1];

 $image = imagecreate(15,15);
 $white = imagecolorallocate($image,255,255,255);
 imagecolortransparent($image,$white);

 $fg = imagecolorallocate($image,$c[0],$c[1],$c[2]);
 imagefilledellipse($image,7,7,13,13,$fg);
 imagepng($image,"{$i}.png");
}

for ($i = 10; $i <= 18; $i++) {
 $c = $cols[$i-10];

 $image = imagecreate(15,15);
 $white = imagecolorallocate($image,255,255,255);
 imagecolortransparent($image,$white);

 $fg = imagecolorallocate($image,$c[0],$c[1],$c[2]);
 imagefilledrectangle($image,5,0,10,14,$fg);
 imagefilledrectangle($image,0,5,14,10,$fg);
 imagepng($image,"{$i}.png");
}

for ($i = 19; $i <= 27; $i++) {
 $c = $cols[$i-19];

 $image = imagecreate(15,15);
 $white = imagecolorallocate($image,255,255,255);
 imagecolortransparent($image,$white);

 $fg = imagecolorallocate($image,$c[0],$c[1],$c[2]);
 imagefilledpolygon($image,array(0,0,14,0,7,14),3,$fg);
 imagepng($image,"{$i}.png");
}

echo "<div style=\"background: e0e0e0;\"><br/>";
for ($i = 1; $i <= 27; $i++) {
 echo "<img src=\"$i.png\"/>&nbsp;";
}
echo "<br/><br/></div>";

?>