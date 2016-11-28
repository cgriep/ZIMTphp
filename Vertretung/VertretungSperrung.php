<?php
/*
 * Created on 22.12.2005
 *
 * VertretungSperrung.php
 * Ermöglicht die Eingabe von Sperrungen für Lehrer, Räume und Klassen.
 * Zeigt zunächst die Liste aller Sperrungen an, kann aber in den Editiermodus
 * umgestellt werden.
 * 
 * Parameter:
 * Lehrerkuerzel[] - Index istdie ID der Sperrung. Wenn gesetzt, wird die 
 *                   entsprechende Sperrung aktualisiert.
 * Wochentag[]       Wochentag ist der Wochentag (1 = Montag)
 * StundeVon[]       Erste Stunde der Sperrung (0 = ganzer Tag)
 * StundeBis[]       Letzte Stunde der Sperrung (bei StundeVon=0 egal)
 * Bezeichnung[]     Eine Beschreibung der Sperrung
 * Zum Hinzufügen von Sperrungen gibt es den Index "NEU". Dieser wird dann 
 * den Sperrungen hinzugefügt.  
 * 
 * Del[] - Index ist die ID der Sperrung. Wenn gesetzt, wird die entsprechende
 *         Sperrung gelöscht.
 * Edit - wenn gesetzt, wird die Liste der Sperrung zum Bearbeiten angezeigt 
 * 
 * Letzte Änderungen:
 * 06.01.06 C. Griep
 * 
 */
$Ueberschrift = 'Sperrungen einrichten';
define('USE_KALENDER', 1);
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';

include('include/header.inc.php');
include('include/Lehrer.class.php');

if ( isset($_REQUEST['Lehrerkuerzel']))
{
	// Speichern der Daten
	foreach ( $_REQUEST['Lehrerkuerzel'] as $key => $kuerzel)
	{
		if ( isset($_REQUEST['Del'][$key])  )
		{
		  if (! mysql_query('DELETE FROM T_Verhinderung_Sperrungen WHERE Sperrung_id='.
		    $key)) echo mysql_error();
		}
		elseif ( !is_numeric($key) && $kuerzel != '')
		{
		  $sql = 'INSERT INTO T_Verhinderung_Sperrungen (Lehrerkuerzel, Wochentag,';
          $sql .= "StundeVon,StundeBis,Bezeichnung) VALUES ('";
          $sql .= $kuerzel."',".$_REQUEST['Wochentag'][$key].',';
		  $sql .= $_REQUEST['StundeVon'][$key].',';
		  $sql .= $_REQUEST['StundeBis'][$key].",'";
		  $sql .= mysql_real_escape_string($_REQUEST['Bezeichnung'][$key])."')";
		  if (! mysql_query($sql)) echo mysql_error();
		}
		elseif (is_numeric($key) &&
		    is_numeric($_REQUEST['Wochentag'][$key]) &&
		    is_numeric($_REQUEST['StundeVon'][$key]) &&
		    is_numeric($_REQUEST['StundeBis'][$key]))
		{
	      $sql = 'UPDATE T_Verhinderung_Sperrungen SET ';	
		  $sql .= "Lehrerkuerzel='$kuerzel',Wochentag=";
		  $sql .= $_REQUEST['Wochentag'][$key].', StundeVon=';
		  $sql .= $_REQUEST['StundeVon'][$key].', StundeBis=';
		  $sql .= $_REQUEST['StundeBis'][$key].", Bezeichnung='";
		  $sql .= mysql_real_escape_string($_REQUEST['Bezeichnung'][$key])."'";
		  $sql .= ' WHERE Sperrung_id='.$key;
		  if ( ! mysql_query($sql) ) echo mysql_error();
		}
	} 
}

