<?php
include_once("include/stupla_vars.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframetest.inc.php");

global $PHP_SELF;

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

if(!$db)
  dieMsgLink("Keine Verbindung mit DB $DBname!", "index.php", "Zur�ck zum Admin-Skript");

echo ladeOszKopf_o("StuPla - Admin", "Turnus - Admin");

if(isset($_REQUEST['Skript']))
  $Skript = $_REQUEST['Skript'];//Aufrufstatus Skript
else
  $Skript = "";

//------------------------------------------------------------------------------
//Neue Turnusliste
//------------------------------------------------------------------------------
if($Skript == "NeueTurnusliste" && isset($_REQUEST['startJahr']) && isset($_REQUEST['stopJahr']))
{
  $startJahr  = $_REQUEST['startJahr'];
  $stopJahr   = $_REQUEST['stopJahr'];

  if($startJahr == "" || $stopJahr == "")
    dieMsgLink("Unvollst�ndige Eingabedaten!", "TurnusAdmin.php", "Zur�ck zum Turnus-Admin");

  $SJahr = $startJahr . "/" . $stopJahr;

  $sql = "INSERT INTO T_Turnus (Turnus, F_ID_Gruppe, SJahr) VALUES (\"A\", 1, \"$SJahr\")";
  mysql_query($sql, $db);
  $sql = "INSERT INTO T_Turnus (Turnus, F_ID_Gruppe, SJahr) VALUES (\"B\", 1, \"$SJahr\")";
  mysql_query($sql, $db);
  $sql = "INSERT INTO T_Turnus (Turnus, F_ID_Gruppe, SJahr) VALUES (\"C\", 1, \"$SJahr\")";
  mysql_query($sql, $db);
  $sql = "INSERT INTO T_Turnus (Turnus, F_ID_Gruppe, SJahr) VALUES (\"X\", 2, \"$SJahr\")";
  mysql_query($sql, $db);
  $sql = "INSERT INTO T_Turnus (Turnus, F_ID_Gruppe, SJahr) VALUES (\"Y\", 2, \"$SJahr\")";
  mysql_query($sql, $db);
  $sql = "INSERT INTO T_Turnus (Turnus, F_ID_Gruppe, SJahr) VALUES (\"Z\", 2, \"$SJahr\")";
  mysql_query($sql, $db);
  $sql = "INSERT INTO T_Turnus (Turnus, F_ID_Gruppe, SJahr) VALUES (\"a\", 3, \"$SJahr\")";
  mysql_query($sql, $db);
  $sql = "INSERT INTO T_Turnus (Turnus, F_ID_Gruppe, SJahr) VALUES (\"b\", 3, \"$SJahr\")";
  mysql_query($sql, $db);
}

//------------------------------------------------------------------------------
//Wochenplan loeschen
//------------------------------------------------------------------------------
if($Skript == "LoescheWochenplan" && isset($_REQUEST['SJahr']))
{
  $SJahr = $_REQUEST['SJahr'];
  if($SJahr == "")
    dieMsgLink("Unvollst�ndige Eingabedaten!", "TurnusAdmin.php", "Zur�ck zum Turnus-Admin");

  $sql = "SELECT ID_Woche FROM T_Woche WHERE SJahr = \"$SJahr\";";
  $rs = mysql_query($sql, $db);
  while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
  {
    $sql = "DELETE FROM T_WocheTurnus WHERE F_ID_Woche = $row[ID_Woche];";
    mysql_query($sql, $db);
  }
  $sql = "DELETE FROM T_Woche WHERE SJahr = \"$SJahr\";";
  mysql_query($sql, $db);
}

//------------------------------------------------------------------------------
//Aufbau des Admin-Frontend
//------------------------------------------------------------------------------
echo "\n<br>";
echo "\n<table border = \"0\" cellpadding = \"10\" cellspacing = \"0\" width = \"100%\">";

