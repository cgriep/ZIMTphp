<?php
include_once("include/oszframe.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla_vars.inc.php");
include_once("include/stupla.inc.php");

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
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

echo ladeOszKopf_o("OSZ IMT Raumpläne", "Raumbelegung");
	
echo "\n\n<table border = \"0\" width = \"100%\" height = \"1%\" cellspacing = \"0\">";

//Neue Zeile
echo "\n\t<tr>";
echo "\n\t\t<td width = \"40%\">&nbsp;</td>";
echo "\n\t\t<td colspan = \"2\" align = \"center\" valign = \"bottom\" nowrap>";
echo "\n\t\t\t<span class = \"p_small\"><span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$GueltigAb) . "</b></span></span>";
echo "\n\t\t</td>";
echo "\n\t\t<td width = \"40%\">&nbsp;</td>";
echo "\n\t</tr>";

//Wochenplan des Schuljahrs einlesen
$sql = "SELECT Woche, Montag, ID_Woche FROM T_Woche WHERE SJahr = \"$aktSJahr\" ORDER BY Jahr, Woche;";
$rs = mysql_query($sql,$db);
echo "\n<form action = \"RPlan1.php\" method = \"post\">";

//Timestamp ersten Montag ermitteln
$sql = "SELECT DISTINCT MIN(Montag) FROM T_Woche WHERE SJahr = \"$aktSJahr\";";
$rs_1 = mysql_query($sql,$db);
$row = mysql_fetch_row($rs_1);
$sofe = false;
if(time() < $row[0]) //Datum liegt vor der ersten Schulwoche (in den Sommerferien) --> erste Woche selektieren
  $sofe = true;
mysql_free_result($rs_1);

//Neue Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td class = \"zelle_beschr_bg ra_bl x1111\" id=\"form_ausw_beschr\">Kalenderwoche</td>";
echo "\n\t\t<td class = \"zelle_beschr_bg ra_bl x1110\" id=\"form_ausw_beschr\">Räume</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td class = \"zelle_inhalt_bg ra_bl x0101\" id=\"form_ausw_inhalt\" style = \"padding-left:20px\">";

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

echo "\n\t\t<td class = \"zelle_inhalt_bg ra_bl x0100\" id=\"form_ausw_inhalt\" style = \"padding-right:20px\">";

//Raeume einlesen
$sql = "SELECT DISTINCT Raum FROM T_StuPla WHERE Version = $OnlineVersion ORDER BY Raum;";
$rs = mysql_query($sql,$db);
include("include/raeume.inc.php");
echo "\n\t\t\t<select class = \"select\" name = \"Raum\" size = \"20\">";
while ($row = mysql_fetch_row($rs))
{
  echo "\n\t\t\t\t<option value = \"$row[0]\">";
  echo "$row[0]";
  // Raumbezeichnung anzeigen
  $Raum = $row[0];
  if ( is_numeric(substr($Raum,0,1)) && substr($Raum,1,1) != "." )
    $Raum = substr($Raum,0,1).".".substr($Raum,1);
  $sql = "SELECT Raumbezeichnung, Kapazitaet FROM T_Raeume WHERE Raumnummer='".$Raum."'";
  if (!$query = mysql_query($sql, $db))
    echo mysql_error();
  if ( !$r = mysql_fetch_array($query) )
  {
    $r["Raumbezeichnung"] = "";
    $r["Kapazitaet"] = "";
  }
  mysql_free_result($query);
  if ( is_numeric($r["Kapazitaet"]) && $r["Raumbezeichnung"] != "" )
  {
    echo "  ".stripslashes($r["Raumbezeichnung"])." (".$r["Kapazitaet"]." Schülerplätze";
    $canz = ComputerAnzahl($Raum, $db);
    if ( $canz != 0 ) echo " / $canz Computer";
      echo ")";
  }
  // Ende Raumbezeichnung anzeigen
  echo "</option>";
}
mysql_free_result($rs);	
echo "\n\t\t\t</select>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

echo "\n\t<tr height = \"1%\">";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td align = \"center\" class = \"zelle_inhalt_bg ra_bl x0111\" id=\"form_ausw_inhalt\" colspan = \"2\">";
echo "\n\t\t\t\t<input type=\"submit\" value=\"Raumbelegung anzeigen\">";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

echo "\n</form>";

echo "\n\t<tr height = \"1%\">";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td align = \"right\" valign = \"top\" colspan = \"2\"><span class = \"smallmessage_kl\">Version: " . date("d.m.Y",$OnlineVersion) . "</span></td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</table>\n\n";
mysql_close($db);

echo ladeOszKopf_u();

echo ladeLink("http://www.oszimt.de", "<b>Home</b>");
echo ladeLink("../index.php", "Interner Bereich");  

echo ladeOszFuss();
?>