<?
function imagefillroundedrect($im,$x,$y,$cx,$cy,$rad,$col) {
	// Draw the middle cross shape of the rectangle
	imagefilledrectangle($im,$x,$y+$rad,$cx,$cy-$rad,$col);
	imagefilledrectangle($im,$x+$rad,$y,$cx-$rad,$cy,$col);

	$dia = $rad*2;

	// Now fill in the rounded corners
	imagefilledellipse($im, $x+$rad, $y+$rad, $rad*2, $dia, $col);
	imagefilledellipse($im, $x+$rad, $cy-$rad, $rad*2, $dia, $col);
	imagefilledellipse($im, $cx-$rad, $cy-$rad, $rad*2, $dia, $col);
	imagefilledellipse($im, $cx-$rad, $y+$rad, $rad*2, $dia, $col);
}

function tLabel($image,$x,$y,$ch,$temp) {
	$white = imagecolorallocate($image, 255,255,255);
	$label=sprintf("T%d",$ch);

	/* draw a rounded rectangle (100x40) */
	imagefillroundedrect($image,$x-50,$y-20,$x+50,$y+20,10,imagecolorallocate($image, 102,149,46));

	/* channel label */
	ImageTTFText ($image, 20, 0, $x-48,   $y+10,   $white, "/home/jjeffers/data.aprsworld.com/admin/fonts/arialbd.ttf",$label);

	/* actual temperature */
	/* generate text for the label */
	if ( -99.9 == $temp) {
		$text="      Not\nConnected";
		ImageTTFText ($image, 9, 0, $x-13,   $y,   $white, "/home/jjeffers/data.aprsworld.com/admin/fonts/arialbd.ttf",$text);
	} else {
		$text=sprintf("%.1f&deg;C\n%.1f&deg;F",$temp,$temp*1.8+32);
		ImageTTFText ($image, 12, 0, $x-10,   $y-3,   $white, "/home/jjeffers/data.aprsworld.com/admin/fonts/arialbd.ttf",$text);
	}
}

function rLabel($image,$x,$y,$ch,$state) {
	$white = imagecolorallocate($image, 255,255,255);
	$label=sprintf("R%d",$ch);
	if ( $state ) {
		$label .= " ON";
		imagefillroundedrect($image,$x-45,$y-20,$x+45,$y+20,10,imagecolorallocate($image, 250,166,53));
		ImageTTFText ($image, 20, 0, $x-41,   $y+10,   $white, "/home/jjeffers/data.aprsworld.com/admin/fonts/arialbd.ttf",$label);
	} else {
		$label .= " OFF";
		imagefillroundedrect($image,$x-50,$y-20,$x+50,$y+20,10,imagecolorallocate($image, 250,166,53));
		ImageTTFText ($image, 20, 0, $x-48,   $y+10,   $white, "/home/jjeffers/data.aprsworld.com/admin/fonts/arialbd.ttf",$label);
	}


}


/* load background image */
$image = imagecreatefrompng('images/Collector-and-Tank.1024.png');
imagealphablending($image,true);
imagesavealpha($image,true);


$r['t0']=24.9;
$r['t0_x']=900;
$r['t0_y']=75;

$r['t1']=-99.9;
$r['t1_x']=450;
$r['t1_y']=90;

$r['t2']=-99.9;
$r['t2_x']=650;
$r['t2_y']=35;

$r['t3']=86.1;
$r['t3_x']=900;
$r['t3_y']=450;

$r['r0']=0;
$r['r0_x']=0;
$r['r0_y']=0;

$r['r1']=1;
$r['r1_x']=720;
$r['r1_y']=430;

/* temperature labels */
for ( $i=0 ; $i<4 ; $i++ ) {
	$cn=sprintf('t%d',$i);

	if ( ! isset($r[$cn]) || (0 == $r[$cn . "_x"] && 0 == $r[$cn . "_y"]) )
		continue;

	tLabel($image,$r[$cn . "_x"],$r[$cn . "_y"],$i+1,$r[$cn]);
}

/* relay labels */
for ( $i=0 ; $i<3 ; $i++ ) {
	$cn=sprintf('r%d',$i);
	
	if ( ! isset($r[$cn]) || (0 == $r[$cn . "_x"] && 0 == $r[$cn . "_y"]) )
		continue;

	rLabel($image,$r[$cn . "_x"],$r[$cn . "_y"],$i+1,$r[$cn]);
}


/* draw actual image */
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
