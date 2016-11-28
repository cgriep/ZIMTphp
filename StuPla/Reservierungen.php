<?php
/*
 * Created on 25.12.2005
 *
 * Zeigt eine Liste aller Reservierungen an, die der aktuelle Benutzer 
 * vorgenommen hat. Für den Benutzer Seidel werden alle Raumreservierungen 
 * angezeigt. Die Liste kann durch Klick auf die Überschriften 
 * sortiert werden.
 * Reservierungen, die vor dem aktuellen Datum liegen, werden automatisch 
 * ausgeblendet.
 * Über einen Link können Reservierungen storniert werden. Dazu wird das 
 * Skript RaumReservierung.php aufgerufen. Es muss im gleichen Verzeichnis 
 * liegen.
 * 
 * Parameter:
 * Sort - Feld nach dem die Liste sortiert wird. 
 *        Mögliche Werte: Lehrer_Neu, Klasse, Raum
 * 
 * Letzte Änderungen:
 * 08.01.06 C. Griep
 * 
 */
$Ueberschrift = 'Reservierungen anzeigen';
define('USE_KALENDER', 1);
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';

include('include/header.inc.php');
include('include/helper.inc.php');
include('include/stupla.inc.php');
include('include/Vertretungen.inc.php');
include('include/Lehrer.class.php');

echo "<tr><td>\n";
if ( isset($_REQUEST['Sort']))
{
	$Sort = mysql_real_escape_string($_REQUEST['Sort']).',';
}
else
  $Sort = '';
$user = strtoupper($_SERVER['REMOTE_USER']);
$sql = 'SELECT * FROM T_Vertretungen WHERE iKey IS NOT NULL AND ' .
		"iKey<>'' AND Datum>=".strtotime('-1day');
if ( $user != 'SEIDEL' && $user != 'GRIEP' )
{
  $L = new Lehrer($_SERVER['REMOTE_USER'],LEHRERID_EMAIL);
  $sql .= ' AND Lehrer_Neu="'.$L->Kuerzel.'"';
} 
$sql .= " ORDER BY $Sort Datum, Stunde";
$query = mysql_query($sql);
echo "<table class=\"Liste\">\n";
echo '<tr><th><a href="'.$_SERVER["PHP_SELF"].'">Datum, Stunde</a></th>';
if ( $user == "SEIDEL" || $user == "GRIEP")
  echo '<th><a href="'.$_SERVER["PHP_SELF"].'?Sort=Lehrer_Neu">Lehrer</a></th>';
echo '<th><a href="'.$_SERVER["PHP_SELF"].'?Sort=Klasse">Klasse</a>';
echo '</th><th><a href="'.$_SERVER["PHP_SELF"].'?Sort=Raum">Raum</a>';
echo "</th><th>Raum (alt)</th><th>Bemerkung</th></tr>\n";
while ($reservierung = mysql_fetch_array($query))
{
	echo '<tr><td>';
	echo '<a href="RaumReservierung.php?iKey='.$reservierung['iKey'];
    echo '" target="_blank"><img src="delete.gif" alt="Papierkorb" title="Löschen"/></a> ';
	echo $Wochentagnamen[date('w', $reservierung['Datum'])].", \n";
	echo date('d.m.Y',$reservierung['Datum']).' / ';
	echo $reservierung['Stunde']."</td><td>\n";
	if ( $user == 'SEIDEL' || $user == 'GRIEP')
	{
      $L = new Lehrer($reservierung['Lehrer_Neu'],LEHRERID_KUERZEL);
      echo $L->Name."</td><td>\n";
	}
	if ( $reservierung['Klasse_Neu'] == '')
	  echo '(keine)';
	else
	  echo $reservierung['Klasse_Neu'];
	echo "</td><td>\n";
	echo $reservierung['Raum_Neu']."</td><td>\n";
	if ( $reservierung['Art'] == VERTRETUNG_RAUMZUSATZ )
	  echo '(keiner)';
	else
	  echo $reservierung['Raum']."</td>\n";
	echo '<td>'.nl2br($reservierung['Bemerkung']).'</td>';
	echo "</tr>\n";
}
echo "</table>\n";
if ( mysql_num_rows($query) == 0)
  echo '<div class="Hinweis">Keine Reservierungen vorhanden!</div>';
mysql_free_result($query);
echo "</td></tr>\n";
include('include/footer.inc.php'); 
?>
