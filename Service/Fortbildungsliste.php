<?php
/*
 * Fortbildungsliste.php
 * Zeigt alle Fortbildungen an, die als Verhinderung eingegeben wurden.
 * Dazu den Namen des Kollegen und den Zeitraum.
 * Als Hinweis wird die Bemerkung aus der Verhinderung angezeigt. Daher sollte
 * diese immer den Titel und eine Beschreibung der Fortbildung enthalten.
 * 
 * Parameter:
 * Sort - Feld, nach dem die Liste sortiert werden soll. Muss eine Zeichenkette
 *        sein. Mögliche Werte: Wer, Von, Hinweis
 * 
 * Letzte Änderungen: 
 *   08.01.2006 C. Griep
 */
$Ueberschrift = 'Fortbildungsliste';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
define('USE_KALENDER', 1);
include('include/header.inc.php');
include('include/Lehrer.class.php');
include('include/helper.inc.php');
include('include/Vertretungen.inc.php');
?>
<tr><td>
<?php

$Sort = 'Von DESC, Wer';
if ( isset($_REQUEST['Sort']) )
  $Sort = mysql_real_escape_string($_REQUEST['Sort']);

if ( $query = mysql_query('SELECT * FROM T_Verhinderungen WHERE Grund='.LEHRERFORTBILDUNG.
  ' ORDER BY '.$Sort) )
{
  echo "<table class=\"Liste\">\n";
  echo "<tr><th><a href=\"{$_SERVER['PHP_SELF']}?Sort=Wer, Von DESC\">Name, Vorname</a></th>";
  echo "<th><a href=\"{$_SERVER['PHP_SELF']}?Sort=Von DESC, Wer\">Von-bis</a></th>";
  echo "<th><a href=\"{$_SERVER['PHP_SELF']}?Sort=Hinweis, Von DESC\">Thema/Hinweis</a></th></tr>\n";
  while ( $eintrag = mysql_fetch_array($query) )
  {
    echo "<tr>\n";
    $derLehrer = KuerzelToLehrer($eintrag['Wer']);
    $Name = $derLehrer['Name'];
    $Vorname = $derLehrer['Vorname'];
    echo '<td>'.$Name.', '.$Vorname.'</td>';
    echo '<td>';
    if ( $eintrag['Von'] == $eintrag['Bis'] )
      echo date('d.m.Y', $eintrag['Von']);
    else
      echo date('d.m.',$eintrag['Von']).'-'.date('d.m.Y',$eintrag['Bis']);
    echo "</td>\n";
    echo '<td>'.nl2br(stripslashes(trim($eintrag['Hinweis'])))."</td>\n";
    echo "</tr>\n";
  }
  if ( mysql_num_rows($query) == 0 )
    echo '<tr><td colspan="3" align="center">keine Fortbildungen eingetragen!</td>';
  mysql_free_result($query);
  echo "</table>\n";
  echo 'Stand: '.date('d.m.Y H:i');
}
else
  dieMsg('Fehlerhafte Abfrage:'.mysql_error());

?>
<br/>
</td></tr>
<?php
include('include/footer.inc.php');
?>