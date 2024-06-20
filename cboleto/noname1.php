<?php

function anti_bot($lenght=4)
{
$number = "";
for ($i = 1; $i <= $lenght; $i++)
{
$number .= rand(0,9)."";
}
$width = 11*$lenght;
$height = 30;

$img = ImageCreate($width, $height);
$background = imagecolorallocate($img,255,255,255);
$color_black = imagecolorallocate($img,0,0,0);
$color_grey = imagecolorallocate($img,169,169,169);
imagerectangle($img,0, 0,$width-1,$height-1,$color_grey);
imagestring($img, 5, $lenght, 7, $number, $color_black);
header("Content-type: image/png");
#header("Content-type: text/html");
imagepng($img);
imagedestroy($img);
}
anti_bot();

?>