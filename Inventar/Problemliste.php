<?php
/**
 * Liste der aktuellen Probleme 
 * (c) 2006 Christoph Griep
 * 
 */
DEFINE('USE_KALENDER',1);
$Ueberschrift = 'Liste des aktuellen Hardwareprobleme';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');
include('include/Lehrer.class.php');

if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) &&
    isset($_REQUEST['Status']) && is_numeric($_REQUEST['Status']))
{
    $sql = "UPDATE T_Reparaturen SET F_Status_id=".$_REQUEST['Status'];
    $sql .= " WHERE Reparatur_id=".$_REQUEST['id'];
    if ( ! mysql_query($sql) ) 
    {
    	echo '<div class="Fehler">'.mysql_error().'</div>';
    }
}

echo '<tr><td align="center">';
echo 'Stand: '.date('d.m.Y H:i');
echo '</td></tr><tr><td>';
$query = mysql_query('SELECT Reparatur_id, Status, Datum, Bezeichnung, Inventar_Nr, Grund, T_Reparaturen.Bemerkung '.
  ', F_Inventar_id FROM (T_Reparaturen INNER JOIN T_Reparaturstatus ON F_Status_id=Status_id) INNER JOIN T_Inventar ON '.
  'F_Inventar_id=Inventar_id WHERE F_Status_id=1 ORDER BY Datum');
if ( mysql_num_rows($query) > 0 )
{
  echo '<table class="Liste">';
  echo '<tr><th>Datum</th><th>Gerät</th><th>Grund</th><th>Status</th></tr>';
  // Reparaturen vorhanden!
  while ( $meldung = mysql_fetch_array($query) )
  {
    echo '<tr><td>'.
      date('d.m.Y',$meldung['Datum']).'</td>';
    echo '<td><a href="Inventar.php?id='.$meldung["F_Inventar_id"].'#Reparaturen">'.
      stripslashes($meldung['Bezeichnung']).'</a>';
    if ( $meldung['Inventar_Nr'] != '' )
      echo ' ('.stripslashes($meldung['Inventar_Nr']).')';
    echo '</td>';
    echo '<td>'.stripslashes($meldung['Grund']).'</td>';
    echo '<td>'.$meldung['Status'].'</td></tr>';
    echo '<tr><td colspan="4" class="zwischenueberschrift">';
    echo '<div class="BemerkungsIcon">';
    echo '<a href="?id='.$meldung['Reparatur_id'].'&Status=4';
    echo '" title="erledigt" alt="grünes Häkchen">
<img src="http://img.oszimt.de/ok.gif" /></a>';
    echo '<a href="?id='.$meldung['Reparatur_id'].'&Status=3';
    echo '" title="in Reparatur" alt="Schraubenschlüssel">
<img src="http://img.oszimt.de/repair.gif" /></a>';
    echo '</div>';
    echo nl2br(stripslashes($meldung['Bemerkung']));
    echo '</td></tr>';
  }
  mysql_free_result($query);
  echo '</table>';
}
else
  echo 'Zur Zeit sind keine Probleme bekannt!';

echo '</td></tr>';
include('include/footer.inc.php');
?>