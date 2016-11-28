<?php
include_once("include/stupla_vars.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframetest.inc.php");

global $PHP_SELF;

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

if(!$db)
  dieMsgLink("Keine Verbindung mit DB $DBname!", "index.php", "Zurück zum Admin-Skript");

echo ladeOszKopf_o("StuPla - Admin", "Ferien - Admin");

if(isset($_REQUEST['Skript']))
  $Skript = $_REQUEST['Skript'];//Aufrufstatus Skript
else
  $Skript = "";

//------------------------------------------------------------------------------
//Ferientermin loeschen
//------------------------------------------------------------------------------
if($Skript == "loescheTermin" && isset($_REQUEST['startDat']))//Aufruf aus "loescheTermin.php"
{
  $startDat = $_REQUEST['startDat'];
  $sql = "DELETE FROM T_FreiTage WHERE ersterTag = $startDat;";
  mysql_query($sql, $db);
}
//------------------------------------------------------------------------------
//Neuer Ferientermin
//------------------------------------------------------------------------------
if($Skript == "neuFerientermin" && isset($_REQUEST['startDat']) && isset($_REQUEST['stopDat']) && isset($_REQUEST['Kommentar']))
{
  $startDat  = $_REQUEST['startDat'];
  $stopDat   = $_REQUEST['stopDat'];
  $Kommentar = $_REQUEST['Kommentar'];

  if($startDat == "" || $Kommentar == "")
    dieMsgLink("Startdatum oder Kommentar fehlt!", "FerienAdmin.php", "Zurück zum Ferien-Admin");

  if($stopDat == "")
    $stopDat = $startDat;

  $startDat = explode(".",$startDat);
  $stopDat  = explode(".",$stopDat);

  if(!checkdate($startDat[1],$startDat[0],$startDat[2]) || !checkdate($stopDat[1],$stopDat[0],$stopDat[2]))
    dieMsgLink("Falsche Datumsangabe!", "FerienAdmin.php", "Zurück zum Ferien-Admin");

  $startDat = mktime(0,0,0,$startDat[1],$startDat[0],$startDat[2]);
  $stopDat  = mktime(0,0,0,$stopDat[1],$stopDat[0],$stopDat[2]);

  if($startDat > $stopDat)
    dieMsgLink("Erster Tag liegt vor dem letzten Tag!", "FerienAdmin.php", "Zurück zum Ferien-Admin");

  //Ist schon ein Termin vorhanden?
  $sql  = "SELECT ersterTag, letzterTag, Kommentar FROM T_FreiTage ";
  //Liegt einer der beiden oder beide Tage (startDat, stopDat) innerhalb eines vorhandenen Zeitraums?
  $sql .= "WHERE (ersterTag <= $startDat AND letzterTag >= $startDat) OR (ersterTag <= $stopDat AND letzterTag >= $stopDat) ";
  //Liegen bereits vorhandene Tage innerhalb des neuen Zeitraums?
  $sql .= "OR (ersterTag >= $startDat AND ersterTag <= $stopDat) OR (letzterTag >= $startDat AND letzterTag <= $stopDat);";

  $rs = mysql_query($sql, $db);

  if(mysql_num_rows($rs) != 0)
  {
    $row = mysql_fetch_array($rs, MYSQL_ASSOC);
    dieMsgLink("Doppelbelegung, von <b>" . date("d.m.y", $row['ersterTag']) . "</b> bis <b>" . date("d.m.y", $row['letzterTag']) . "</b>: " . $row['Kommentar']. "!", "FerienAdmin.php", "Zurück zum Ferien-Admin");
  }
  else
  {
    $sql = "INSERT INTO T_FreiTage (ersterTag, letzterTag, Kommentar) VALUES ($startDat, $stopDat, \"$Kommentar\")";
    mysql_query($sql, $db);
  }
}

//------------------------------------------------------------------------------
//Aufbau des Admin-Frontend
//------------------------------------------------------------------------------
echo "\n<br>";
echo "\n<table border = \"0\" cellpadding = \"10\" cellspacing = \"0\" width = \"100%\">";

//Neue Ferien/Feiertage anlegen
echo "\n<form action = \"$PHP_SELF\" method = \"POST\" name = \"Datum\">";
echo "\n\t<tr>";
echo "\n\t\t<td width = \"15%\">&nbsp;</td>";
echo "\n\t\t<td align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t\t<b>von</b><br><input type = \"text\" name = \"startDat\" size = \"10\" maxlength=\"10\"><br><font size=\"-2\">(TT.MM.JJJJJ)</font>&nbsp;<br>";
echo "\n\t\t\t<b>bis</b><br><input type = \"text\" name = \"stopDat\" size = \"10\" maxlength=\"10\"><br><font size=\"-2\">(TT.MM.JJJJ)</font>&nbsp;";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t\t<input type = \"hidden\" name = \"Skript\" value = \"neuFerientermin\">";
echo "\n\t\t\t<b>Kommentar</b><br><input type = \"text\" name = \"Kommentar\" size = \"20\" maxlength=\"30\"><br><br>";
echo "\n\t\t\t<input type=\"Submit\" value=\"Daten eintragen\">";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"40%\">";
echo "<span class = \"smallmessage_mi\"><b>Schulfreie Tage/Zeiten:&nbsp;</b>Hier können schulfreie Zeiten angelegt werden.<br>Achtung: Es kann keine Doppelbelegungen geben, z.B. ein Feiertag in den Ferien.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td width = \"15%\">&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</form>\n";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Termine ansehen/loeschen
$sql = "SELECT DISTINCT SJahr FROM T_Woche ORDER BY SJahr;";
$rs = mysql_query($sql, $db);
$AnzSJahre = mysql_num_rows($rs);
echo "\n<form action = \"loescheTermin.php\" method = \"POST\">";
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\">";
echo "\n\t\t\t<select name=\"SJahr\" size=\"$AnzSJahre\">";

while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
  echo"\n\t\t\t<option>$row[SJahr]</option>";

echo "\n\t\t\t</select>";
echo "\n\t\t\t<input type=\"Submit\" value=\"Weiter\">";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\">";
echo "<span class = \"smallmessage_mi\"><b>Termine ansehen/löschen:&nbsp;</b>Hier können sie die schulfreien Zeiten eines Schuljahrs ansehen und löschen.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</form>\n";
echo "\n</table>";

echo ladeOszKopf_u();

echo ladeLink("index.php", "<b>StuPla-Admin</b>");
echo ladeLink("TurnusAdmin.php", "Turnus-Admin");

echo ladeOszFuss();
?>