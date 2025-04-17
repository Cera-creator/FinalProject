<?php
session_start();
$captcha_code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
$_SESSION['captcha'] = $captcha_code;

header('Content-type: image/png');

$image = imagecreatetruecolor(150, 50);
$bg_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);
$line_color = imagecolorallocate($image, 64, 64, 64);

imagefilledrectangle($image, 0, 0, 150, 50, $bg_color);

for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0,150), rand(0,50), rand(0,150), rand(0,50), $line_color);
}

$font_path = __DIR__ . '/OpenSans_Condensed-Light.ttf'; 
imagettftext($image, 24, 0, 20, 35, $text_color, $font_path, $captcha_code);

imagepng($image);
imagedestroy($image);
