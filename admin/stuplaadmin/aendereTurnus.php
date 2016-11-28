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

if(isset($_REQUEST['Skript']))
  $Skript = $_REQUEST['Skript'];//Aufrufstatus Skript
else
  $Skript = "";

if(isset($_REQUEST['SJahr']))
  $SJahr  = $_REQUEST['SJahr'];
else
  dieMsgLink("Unvollständige Eingabedaten!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");

echo ladeOszKopf_o("StuPla - Admin", "Turnus - Admin (Turnus ändern für Schuljahr $SJahr)");

//------------------------------------------------------------------------------
//Turnus loeschen
//------------------------------------------------------------------------------
if($Skript == "LoescheTurnus" && isset($_REQUEST['ID_Turnus']))
{
  $ID_Turnus = $_REQUEST['ID_Turnus'];
  if($ID_Turnus == "" || $SJahr == "")
    dieMsgLink("Unvollständige Eingabedaten!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");

  //Turnus loeschen
  $sql = "DELETE FROM T_Turnus WHERE ID_Turnus = $ID_Turnus;";
  mysql_query($sql, $db);
  //Zuordnung Woche-Turnus loeschen
  $sql = "DELETE FROM T_WocheTurnus WHERE F_ID_Turnus = $ID_Turnus;";
  mysql_query($sql, $db);
}

//------------------------------------------------------------------------------
//Turnus einfuegen
//------------------------------------------------------------------------------
if($Skript == "EinfuegenTurnus" && isset($_REQUEST['Turnus']) && isset($_REQUEST['ID_Gruppe']))
{
  $Turnus    = $_REQUEST['Turnus'];
  $ID_Gruppe = $_REQUEST['ID_Gruppe'];
  if($Turnus == "" || $ID_Gruppe == "" || $SJahr == "")
    dieMsgLink("Unvollständige Eingabedaten!", "TurnusAdmin.php", "Zurück zum Turnus-Admin");

  //Turnus schon vorhanden?
  $sql = "SELECT * FROM T_Turnus WHERE Turnus = \"$Turnus\" AND F_ID_Gruppe = $ID_Gruppe AND SJahr = \"$SJahr\";";
  $rs = mysql_query($sql, $db);
  if(mysql_num_rows($rs) == 0)//Turnus einfuegen
  {
    $sql = "INSERT INTO T_Turnus (Turnus, F_ID_Gruppe, SJahr) VALUES (\"$Turnus\", $ID_Gruppe, \"$SJahr\");";
    mysql_query($sql, $db);
  }
  mysql_freeresult($rs);
}

//------------------------------------------------------------------------------
//Aufbau des Admin-Frontend
//------------------------------------------------------------------------------

echo "\n<br>";
echo "\n<table border = \"0\" cellpadding = \"10\" cellspacing = \"0\" width = \"100%\">";

//Turnus löschen
$sql = "SELECT T.Turnus, T.ID_Turnus FROM T_Turnus AS T, T_TurnusGruppe AS TG WHERE T.SJahr = \"$SJahr\" AND T.F_ID_Gruppe = TG.ID_Gruppe ORDER BY TG.Nummer, T.Turnus;";
$rs = mysql_query($sql, $db);
$AnzTurnus = mysql_num_rows($rs);
echo "\n<form action = \"$PHP_SELF\" method = \"POST\">";
echo "\n\t<tr>";
echo "\n\t\t<td width = \"15%\">&nbsp;</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t\t<select name=\"ID_Turnus\" size=\"$AnzTurnus\">";

while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
  echo"\n\t\t\t<option value = \"$row[ID_Turnus]\">$row[Turnus]</option>";

mysql_freeresult($rs);
echo "\n\t\t\t</select>";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"center\" bgcolor = \"#eeeeee\" width = \"15%\">";
echo "\n\t\t\t<input type = \"hidden\" name = \"Skript\" value = \"LoescheTurnus\">";
echo "\n\t\t\t<input type = \"hidden\" name = \"SJahr\" value = \"$SJahr\">";
echo "\n\t\t\t<input type=\"Submit\" value=\"Löschen\">";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\" width = \"40%\">";
echo "<span class = \"smallmessage_mi\"><b>Turnus löschen:&nbsp;</b>Löscht einen Turnus.<br><font color=\"#FF0000\"><b>Achtung:&nbsp;</b></font>Mit dem Löschen eines Turnus werden gleichzeitig die Verbindungen mit den Kalenderwochen gelöscht. Mit dem Löschen eines Turnus nicht experimentieren!</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td width = \"15%\">&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</form>\n";
echo "\n\t<tr><td colspan = \"5\">&nbsp;</td></tr>";

//Turnus einfuegen
$sql = "SELECT T.Turnus, T.ID_Turnus , TG.Name FROM T_Turnus AS T, T_TurnusGruppe AS TG WHERE T.SJahr = \"$SJahr\" AND T.F_ID_Gruppe = TG.ID_Gruppe ORDER BY TG.Nummer, T.Turnus;";
$rs = mysql_query($sql, $db);
$AnzTurnus = mysql_num_rows($rs);
echo "\n<form action = \"$PHP_SELF\" method = \"POST\">";
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#eeeeee\" nowrap>";

while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
  echo"\n\t\t\t<b>$row[Turnus]</b> - $row[Name]<br>";

mysql_freeresult($rs);
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"center\" bgcolor = \"#eeeeee\">";
echo "\n\t\t\t<input type = \"hidden\" name = \"Skript\" value = \"EinfuegenTurnus\">";
echo "\n\t\t\t<input type = \"text\" name = \"Turnus\" size = \"2\" maxlength=\"4\"><br><font size=\"-1\">Turnus</font><br>";

$sql = "SELECT Name, ID_Gruppe FROM T_TurnusGruppe ORDER BY Nummer;";
$rs = mysql_query($sql, $db);
$AnzGruppen = mysql_num_rows($rs);
echo "\n\t\t\t<br><select name=\"ID_Gruppe\" size=\"$AnzGruppen\">";

while($row = mysql_fetch_array($rs, MYSQL_ASSOC))
  echo"\n\t\t\t<option value = \"$row[ID_Gruppe]\">$row[Name]</option>";

mysql_freeresult($rs);
echo "\n\t\t\t</select>";
echo "\n\t\t\t<input type = \"hidden\" name = \"SJahr\" value = \"$SJahr\">";
echo "\n\t\t\tGruppe<br><br><input type=\"Submit\" value=\"Einfügen\">";
echo "\n\t\t</td>";
echo "\n\t\t<td align = \"left\" bgcolor = \"#ffffff\">";
echo "\n\t\t\t<span class = \"smallmessage_mi\"><b>Turnus einfügen:&nbsp;</b>Fügt einen Turnus zu der ausgewählten Gruppe hinzu.</span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</form>\n";

echo "\n</table>";

echo ladeOszKopf_u();

echo ladeLink("index.php", "<b>StuPla-Admin</b>");
echo ladeLink("TurnusAdmin.php", "Turnus-Admin");
echo ladeLink("FerienAdmin.php", "Ferien-Admin");

echo ladeOszFuss();
?>