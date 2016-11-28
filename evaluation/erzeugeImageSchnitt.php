<?php
$schnitt = $_REQUEST['schnitt'];
$staabw  = $_REQUEST['staabw'];;

$breite = 140;
$hoehe  = 10;

$fuellgrad = round(($schnitt - 1) / 4 * $breite, 0);
//$posStaAbwLinks  = $fuellgrad - $staabw * $breite / 4; 
//$posStaAbwRechts = $fuellgrad + $staabw * $breite / 4; 

$img = imageCreateTrueColor($breite, $hoehe);

$gruen   = imageColorAllocate($img, 0, 255, 0);
$rot     = imageColorAllocate($img, 255, 0, 0);
$schwarz = imageColorAllocate($img, 0, 0, 0);

imageFilledRectangle($img, 0, 0, $fuellgrad - 1, $hoehe, $rot);
imageFilledRectangle($img, $fuellgrad - 1, 0, $breite, $hoehe, $gruen);
/*
if($posStaAbwLinks > 0)
  imageline($img, $posStaAbwLinks, 0, $posStaAbwLinks, $hoehe - 1, $schwarz);
if($posStaAbwRechts < $breite - 1)
  imageline($img, $posStaAbwRechts, 0, $posStaAbwRechts, $hoehe - 1, $schwarz);
*/
imageline($img, 0, 0, $breite - 1, 0, $schwarz);
imageline($img, 0, $hoehe - 1, $breite - 1, $hoehe - 1, $schwarz);
imageline($img, 0, 0, 0, $hoehe - 1, $schwarz);
imageline($img, $breite - 1, 0, $breite - 1, $hoehe - 1, $schwarz);
imageline($img, $breite - 1, 0, $breite - 1, $hoehe - 1, $schwarz);

$sektor = round($breite / 4, 0);
imageline($img, $sektor - 1, 0, $sektor - 1, $hoehe - 1, $schwarz);
imageline($img, $sektor * 2 - 1, 0, $sektor * 2 - 1, $hoehe - 1, $schwarz);
imageline($img, $sektor * 3 - 1, 0, $sektor * 3 - 1, $hoehe - 1, $schwarz);

imagePng($img);

imageDestroy($img);
?>
