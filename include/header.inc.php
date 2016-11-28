<?php
  session_start();
  // Variablen:
  // $Ueberschrift: Überschrift der Seite
  // $OnLoad: Ergänzung des Body-Tags
  // USE_OVERLIB: Wenn 1, dann wird overlib.js eingebunden
  // USE_KALENDER: Wenn 1, dann werden die Kalender-Javascripts eingebunden
  // Öffnet die Datenbank
  include_once("config.php");
  include_once("Logins.inc.php");
  // <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN"
  // "http://www.w3.org/TR/html401/loose.dtd">
  if ( ! defined("USE_KALENDER") ) DEFINE("USE_KALENDER", 0);
  if ( ! defined("USE_OVERLIB") ) DEFINE("USE_OVERLIB", 0);
  $Wochentagnamen[0] = "Sonntag";
  $Wochentagnamen[1] = "Montag";
  $Wochentagnamen[2] = "Dienstag";
  $Wochentagnamen[3] = "Mittwoch";
  $Wochentagnamen[4] = "Donnerstag";
  $Wochentagnamen[5] = "Freitag";
  $Wochentagnamen[6] = "Samstag";

  // Login festhalten
  SaveLogin();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta name="author" content="Christoph">
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">

<link rel="shortcut icon" href="http://oszimt.de/favicon.ico">
<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/style.css">
<link rel="stylesheet" media="print" href="http://css.oszimt.de/styledruck.css">
<link rel="stylesheet" media="printerversion" href="http://css.oszimt.de/styledruck.css">
<?php
if ( isset($HeaderZusatz) ) echo $HeaderZusatz;
if ( USE_KALENDER ) echo '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/popcalendar.css">';
?>
<?php
if ( USE_KALENDER )
{
  echo '<script type="text/javascript" language="JavaScript" src="http://js.oszimt.de/common.js"></script>';
  echo '<script type="text/javascript" language="JavaScript" src="http://js.oszimt.de/popcalendar.js"></script>';
}
if ( ! isset($Ueberschrift) ) $Ueberschrift = "";
?>
<title><?=$Ueberschrift?></title>
<?php
if ( USE_OVERLIB ) {
/* Alte grüne Version auf weißem Fenster
  echo '<script type="text/javascript">
var ol_fgcolor="#EFFFEB";
var ol_bgcolor="#008000";
</script>'; */
  echo '<script src="http://js.oszimt.de/overlib.js" type="text/javascript"></script>';
}
?>
</head>
<body text="#000000" bgcolor="#FFFFFF" link="#FF0000" alink="#FF0000" vlink="#FF0000"
<?php
if ( isset($OnLoad) )
  echo $OnLoad;
echo ">";
if ( USE_OVERLIB ) {
echo '<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>';
}
?>
<table cellpadding = "5" border = "0" width = "100%" bgcolor = "#eeeeee">
<tr>
 <td width = "1%">
 <a href="http://www.oszimt.de/"><img src="http://img.oszimt.de/logo/oszimt-logo.gif" alt="OSZ IMT (Logo)" border="0"></a>
 </td>
 <td>
    <span class = "osz">Oberstufenzentrum<br>Informations- und Medizintechnik</span>
 </td>
 <td align="right"> <span class="small" align="right"><?php
 if ( ! isset($_REQUEST["Print"]) ) echo getLastLogin(); ?></span>
 </td>
</tr>
</table>
<table cellpadding = "5" border = "0" width = "100%" bgcolor = "#eeeeee">
<tr><td width="90%" valign="top">
<table border = "0" width = "100%" bgcolor = "#dddddd">
<tr>
 <td align = "center"><a id="top" name="top"></a>
 <span class = "ueberschrift"><strong><?=$Ueberschrift?></strong></span>
 </td>
</tr>
