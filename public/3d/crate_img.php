<?php 
	error_reporting(E_ALL);
	$text = (isset($_GET['num'])) ? (int) $_GET['num'] : '0';
	
	$width = 256;
	$height = 256;
    $per = imagecreatetruecolor($width, $height); 
    $foreground = imagecolorallocate ($per, 200, 0, 0); // rgb
	$img = imagecreatefromjpeg("crate.jpg");
	$per2 = imagecopy($per, $img, 0, 0, 0, 0, $width, $height);
	$len = strlen((string) $text);
	$text_x = floor(128 - (($len * 30) / 2));
	$text_y = 230;
	imagettftext ($per, 40, 0, $text_x, $text_y, $foreground, "arialbd.ttf", $text);
    header("Content-type: image/jpeg");
    imagejpeg($per, null, 30);
	imagedestroy($per);
?>