function schreibeZeile($zeile = array() ) 
{
	global $Wochentagnamen;
	global $Lehrer;
	echo "<tr><td>\n";
	if ( count($zeile) == 0)
	{
	  echo 'Neu: ';
	  $zeile['Sperrung_id'] = 'NEU';
	}
	echo '<select name="Lehrerkuerzel['.$zeile['Sperrung_id'].']">';
	if ( $zeile['Sperrung_id'] == 'NEU')
	  echo '<option value="" selected="selected">--Neu--</option>';
	foreach ($Lehrer as $derLehrer)
	{
	  echo '<option ';
	  if ( $zeile['Lehrerkuerzel'] == $derLehrer)
	    echo 'selected="selected"';
	  echo '>'.$derLehrer;
	  echo "</option>\n";
	}	
	echo '</select>';
	echo '</td><td>';
	echo '<select name="Wochentag['.$zeile["Sperrung_id"].']">';
	foreach ( $Wochentagnamen as $key => $tag)
	{
	  echo '<option value="'.$key.'"';
	  if ( $zeile["Wochentag"] == $key ) echo 'selected="selected"';
      echo '>'.$tag."</option>\n";
	}
	echo "</select>\n";
	echo "</td><td>\n";
	echo '<select name="StundeVon['.$zeile["Sperrung_id"].']">';
	echo '<option ';
	if ( $zeile["StundeVon"] == 0 ) echo 'selected="selected"';
	echo ">ganztägig</option>\n";
	for ( $i=1; $i<7; $i++)
	{
	  echo '<option ';
	  if ( $zeile["StundeVon"] == $i ) echo 'selected="selected"';
	  echo '>'.$i."</option>\n";
	}
	echo "</select>\n";
    echo "</td><td>\n";
    echo '<select name="StundeBis['.$zeile["Sperrung_id"].']">';
	for ( $i=1; $i<7; $i++)
	{
	  echo '<option ';
	  if ( $zeile["StundeBis"] == $i ) echo 'selected="selected"';
	  echo '>'.$i."</option>\n";
	}
	echo "</select>\n";
    echo "</td><td>\n";
    echo '<input type="text" name="Bezeichnung['.$zeile["Sperrung_id"].
        ']" maxlength="45" value="'.htmlentities($zeile["Bezeichnung"]);
    echo '"/>';
    if ( is_numeric($zeile["Sperrung_id"]))
      echo '<input type="checkbox" name="Del['.$zeile["Sperrung_id"].
        ']" value="x"/> Löschen';	
	echo "</td></tr>\n";
}

echo "<tr><td>\n";

echo '<table class="Liste">';
if ( isset($_REQUEST["Edit"]))
{
  $query = mysql_query("SELECT DISTINCT Lehrer FROM T_StuPla ORDER BY Lehrer");
  $Lehrer = array();
  while ( $derLehrer = mysql_fetch_array($query))
  {
  	$Lehrer[] = $derLehrer["Lehrer"];
  } 	
  mysql_free_result($query);
  echo '<form action="'.$_SERVER["PHP_SELF"].'">';
}
echo "<tr><th>Name</th><th>Wochentag</th><th>von Block</th>";
echo "<th>bis Block</th><th>Hinweis</th></tr>\n";
$query = mysql_query("SELECT * FROM T_Verhinderung_Sperrungen ORDER BY Lehrerkuerzel");
while ( $sperrung = mysql_fetch_array($query))
{
	if ( ! isset($_REQUEST["Edit"]))
	{
	  echo "<tr><td>";
	  $derLehrer = KuerzelToLehrer($sperrung["Lehrerkuerzel"]);
	  $Name = $derLehrer['Name'];
	  $Vorname = $derLehrer['Vorname'];
	  echo $Name;
	  if ( $Vorname != '' ) echo ', '.$Vorname;
      echo "</td><td>\n";
      echo $Wochentagnamen[$sperrung["Wochentag"]];
      echo "</td><td ";
      if ( $sperrung["StundeVon"] == 0 )
        echo " colspan=\"2\">ganztägig</td>";
      else 
        echo ">{$sperrung["StundeVon"]}</td><td>{$sperrung["StundeBis"]}</td>";
      echo "<td>{$sperrung["Bezeichnung"]}</td></tr>\n";
	}
	else
	  schreibeZeile($sperrung); 
}
mysql_free_result($query);
if ( isset($_REQUEST['Edit']) )
{
	schreibeZeile();
}
echo "</table>\n";
if ( ! isset($_REQUEST['Edit']) )
{
	echo '<a href="'.$_SERVER['PHP_SELF'].'?Edit=1">Bearbeiten und hinzufügen</a>';
}
else
{
  echo '<input type="submit" value="Speichern" />';
  echo "</form>\n";
}
echo "</td></tr>\n";
include('include/footer.inc.php');
?>
