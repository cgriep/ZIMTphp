<?php
/** 
 * Ansehen der vorhandenen Bestellungen
 * (c) 2006 Christoph Griep
 * 
 */
$Ueberschrift = 'MSDNAA - Lizenzanträge von '.$_SERVER['REMOTE_USER'].' ansehen';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
include('include/header.inc.php');
include('../Lizenzverwaltung/msdnaaconfig.inc.php');
?>
<tr><td align="center">
<a href="/">Zurück zur Startseite des Lehrerbereichs.</a><br />
<a href=".">Zurück zur Lizenzbeantragung</a>
</td></tr>
<tr>
  <td align="center">
  Klicken Sie auf den Namen eines Schülers, um seinen Antrag erneut auszudrucken.
 <?php
  if ( isset($_REQUEST['Del'] ) && is_numeric($_REQUEST['Del']) )
  {
    mysql_query('UPDATE T_Antraege SET Bemerkungen=CONCAT(Bemerkungen,'.
      "'\nKann gelöscht werden, ".$_SERVER['REMOTE_USER'].'/'.date('d.m.Y H:i').
      "') WHERE Vertragsnummer=".
      $_REQUEST['Del']);
  }
  $Sort = 'Name, Vorname';
  if ( isset($_REQUEST['Sort']) ) $Sort = $_REQUEST['Sort'];
  $query = mysql_query('SELECT DISTINCT Name, Vorname, Art, Vertragsnummer, Bemerkungen, '.
    "Eingang FROM T_Antraege WHERE Ansprechpartner = '".$_SERVER['REMOTE_USER']."'ORDER BY $Sort");

  echo '<table class="Liste">';
  echo '<tr><th><a href="Ansehen.php?Sort=Vertragsnummer">Vertragsnummer</a></th><th><a href="';
  echo 'Ansehen.php">Name, Vorname</a></th><th><a href="Ansehen.php?Sort=Art">Klasse</a>';
  echo '</th><th><a href="Ansehen.php?Sort=Eingang">Eingang</a></th><th><a href="Ansehen.php?Sort=Bemerkungen">';
  echo 'Bemerkungen</a></th><th></th></tr>';
  while ( $row = mysql_fetch_array($query))
  {
    echo '<tr><td align="center">'.$row['Vertragsnummer'].
       '</td><td><a href="Antragsformular.php?Antragnr=';
    echo $row['Vertragsnummer'];
    echo '&Nochmal=1" target="_blank">'.$row['Name'].', '.$row['Vorname'].'</a></td>';
    echo '<td>'.$row['Art'].'</td><td>'.$row['Eingang'].'</td><td>'.
      stripslashes($row['Bemerkungen']);
    echo '</td><td>';
    if ( strpos($row['Bemerkungen'], 'Kann gelöscht werden') === false)
      echo '<a href="'.$_SERVER['PHP_SELF'].'?Del='.$row['Vertragsnummer'].'">Löschen</a></td></tr>';
  }
  echo '</table>';
  echo '</td></tr>';
  echo '<tr><td colspan="3" align="center" class="osz">';
  echo mysql_num_rows($query).' Anträge unbearbeitet.<br />';
  mysql_free_result($query);
?>
Klicken Sie auf den Namen eines Schülers, um seinen Antrag erneut auszudrucken.
</td>
</tr>
<tr>
 <td align="center"><a href="/">Zurück zur Startseite des Lehrerbereichs.</a><br>
 <a href=".">Zurück zur Lizenzbeantragung</a></td>
</tr>
<?php
include('include/footer.inc.php');
?>