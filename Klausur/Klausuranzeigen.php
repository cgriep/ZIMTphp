<?php
/*
 * Klausuranzeigen.php
 * Zeigt die Klausurergebnisse einer Abteilung pro Schuljahr an.
 * Für die Abteilungsleitung ergibt sich die Möglichkeit, die Kenntnisnahme der
 * Klausuren zu kennzeichnen.
 * (c) 2006 Christoph Griep
 */
$Ueberschrift = "Klausurergebnisse auflisten";
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include("include/header.inc.php");
include("include/Klausur.inc.php");
include("include/turnus.inc.php");
include("include/Abteilungen.class.php");
$dieAbteilungen = new Abteilungen($db);

if ( isset($_REQUEST["Kenntnis"]) && is_array($_REQUEST["Kenntnis"]) && 
     $dieAbteilungen->isAbteilungsleitung() )
{
	foreach ( $_REQUEST["Kenntnis"] as $key => $value )
	{
  	  if ( ! mysql_query("UPDATE T_Klausurergebnisse SET Kenntnisnahme='".$_SERVER["REMOTE_USER"].
        " ".date("d.m.Y")."' WHERE Klausur_id = ".$key)) 
        echo mysql_error();
	}
	echo '<tr><td class="Hinweis">Die Kenntnisnahme der Klausuren wurde gespeichert.</td></tr>';
}

echo '<tr><td>';
if ( isset($_REQUEST["Schuljahr"]) && isset($_REQUEST["Abteilung"]) )
{ 
  echo '<table class="Liste" width="100%">';
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  echo '<tr><td colspan="8" align="center" class="home-content-title">';
  echo $dieAbteilungen->getAbteilung($_REQUEST["Abteilung"]);
  echo ", Schuljahr ".$_REQUEST["Schuljahr"];
  echo "</td></tr>\n";
  echo "<tr><th>Datum</th><th>Klasse</th><th>Fach</th><th>LehrerIn</th><th>Dauer</th><th>\n";
  echo "Teilnehmer</th><th>Schnitt</th><th>Kenntnisnahme</th></tr>\n";
  $query = mysql_query("SELECT * FROM T_Klausurergebnisse WHERE Schuljahr='".
    $_REQUEST["Schuljahr"]."' AND Abteilung='".$_REQUEST["Abteilung"]."' ORDER BY Datum, Klasse, Fach");
  $Genehmigung = 0;
  $Schnitt = 0;
  $Klausuren = 0; 
  while ( $Klausur = mysql_fetch_array($query) )
  {
    echo '<tr><td><a href="Klausurergebnisse.php?Klausur_id='.$Klausur["Klausur_id"];
    echo '">'.$Klausur["Datum"]."</a></td><td>";
    echo $Klausur["Klasse"]."</td><td>".$Klausur["Fach"]."</td><td>".$Klausur["Lehrer"].
      '</td><td align="center">';
    $Anzahl = Teilnehmeranzahl($Klausur);
    echo $Klausur["Dauer"].'</td><td align="center">'.$Anzahl;
    echo '</td><td align="right">';
    if ( substr($Klausur["Klasse"],0,2) != "OG" && $Anzahl > 0 &&
              $Anzahl < ($Klausur["Fuenfer"]+$Klausur["Sechser"])*3 )
    {
      echo '<span class="Fehler" title="Genehmigungspflichtig! Mehr als 1/3 unterm Strich!">&empty;!</span>';
      $Genehmigung++;
    }
    $s = Durchschnitt($Klausur);
    if ( is_numeric($s) )
    {
    	 $Schnitt += $s;
    	 $Klausuren++;
    }
    echo $s."</td><td>";    
    // Abteilungsleiter-Möglichkeit zum Abhaken
    if ( $dieAbteilungen->isAbteilungsleitung() )
      if ($Klausur["Kenntnisnahme"] == "" )
        echo '<input type="checkbox" name="Kenntnis['.$Klausur["Klausur_id"].']" ' .
        	 'value="X" /> <b>Kenntnis nehmen</b>';
      else
        echo stripslashes($Klausur["Kenntnisnahme"]);
    else      
      echo stripslashes($Klausur["Kenntnisnahme"]);
    echo "</td></tr>\n";
  }
  mysql_free_result($query);
  if ($Anzahl > 0)
  {
    echo '<tr><td colspan="6">Gesamt</td><td align="right">';
    echo number_format($Schnitt/$Klausuren,2);
    echo "</td><td>$Klausuren Klausuren";
    if ( $Genehmigung > 0 ) echo "<br />$Genehmigung unterm Strich";
    echo "</td>";
  }
  if ( $dieAbteilungen->isAbteilungsleitung() )
  {
    echo '<tr><td colspan="8"><input type="submit" value="Kenntnisnahme speichern" /></td></tr>';
    echo '<input type="hidden" value="'.$_REQUEST["Schuljahr"].'" name="Schuljahr" />';
    echo '<input type="hidden" value="'.$_REQUEST["Abteilung"].'" name="Abteilung" />';
  }
  echo "</form>\n";
  echo '</table>';
  echo "Stand: ".date("d.m.Y H:i");
}
if ( ! isset($_REQUEST["Print"]))
{
  echo "<hr />\n";
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  echo 'Abteilung ';
  echo '<select name="Abteilung">';
  foreach ($dieAbteilungen->Abteilungen as $key => $value )
    echo '<option value="'.$key.'">'.$value["Abteilung"]."</option>\n";
  echo '</select> ';
  echo 'Schuljahr ';
  echo '<select name="Schuljahr">';
  $query = mysql_query("SELECT Schuljahr FROM T_Klausurergebnisse GROUP BY Schuljahr ORDER BY Datum");
  $jahr = Schuljahr(false);
  while ( $Schuljahr = mysql_fetch_row($query) )
  {
    echo '<option ';
    if ( $jahr == $Schuljahr[0] ) echo 'selected="selected"';
    echo '>'.$Schuljahr[0]."</option>\n";
  }
  mysql_free_result($query);
  echo '</select>';
  echo ' <input type="Submit" value="anzeigen">';
  echo "</form>\n";
}
echo '</td></tr>';

include("include/footer.inc.php");
?>