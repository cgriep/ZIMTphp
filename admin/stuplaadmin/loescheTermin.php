<?php
include_once("include/stupla_vars.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframetest.inc.php");

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

if(!$db)
  dieMsgLink("Keine Verbindung mit DB $DBname!", "index.php", "Zurück zum Admin-Skript");

if(isset($_REQUEST['SJahr']) && $_REQUEST['SJahr'] != "")
  $SJahr = $_REQUEST['SJahr'];
else
  dieMsgLink("Unvollständige Eingabedaten!", "FerienAdmin.php", "Zurück zum Ferien-Admin");

echo ladeOszKopf_o("StuPla - Admin", "Ferien - Admin (Termine ansehen / löschen)");

//Ermittle ersten und letzten Montag im Schuljahr
$sql  = "SELECT MIN(Montag), MAX(Montag) FROM T_Woche WHERE SJahr = \"$SJahr\";";
$rs = mysql_query($sql, $db);
$row = mysql_fetch_row($rs);
$ersterMontag   = $row[0];
$letzterSonntag = strtotime("+6 day", $row[1]);

//TODO: Fehler abfangen wenn Schuljahr noch nicht angelegt!

//Ermittle alle freien Zeiten des Schuljahres
$sql  = "SELECT ersterTag, letzterTag, Kommentar FROM T_FreiTage ";
$sql .= "WHERE letzterTag >= $ersterMontag AND ersterTag <= $letzterSonntag ORDER BY ersterTag;";
$rs = mysql_query($sql, $db);

echo "<p align = \"center\"><b>Freie Tage im Schuljahr: $SJahr</b></p>";
echo "\n<table border = \"0\" cellpadding = \"10\" cellspacing = \"0\" width = \"100%\">";

while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
{
  echo "\n\t<tr>";
  echo "\n\t\t<td width = \"40%\">&nbsp;</td>";
  echo "\n\t\t<td bgcolor = \"#eeeeee\" nowrap>" . date("D: d.m.y", $row['ersterTag']) . " - " . date("D: d.m.y", $row['letzterTag']) . "</td>";
  echo "\n\t\t<td bgcolor = \"#eeeeee\" nowrap>$row[Kommentar]</td>";
  echo "\n\t\t<td bgcolor = \"#ffffff\" nowrap><a href=\"FerienAdmin.php?startDat=" . $row['ersterTag'] . "&Skript=loescheTermin\">Löschen</a></td>";
  echo "\n\t\t<td width = \"40%\">&nbsp;</td>";
  echo "\n\t<tr>";
}

echo "\n</table>";

echo ladeOszKopf_u();

echo ladeLink("index.php", "<b>StuPla-Admin</b>");
echo ladeLink("FerienAdmin.php", "Ferien-Admin");
echo ladeLink("TurnusAdmin.php", "Turnus-Admin");

echo ladeOszFuss();
?>