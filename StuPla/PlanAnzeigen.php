<?php
DEFINE('USE_KALENDER',1);
$Ueberschrift = 'Plan mit Vertretungen';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">
<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/stupla.css">
<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszframe.css">';
// <link rel="stylesheet" type="text/css" href="http://css.oszimt.de/stuplatest.css">
include('include/header.inc.php');
echo '<tr><td>';

include('include/helper.inc.php');
include('include/stupla.inc.php');
include('include/Vertretungen.inc.php');
if ( ! isset($_REQUEST['Druck'])) $_REQUEST['Druck'] = false;
$Druck     = $_REQUEST['Druck'];    //true -> Druckansicht

if ( ! $Druck )
{
  $Felder = array('Lehrer', 'Klasse', 'Raum');	
  $datum = date('d.m.Y');
  if ( isset($_REQUEST['Datum']))
    $datum = $_REQUEST['Datum'];
  $dasDatum = explode('.',$datum);  
  $Version = getAktuelleVersion(mktime(0,0,0,$Datum[1],$Datum[0],$Datum[2]));
  foreach ( $Felder as $dieArt )
  {
    echo '<form action="'.$_SERVER['PHP_SELF'].'" name="Eingabe'.$dieArt.
      '" method="post" class="Verhinderung">';
    echo 'Plan von '.$dieArt.' <select name="'.$dieArt.'" size="1">';
    if ( $dieArt == 'Lehrer') 
      $FeldArt ='Name, Vorname, Lehrer';
    else
      $FeldArt = $dieArt;
    
    $query = mysql_query('SELECT DISTINCT '.$FeldArt.' FROM T_StuPla WHERE Version>='.
         $Version.' ORDER BY '.$FeldArt);
    while ($inhalt = mysql_fetch_array($query)) 
    {
    	echo '<option ';
    	if ( $dieArt == 'Lehrer')
    	{
    		echo 'value="'.$inhalt[2].'">';
    		echo trim($inhalt[0].", ".$inhalt[1]);
    	}  
    	else
    	  echo ">".$inhalt[0];
    	echo "</option>\n";
    } // while
    mysql_free_result($query);
    echo "</select>\n";
    echo 'für Datum <input type="Text" name="Datum" value="'.$datum.'" size="10" maxlength="10"';
    echo " onClick=\"popUpCalendar(this,Eingabe{$dieArt}['Datum'],'dd.mm.yyyy')\" ";
    echo "onBlur=\"autoCorrectDate('Eingabe$dieArt','Datum' , false )\"";
    echo '/>
  <input type="Submit" value="Anzeigen"/>
  </form>';
  } // foreach
} // if

unset($Art);
foreach ( array('Klasse', 'Lehrer', 'Raum') as $dieArt )
  if ( isset($_REQUEST[$dieArt]) )
    $Art = $dieArt;
if ( ! isset($Art) && isset($_REQUEST['Art']) )
{
  $Art = $_REQUEST['Art'];
  $_REQUEST[$Art] = $_REQUEST['Wofuer'];
}
if ( isset($_REQUEST['Datum']))
{
  $Datum = explode('.',$_REQUEST['Datum']);
  $_REQUEST['Datum'] = mktime(0,0,0,$Datum[1],$Datum[0],$Datum[2]);
  if ( date('w', $_REQUEST['Datum']) == 0 )
    $_REQUEST['Datum'] = strtotime('+2 day', $_REQUEST['Datum']);
  elseif ( date('w', $_REQUEST['Datum']) == 6)
    $_REQUEST['Datum'] = strtotime('+2 day', $_REQUEST['Datum']);
  $ID_Woche = getID_Woche($_REQUEST['Datum']);
}
if ( isset($_REQUEST['ID_Woche']) && is_numeric($_REQUEST['ID_Woche']) )
  $ID_Woche = $_REQUEST['ID_Woche'];

if ( ! isset($ID_Woche))
  $ID_Woche = '';
if ( isset($Art) && isset($ID_Woche) )
{
  $Plan = liesPlanEin($db, $Art, $_REQUEST[$Art], $ID_Woche,true);
  schreibePlan($Plan, $db);
}

echo '</td></tr>';
include('include/footer.inc.php');
?>