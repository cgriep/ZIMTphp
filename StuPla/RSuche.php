<?php
include_once("include/oszframe.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla_vars.inc.php");
include_once("include/stupla.inc.php");

$WTag = array("Montag","Dienstag","Mittwoch","Donnerstag","Freitag");
$BZeit = array("8:00-9:30","9:45-11:15","11:45-13:15","13:30-15:00","15:15-16:45","17:00-18:30");

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuserIntern,$DBpasswdIntern);
if(!$db)
	dieMsg("Keine Verbindung zur Datenbank möglich!");
mysql_select_db($DBname,$db);

//Timestamp Online-Version einlesen
$OnlineVersion = getAktuelleVersionWE();
if($OnlineVersion == -1)
	dieMsg("Zur Zeit sind keine Daten abrufbar!");

//Gueltigkeits-Timestamp der aktuellsten Version aus DB holen
$GueltigAb = getGueltigAbVersion($OnlineVersion);
if($GueltigAb == -1)
  dieMsg("Zur Zeit sind keine Daten abrufbar!");

//Aktuelles Schuljahr einlesen
$aktSJahr = getSJahr($OnlineVersion);
if($aktSJahr == -1)
  dieMsg("Zur Zeit sind keine Daten abrufbar!");

echo ladeOszKopf_o("OSZ IMT Raumsuche", "Suche nach freien Räumen");

echo "\n\n<table border = \"0\" width = \"100%\" height = \"60%\" cellspacing = \"0\">";

echo "\n\t<tr>";
echo "\n\t\t<td  width = \"49%\">&nbsp;</td>";
echo "\n\t\t<td colspan = \"3\" align = \"center\" valign = \"bottom\" nowrap>";
echo "\n\t\t\t<span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$GueltigAb) . "</b></span>";
echo "\n\t\t</td>";
echo "\n\t\t<td  width = \"49%\">&nbsp;</td>";
echo "\n\t</tr>";

//Wochenplan des Schuljahrs einlesen
$sql = "SELECT Woche, Montag, ID_Woche FROM T_Woche WHERE SJahr = \"$aktSJahr\" ORDER BY Jahr, Woche;";
$rs = mysql_query($sql,$db);

//Timestamp ersten Montag ermitteln
$sql = "SELECT DISTINCT MIN(Montag) FROM T_Woche WHERE SJahr = \"$aktSJahr\";";
$rs_1 = mysql_query($sql,$db);
$row = mysql_fetch_row($rs_1);
$sofe = false;
if(time() < $row[0]) //Datum liegt vor der ersten Schulwoche (in den Sommerferien) --> erste Woche selektieren
  $sofe = true;
mysql_free_result($rs_1);

echo "\n<form action = \"RSuche1.php\" method = \"post\">";

echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td class = \"zelle_beschr_bg ra_bl x1111\" id = \"form_ausw_beschr\">Kalenderwoche</td>";
echo "\n\t\t<td class = \"zelle_beschr_bg ra_bl x1010\" id = \"form_ausw_beschr\">Wochentag</td>";
echo "\n\t\t<td class = \"zelle_beschr_bg ra_bl x1111\" id = \"form_ausw_beschr\">Unterrichtszeit</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td class = \"zelle_inhalt_bg ra_bl x0101\" id = \"form_ausw_inhalt\" style = \"padding-left:20px\">";
echo "\n\t\t\t<select class = \"select\" name=\"ID_Woche\" size=\"20\">";
while ($row = mysql_fetch_row($rs))
{
  //Der Zeitpunkt des Umschaltens ist Samstag 00:00 Uhr
  if($sofe || (time() >= strtotime("-2 day", $row[1]) && time() < strtotime("+5 day", $row[1])))
  {
    echo "\n\t\t\t\t<option  value = \"$row[2]\" selected>";
    $sofe = false;
  }
  else
    echo "\n\t\t\t\t<option value = \"$row[2]\">";
  if($row[0] < 10)
    echo "0";
  echo $row[0] . ": " . date("d.m.y", $row[1]) . " - " . date("d.m.y", strtotime("+6 day", $row[1]));
  echo "</option>";
}
mysql_free_result($rs);
echo "\n\t\t\t</select>";
echo "\n\t\t</td>";

echo "\n\t\t<td class = \"zelle_inhalt_bg\" id = \"form_ausw_inhalt\">";
echo "\n\t\t\t<select class = \"select\" name=\"WTag\" size=\"5\">";
for ($i = 1; $i <=5; $i++)
{
  echo "\n\t\t\t\t<option value = \"$i\">";
  echo $WTag[$i-1];
  echo "</option>";
}
echo "\n\t\t\t</select>";
echo "\n\t\t</td>";

echo "\n\t\t<td class = \"zelle_inhalt_bg ra_bl x0101\" id = \"form_ausw_inhalt\" style = \"padding-right:20px\">";
echo "\n\t\t\t<select class = \"select\" name=\"Block\" size=\"5\">";
for ($i = 1; $i <=5; $i++)
{
  echo "\n\t\t\t\t<option value = \"$i\">";
  echo $BZeit[($i-1)];
  echo "</option>";
}
echo "\n\t\t\t</select>";
echo "\n\t\t</td>";
echo "\n\t\t<td><span class = \"smallmessage_mi\"><p style=\"text-align:justify; margin-left:0.5cm; margin-right:0.5cm\"><b><u>HINWEIS</u>:</b><br><br>Wenn Sie einen Raum abweichend von ihrem Stundenplan belegen möchten verwenden Sie bitte die Online-Reservierung. Nur so kann ein reibungsloser Ablauf garantiert werden.</p></span></td>";
echo "\n\t</tr>";

echo "\n\t<tr height = \"1%\">";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td align = \"center\" class = \"zelle_inhalt_bg ra_bl x0111\" id = \"form_ausw_inhalt\" colspan = \"3\">";
echo "\n\t\t\t\t<input type=\"submit\" value=\"Suchergebnis anzeigen\">";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

echo "\n</form>";

echo "\n\t<tr height = \"1%\">";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td align = \"right\" valign = \"top\" colspan = \"3\"><span class = \"smallmessage_kl\">Version: " . date("d.m.Y",$OnlineVersion) . "</span></td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</table>\n\n";
mysql_close($db);

echo ladeOszKopf_u();

echo ladeLink("http://www.oszimt.de", "<b>Home</b>");
echo ladeLink("../index.php", "Interner Bereich");

echo ladeOszFuss();
?>