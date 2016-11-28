<?php
/*
 * Created on 28.09.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$Ueberschrift = 'Stellenbörse administrieren';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt
.de/oszimt.css">';

include('include/header.inc.php');
mysql_select_db('Stellenboerse');
echo '<tr><td>';
if ( isset($_REQUEST['del']) && is_numeric($_REQUEST['del']))
{
  if ( ! mysql_query('DELETE FROM T_Stellenangebote WHERE Stellen_id='.$_REQUEST['del'])) echo mysql_error();
  echo '<div class="Hinweis">Stellenangebot '.$_REQUEST['id'].' gelöscht</div>';
}
if ( isset($_REQUEST['Titel']) && $_REQUEST['Titel'] != '')
{
  echo '<div class="Hinweis">Speichere Eintrag...</div>';
	// Upload
  $Datum = explode('.',$_REQUEST['Datum']);
  if ( count($Datum) == 3 )
    $Datum = mktime(0,0,0,$Datum[1],$Datum[0],$Datum[2]);
  else 
    $Datum = 0;
  if ( $Datum < time() )
    $Datum = strtotime('+2 days');
	$Datei = mysql_real_escape_string(
	   file_get_contents($_FILES['Datei']['tmp_name']));
	$Mime = $_FILES['Datei']['type'];	
	$sql = 'INSERT INTO T_Stellenangebote (Titel, Beschreibung, Datum, Datei, Mime) '.
	  ' VALUES ("'.mysql_real_escape_string($_REQUEST['Titel'])
	  .'","'.mysql_real_escape_string($_REQUEST['Beschreibung']).'",'.
      $Datum.',"'.$Datei.'","'.$Mime.'")';
    if ( ! mysql_query($sql))
      echo mysql_error();
}
?>

<form action="<?=$_SERVER['PHP_SELF']?>" class="Formular" enctype="multipart/form-data" method="post">
<label>Titel</label> 
<input type="Text" name="Titel" value="" /><br />
<label>Beschreibung</label>
<textarea name="Beschreibung" rows="5" cols="60">
</textarea><br />
<label>Herkunft</label>

<label>Gültig bis</label>
<input type="text" name="Datum" value="<?php 
echo date('d.m.Y',strtotime('+60 days'))?>" /><br />
<label>Wählen Sie die Datei aus:</label>
    <input name="Datei" type="file" size="50" maxlength="100000" >
    <br />
    <input type="submit" value="Speichern" />
</form>

<?php

$query = mysql_query('SELECT * FROM T_Stellenangebote ORDER BY Datum DESC');
if ( mysql_num_rows($query) > 0)
{
  echo '<h2>Vorhandene Stellenangebote</h2>';
  echo '<table class="Liste">';
  echo '<tr><th>Titel</th><th>Beschreibung</th><th>Gültig bis</th></tr>';
  while ( $row = mysql_fetch_array($query))
  {
    echo '<tr><td>';
    echo '<a href="'.$_SERVER['PHP_SELF'].'?del='.$row['Stellen_id'].
      '"><img src="/StuPla/delete.gif" alt="Löschen"/></a>';
    echo '<a href="Stellenangebotzeigen.php?id='.$row['Stellen_id'].
      '" target="_blank">'.$row['Titel'].'</a></td><td>';
    echo nl2br($row['Beschreibung']).'</td><td>';
    echo date('d.m.Y',$row['Datum']).'</td></tr>';
  } // while 
  echo '</table>';
}
else
  echo '<div class="Hinweis">Keine Stellenangebote vorhanden!</div>';

mysql_free_result($query);
echo '</td></tr>';
include('include/footer.inc.php');
?>