//Neue Turnusliste anlegen
echo "\n<form action = \"$PHP_SELF\" method = \"POST\">";
echo "\n\t<tr>";
echo "\n\t\t<td width = \"15%\">&nbsp;</td>";
echo "\n\t\t<td align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t\t<input type = \"text\" name = \"startJahr\" size = \"2\" maxlength=\"2\"><br><font size=\"-2\">(JJ) von</font><br><br>";
echo "\n\t\t\t<input type = \"text\" name = \"stopJahr\" size = \"2\" maxlength=\"2\"><br><font size=\"-2\">(JJ) bis</font>";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t<input type = \"hidden\" name = \"Skript\" value = \"NeueTurnusliste\">";
echo "\n\t\t\t<input type=\"Submit\" value=\"Weiter\">";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"40%\">";
echo "<span class = \"smallmessage_mi\"><b>Neue Turnusliste:&nbsp;</b>Hier kann eine neue Turnusliste f�r ein Schuljahr angelegt werden (Schuljahr zweistellig eingeben).<br><u>Das muss vor dem Anlegen des Wochenplans durchgef�hrt werden!</u><br>Standardm��ig werden die Turnusse A/B/C, X/Y/Z und a/b erzeugt.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td width = \"15%\">&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</form>\n";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Auswahl Turnus einfuegen/loeschen
$sql = "SELECT DISTINCT SJahr FROM T_Turnus ORDER BY SJahr;";
$rs = mysql_query($sql, $db);
$AnzSJahre = mysql_num_rows($rs);
echo "\n<form action = \"aendereTurnus.php\" method = \"POST\">";
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\">";echo "\n\t\t\t<select name=\"SJahr\" size=\"$AnzSJahre\">";

while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
  echo"\n\t\t\t<option>$row[SJahr]</option>";

echo "\n\t\t\t</select>";
echo "\n\t\t\t<input type=\"Submit\" value=\"Weiter\">";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\">";
echo "<span class = \"smallmessage_mi\"><b>Turnus �ndern/l�schen:&nbsp;</b>Hier k�nnen einem Schuljahr Turnusse hinzugef�gt bzw. gel�scht werden.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</form>\n";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Neuen Wochenplan anlegen
echo "\n<form action = \"neuWochenplan.php\">";
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\">";
echo "\n\t\t\t<input type=\"Submit\" value=\"Neuer Wochenplan\">";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\">";
echo "<span class = \"smallmessage_mi\"><b>Neuer Wochenplan:&nbsp;</b>Anlegen eines neuen Wochenplans f�r ein Schuljahr.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</form>\n";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Auswahl Wochenplan aendern
$sql = "SELECT DISTINCT SJahr FROM T_Woche ORDER BY SJahr;";
$rs = mysql_query($sql, $db);
$AnzSJahre = mysql_num_rows($rs);
echo "\n<form action = \"aendereWochenplan.php\" method = \"POST\">";
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\">";
echo "\n\t\t\t<select name=\"SJahr\" size=\"$AnzSJahre\">";

while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
  echo"\n\t\t\t<option>$row[SJahr]</option>";

echo "\n\t\t\t</select>";
echo "\n\t\t\t<input type=\"Submit\" value=\"�ndern\">";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\">";
echo "<span class = \"smallmessage_mi\"><b>Wochenplanplan �ndern:&nbsp;</b>�nderung eines vorhandenen Wochenplans.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</form>\n";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Auswahl Wochenplan loeschen
$sql = "SELECT DISTINCT SJahr FROM T_Woche ORDER BY SJahr;";
$rs = mysql_query($sql, $db);
$AnzSJahre = mysql_num_rows($rs);
echo "\n<form action = \"$PHP_SELF\" method = \"POST\">";
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"2\" align = \"center\" bgcolor = \"#eeeeee\">";
echo "\n\t\t\t<select name=\"SJahr\" size=\"$AnzSJahre\">";

while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
  echo"\n\t\t\t<option>$row[SJahr]</option>";

echo "\n\t\t\t</select>";
echo "\n\t\t<input type = \"hidden\" name = \"Skript\" value = \"LoescheWochenplan\">";
echo "\n\t\t\t<input type=\"Submit\" value=\"L�schen\">";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\">";
echo "<span class = \"smallmessage_mi\"><b>Wochenplan l�schen:&nbsp;</b>L�scht einen Wochenplan.<br><font color=\"#FF0000\"><b>Achtung:&nbsp;</b></font>Der L�schvorgang ist vollst�ndig und unwiderruflich!</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</form>\n";

echo "\n</table>";

echo ladeOszKopf_u();

echo ladeLink("index.php", "<b>StuPla-Admin</b>");
echo ladeLink("FerienAdmin.php", "Ferien-Admin");

echo ladeOszFuss();
?>