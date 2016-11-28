<?php
/*
 * VertretungLehrerStatistik.php
 * Zeigt die Statistik der Vertretungen pro Lehrer an.
 * Es werden nur zusätzliche Stunden und ausgefallene Stunden angezeigt. 
 * 
 * Parameter:
 * Jahr - das Jahr
 * Monat - der Monat
 * 
 */
$Ueberschrift = 'Statistik Lehrer';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';

include('include/header.inc.php');
echo '<tr><td>';
/* ----
  Vertretungsstatistik
*/

include('include/Vertretungen.inc.php');
include('include/Lehrer.class.php');

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" class="Formular">';
echo '<label>Monat</label>';
echo '<select name="Monat">';
for ($i=1; $i<13; $i++)
{
  echo '<option value="'.$i.'" ';
  if ( $i == date('m')) echo 'selected="selected"';
  echo '>'.$i.'</option>'."\n";
}
echo '</select><br />';
echo '<label>Jahr</label> ';
echo '<select name="Jahr">';
$query = mysql_query('SELECT DISTINCT YEAR(FROM_UNIXTIME(Datum)) FROM T_Vertretungen ' .
		'ORDER BY YEAR(FROM_UNIXTIME(Datum)) DESC');
while ( $jahr = mysql_fetch_row($query))
{
	echo '<option ';
    if ( $jahr[0] == date('Y')) echo 'selected="selected"';
    echo '>'.$jahr[0].'</option>'."\n";
}
mysql_free_result($query);
echo '</select><br />';
echo '<input type="Submit" value="Anzeigen">';
echo '</form>';
echo '<br />';

// Statistik für die angegebene Woche ausgeben
if ( isset($_REQUEST['Monat']) && is_numeric($_REQUEST['Monat'])
  && isset($_REQUEST['Jahr']) && is_numeric($_REQUEST['Jahr']) )  
{
	$Monat = $_REQUEST['Monat'];
	$Jahr = $_REQUEST['Jahr'];
    echo '<h1>Vertretungen für '.$Monat.' / '.$Jahr.'</h1>';
    $anfangsdatum = mktime(0,0,0,$Monat,1,$Jahr);
    $enddatum = strtotime('+1 month -1 day', $anfangsdatum);
    $version = getAktuelleVersion();
    $query = mysql_query('SELECT DISTINCT Lehrer FROM T_StuPla ' .
    		'WHERE Version='.$version.' ORDER BY Lehrer');
    echo '<table class="Liste">';
    echo '<tr><th>Name</th><th>Mehrarbeit</th><th>Wenigerarbeit</th><th>Differenz</th></tr>';
    $wg = 0;
    $mg = 0;
    while ( $lehrer = mysql_fetch_row($query))
    {
      	echo '<tr><td>';
      	$derLehrer = new Lehrer($lehrer[0], LEHRERID_KUERZEL);
      	echo $derLehrer->Name.', '.$derLehrer->Vorname;
      	echo '</td><td>';
      	$m = berechneVertretungsstunden($lehrer[0], $anfangsdatum, $enddatum, 
            ' AND Art IN '.VERTRETUNG_MEHRARBEIT);
        echo $m;
      	echo '</td><td>';
      	$w = berechneAusfallstunden($lehrer[0], $anfangsdatum, $enddatum);
        echo $w;
        echo '</td><td>';
        echo $m-$w;
      	echo '</td></tr>';
      	$mg += $m;
      	$wg += $w; 
    }
    mysql_free_result($query);
    echo '<tr><td>Gesamt</td><td>'.$mg.'</td><td>'.$wg.'</td><td>'.($mg-$wg).'</td></tr>';
    echo '</table>';    
}
echo '</td></tr>';
include('include/footer.inc.php');

?>