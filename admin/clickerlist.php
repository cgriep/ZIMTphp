<?php
$Ueberschrift = 'Auswertung Homepage-Clicks';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');
echo '<tr><td>';

//$query = mysql_query('SELECT LastClick FROM T_URLClicks ')

$Order = 'Anzahl';
$Richtung = 'DESC';
if ( isset($_REQUEST['Sort']))
{
	switch ( $_REQUEST['Sort'] )
	{
		case 0: $Order = 'URL';
		break;
		case 1: $Order = 'Anzahl';
		break;
		case 2: $Order = 'LastClick';
		break;
		case 3: $Order = 'Referer';
		break;
	}
}
if ( isset($_REQUEST['Richtung']) && ($_REQUEST['Richtung'] == 'DESC'
|| $_REQUEST['Richtung'] == 'ASC'))
{
	$Richtung = $_REQUEST['Richtung'];
}
if ( $Richtung == 'ASC')
$AndereRichtung = 'DESC';
else
$AndereRichtung = 'ASC';

if ( ! isset($_REQUEST['URL']))
{
	$sql = 'SELECT URL, Count(URL) AS Anzahl '.
   'FROM T_URLClicks GROUP BY URL ORDER BY '.$Order.' '.$Richtung;
	if ( ! $query = mysql_query($sql))
	{
		echo '<div class="Fehler">Datenbankfehler: '.mysql_error().'</div>';
	}
	else
	{
		echo '<table class="Liste">';
		echo '<tr><th><a href="?Sort=0&Richtung='.$AndereRichtung.'">Ziel-URL ';
		if ( $Order == 'URL' )
		{
			echo '<img src="http://img.oszimt.de/s_'.strtolower($Richtung).'.png" alt="" />';
		}
		echo '</a></th><th><a href="?Sort=1&Richtung='.$AndereRichtung.'">Anzahl Klicks ';
		if ( $Order == 'Anzahl' )
		{
			echo '<img src="http://img.oszimt.de/s_'.strtolower($Richtung).'.png" alt="" />';
		}
		echo '</a></th></tr>';

		while ( $row = mysql_fetch_array($query) )
		{
			echo '<tr><td><a href="?URL='.htmlentities($row['URL']).'">'.
			$row['URL'].'</a></td><td>'.$row[1].'</td></tr>';
		}
		mysql_free_result($query);
		echo '</table>';
	}
}
else
{
	if ( ! isset($_REQUEST['Sort'])) $Order = 'LastClick';
$sql = 'SELECT LastClick, Referer'.
   ' FROM T_URLClicks WHERE URL="'.mysql_real_escape_string($_REQUEST['URL']).
       '" ORDER BY '.$Order.' '.$Richtung;
	if ( ! $query = mysql_query($sql))
	{
		echo '<div class="Fehler">Datenbankfehler: '.mysql_error().'</div>';
	}
	else
	{
		echo '<table class="Liste">';
		echo '<tr><th><a href="?Sort=2&Richtung='.$AndereRichtung.
		'&URL='.$_REQUEST['URL'].'">Klickzeitpunkt ';
		if ( $Order == 'LastClick' )
		{
			echo '<img src="http://img.oszimt.de/s_'.strtolower($Richtung).'.png" alt="" />';
		}
		echo '</a></th><th><a href="?Sort=3&Richtung='.$AndereRichtung.
		'&URL='.$_REQUEST['URL'].'">Referer ';
		if ( $Order == 'Referer' )
		{
			echo '<img src="http://img.oszimt.de/s_'.strtolower($Richtung).'.png" alt="" />';
		}
		echo '</a></th></tr>';

		while ( $row = mysql_fetch_array($query) )
		{
			echo '<tr><td>'.date('d.m.Y H:i',$row['LastClick']).'</td><td>'.$row['Referer'].'</td></tr>';
		}
		mysql_free_result($query);
		echo '</table>';
	}
}
echo '</td></tr>';
include("include/footer.inc.php");
?>