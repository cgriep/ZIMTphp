<?php
include_once("include/oszframe.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla_vars.inc.php");
include_once("include/stupla.inc.php");

$User = $_SERVER['REMOTE_USER'];

//Datenbankverbindung aufbauen
$db = mysql_connect($DBhost,$DBuser,$DBpasswd);
if(!$db)
  dieMsg("Keine Verbindung zur Datenbank möglich!");
mysql_select_db($DBname,$db);
	
//Timestamp Online-Version ermitteln
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

echo ladeOszKopf_o("OSZ IMT Unterrichtspläne der Lehrer","Unterrichtspläne der Lehrer");

echo "\n\n<table border = \"0\" width = \"100%\" height = \"1%\" cellspacing = \"0\" cellpadding = \"0\">";

//Neue Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"3\" align = \"center\" valign = \"bottom\" nowrap>";
echo "\n\t\t\t<span class = \"p_small\"><span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$GueltigAb) . "</b></span></span>";
echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

//Neue Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t\t<td class = \"zelle_beschr_bg ra_bl x1111\" id = \"form_ausw_beschr\">Kalenderwoche (KW)</td>";
echo "\n\t\t\t<td class = \"zelle_beschr_bg ra_bl x1110\" id = \"form_ausw_beschr\">Lehrer</td>";
echo "\n\t\t\t<td class = \"zelle_beschr_bg ra_bl x1110\" id = \"form_ausw_beschr\">Turnus</td>";
echo "\n\t\t<td>&nbsp;</td>";
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

echo "\n<form action = \"LPlan1.php\" method = \"post\" name = \"Formular\">";

//Neue Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td class = \"zelle_inhalt_bg ra_bl x0101\" id = \"form_ausw_inhalt\" align = \"center\">";

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

echo "\n\t\t<td class = \"zelle_inhalt_bg ra_bl x0100\" id = \"form_ausw_inhalt\" align = \"center\">";

//Lehrer einlesen
$sql = "SELECT DISTINCT Lehrer, Name, Vorname FROM T_StuPla WHERE Version = $OnlineVersion ORDER BY Name, Vorname;";
$rs = mysql_query($sql,$db);
echo "\n\t\t\t<select class = \"select\" name=\"Lehrer\" size=\"20\">";
while ($row = mysql_fetch_row($rs))
{
  $Nachname = ersetzeUmlaute($row[1]);
  if(strcmp(strtolower($Nachname),strtolower($User)) == 0)
    echo "\n\t\t\t\t<option selected value = $row[0]>";	
  else
    echo "\n\t\t\t\t<option value = $row[0]>";
  echo "$row[1], $row[2]";
  echo "</option>";
}
mysql_free_result($rs);
echo "\n\t\t\t</select>";

echo "\n\t\t</td>";

echo "\n\t\t<td class = \"zelle_inhalt_bg ra_bl x0100\" id = \"form_ausw_inhalt\" align = \"left\">";

//Turnusse einlesen
$sql = "SELECT DISTINCT T.Turnus FROM T_Turnus AS T, T_TurnusGruppe AS TG WHERE T.SJahr = \"$aktSJahr\" AND T.F_ID_Gruppe = TG.ID_Gruppe ORDER BY TG.Nummer, T.Turnus;";
$rs = mysql_query($sql,$db);
echo "\n\t\t\t\t<input type = \"checkbox\" value = \"true\" name = \"KW\" checked onClick=\"uncheck(1);\">&nbsp;<b><span class = \"checkboxtext\">Anzeige KW</span></b><br><br>";
echo "\n\t\t\t\t<input type = \"checkbox\" value = \"jede Woche\" name = \"selTurnus[]\" onClick=\"uncheck(2);\">&nbsp;<span class = \"checkboxtext\">jede Woche</span><br>";
while ($row = mysql_fetch_row($rs))
  echo "\n\t\t\t\t<input type = \"checkbox\" value = \"$row[0]\" name = \"selTurnus[]\" onClick=\"uncheck(2);\">&nbsp;<span class = \"checkboxtext\">$row[0]</span><br>";
mysql_free_result($rs);

echo "\n\t\t</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

//Neue Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td align = \"center\" class = \"zelle_inhalt_bg ra_bl x0111\" id = \"form_ausw_inhalt\" width = \"1%\" nowrap colspan = \"3\">";
echo "\n\t\t\t\t<input type=\"submit\" value=\"Stundenplan anzeigen\">";
echo "\n\t\t&nbsp;</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

echo "\n</form>";

//Neue Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td align = \"right\" valign = \"top\" colspan = \"3\"><span class = \"smallmessage_kl\">Version: " . date("d.m.Y",$OnlineVersion) . "</span></td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</table>\n\n";
mysql_close($db);

echo ladeOszKopf_u();

echo ladeLink("http://www.oszimt.de","<b>Home</b>");
echo ladeLink("../index.php","Interner Bereich");

echo ladeOszFuss();
?>

<script type="text/javascript">
<!--
function uncheck(wert)
{
  if (document.Formular.KW.checked && wert == 1)//Aufruf aus Checkbox "KW" (checked) -> Turnus zuruecksetzen
    for(var i=0;i<document.Formular.elements.length;i++)
    {
      var e = document.Formular.elements[i];
      if(e.name == "selTurnus[]")
        e.checked = false;
    }
  if(!document.Formular.KW.checked && wert == 1)//Aufruf aus Checkbox "KW" (unchecked) -> Wenn kein Turnus ausgewaehlt KW auf checked setzen
  {
    var TurnusGewaehlt = false;
    for(var i=0;i<document.Formular.elements.length;i++)
    {
      var e = document.Formular.elements[i];
      if(e.name == "selTurnus[]" && e.checked)
        TurnusGewaehlt = true;
    }//for
    if(!TurnusGewaehlt)
      document.Formular.KW.checked = true; 
  }//if
  else if(wert == 2)//Aufruf aus Checkbox "selTurnus"
  {
    var TurnusGewaehlt = false;
    for (var i=0;i<document.Formular.elements.length;i++)
    {
      var e = document.Formular.elements[i];
      if(e.name == "selTurnus[]" && e.checked)//KW auf unchecked setzen
      {
        document.Formular.KW.checked = false;
        TurnusGewaehlt = true;
      }
    }//for
    if(!TurnusGewaehlt)//Wenn kein Turnus ausgewaehlt KW auf checked setzen
      document.Formular.KW.checked = true; 
  }//else if
}
//-->
</script>