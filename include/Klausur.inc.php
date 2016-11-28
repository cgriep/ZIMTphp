<?php
/*
$Abteilungen[1]["Name"] = "Abt I";
$Abteilungen[1]["Empfänger"] = "hopp@oszimt.de";
// Sonderfall in Klausurergebnisse.php für Günther programmiert!!
$Abteilungen[2]["Name"] = "Abt II";
$Abteilungen[2]["Empfänger"] = "scholl@oszimt.de";
$Abteilungen[3]["Name"] = "Abt III";
$Abteilungen[3]["Empfänger"] = "koepf@oszimt.de";
$Abteilungen[4]["Name"] = "Abt IV";
$Abteilungen[4]["Empfänger"] = "ansorge@oszimt.de";

function isAbteilungsleitung()
{
  return false;
}
*/
function Teilnehmeranzahl($Klausur)
{
  if ( isset($Klausur["Einser"]) )
    return $Klausur["Einser"] + $Klausur["Zweier"] + $Klausur["Dreier"] +
     $Klausur["Vierer"]+$Klausur["Fuenfer"]+$Klausur["Sechser"];
  else
    return 0;
}

function Durchschnitt($Klausur)
{
 $Anzahl = Teilnehmeranzahl($Klausur);
 if ( $Anzahl != 0 )
   return number_format(($Klausur["Einser"] + 2*$Klausur["Zweier"] + 3*$Klausur["Dreier"] +
     4 *$Klausur["Vierer"]+5*$Klausur["Fuenfer"]+6*$Klausur["Sechser"])
     / $Anzahl,2);
 else
   return "n/a";
}

function AbteilungFeststellen($Klasse, $db)
{
  $query = mysql_query("SELECT DISTINCT Abteilung FROM T_Schueler WHERE Klasse='$Klasse'", $db);
  if ( $row = mysql_fetch_row($query) )
    $Abteilung = $row[0];
  else
    $Abteilung = "";
  mysql_free_result($query);
  switch ( trim($Abteilung) )
  {
   case "I": $Abt = 1; break;
   case "II": $Abt = 2; break;
   case "III": $Abt = 3; break;
   case "IV": $Abt = 4; break;
   default: $Abt = 99;
  }
  return $Abt;
}

function FachLehrer($fach, $klasse, $db, $version = -1)
{
  $sql = "SELECT DISTINCT Lehrer FROM T_StuPla WHERE Klasse='".
   mysql_real_escape_string($klasse)."' AND Fach LIKE '".mysql_real_escape_string($fach).
   "%'";
  if ( $version > 0 ) $sql .= " AND Version=$version";
  $sql .= " ORDER BY Fach";
  $q = mysql_query($sql,$db);
  $erg = "";
  if ( $lehrer = mysql_fetch_row($q) )
  {
    $erg = $lehrer[0];
  }
  mysql_free_result($q);
  return $erg;

}

?>
