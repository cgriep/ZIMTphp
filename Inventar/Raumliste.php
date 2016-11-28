<?php
/**
 * Inventarliste pro Raum
 * (c) 2006 Christoph Griep
 */
$Ueberschrift = 'Inventarliste anzeigen';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');

$Nutzungsarten[1] = 'Neu';
$Nutzungsarten[2] = 'nutzbar';
$Nutzungsarten[3] = 'in Betrieb';

if ( isset($_REQUEST['Raum_id']) && is_numeric($_REQUEST['Raum_id']) )
{
  $q = mysql_query('SELECT * FROM T_Raeume WHERE Raum_id='.$_REQUEST['Raum_id']);
  if ( ! $Art = mysql_fetch_array($q) ) $Art = array();
  //Liste des Inventars
  echo '<tr><td>';
  echo '<h2>Raum '.$Art['Raumnummer'].' - '.$Art['Raumbezeichnung'].'</h2>';
  echo '<table class="Liste">';
  echo '</tr>';
  if ( ! $query = mysql_query('SELECT Inventar_id, Bezeichnung, Seriennummer, '.
    ' Inventar_Nr, Herstellungsjahr, '.
    'Art FROM T_Inventar INNER JOIN '.
    'T_Inventararten ON F_Art_id=Art_id WHERE F_Raum_id='.$_REQUEST['Raum_id'].
    ' ORDER BY Art, Inventar_Nr')) echo '<div class="Fehler">Fehler: '.mysql_error().'</div>';
  $anz = 0;
  $Art = '';
  while ( $inv = mysql_fetch_array($query) )
  {
  	if ( $Art != $inv['Art'])
  	{
  		echo '<tr><th colspan="4">'.$inv['Art'].'</th></tr>';
  		echo '<tr><td class="Zwischenueberschrift">Bezeichnung</td><td 
class="Zwischenueberschrift">Seriennummer</td><td class="Zwischenueberschrift">
Inventarnummer</td>';
  		echo '<td class="Zwischenueberschrift">Menge</td>';
  		$Art = $inv['Art'];
  	}
    echo '<tr><td>';
    echo stripslashes($inv['Bezeichnung']).'</td>';
    echo '<td>'.$inv['Seriennummer'].'</td><td>'.$inv['Inventar_Nr'].'</td>';
    echo '<td align="right">';
    $mengequery = mysql_query('SELECT Inhalt FROM T_Inventardaten '.
      'WHERE Bemerkung="Menge" AND F_Inventar_id='.$inv['Inventar_id']);
    if ( $menge = mysql_fetch_array($mengequery) )
      echo $menge[0];
    else
      echo '1';
    mysql_free_result($mengequery);
    echo '</td>';
    echo '</tr>';
    $anz++;
  }
  echo '<tr><td colspan="4">'.$anz.' Einträge</td></tr>';
  mysql_free_result($query);
  echo '</table>';
  
  
  echo '</td></tr>';
}
else
{
  echo '<tr><td>';
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" class="Formular">';
  echo '<select name="Raum_id">';
  $query = mysql_query('SELECT * FROM T_Raeume ORDER BY Raumnummer');
  while ( $art = mysql_fetch_array($query) )
  {
    echo '<option value="'.$art['Raum_id'].'">'.$art['Raumnummer'].' '.
      $art['Raumbezeichnung']."</option>\n";
  }
  mysql_free_result($query);
  echo '</select>';
  echo '<input type="Submit" value="Anzeigen" />';
  echo '</form>';
  echo '</td></tr>';
}
include('include/footer.inc.php');
?>