<?php

include_once("include/oszframe.inc.php");
include_once("include/evaluation.inc.php");
include_once("include/evaluation.vars.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla.inc.php");

echo ladeOszKopf_o("OSZ-Umfrage","OSZ IMT - Ihre Meinung ist gefragt!");

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

echo "\n\n<div align = \"center\"><b>[Stimmabgabe]</b></div>";
echo "<br>";

echo "\n<table width = \"100%\" cellpadding = \"10\">";
echo "\n\t<tr>";
echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_ro x1111\" nowrap align = \"center\"><b>Klasse:</b>&nbsp;XYZ 32&nbsp;&nbsp;&nbsp;<b>Fach:</b>&nbsp;Musik&nbsp;&nbsp;&nbsp;<b>Lehrer/in:</b>&nbsp;Obermeier</td>";
echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
echo "\n\t</tr>";
echo "\n</table>";
echo "<br>";
//Fragen einlesen
$sql = "SELECT * FROM T_eval_Gruppen ORDER BY Gruppennummer;";
$rsGruppe = mysql_query($sql);
$counterFrage = 1;
	
//Schleife ueber alle Fragengruppen
while($rowGruppe = mysql_fetch_array($rsGruppe, MYSQL_ASSOC))
{
  echo "\n\t\t\t\t\t<div id = \"GruppeText\"><span class = \"Text-fett\">$rowGruppe[GruppeText]</class></div>";
  $sql = "SELECT * FROM T_eval_Fragen WHERE F_ID_Gruppe = $rowGruppe[ID_Gruppe] AND IstAktiv = 1 ORDER BY Fragennummer;";
  $rsFrage = mysql_query($sql, $db);
  //Schleife ueber alle Fragen
  while($rowFrage = mysql_fetch_array($rsFrage, MYSQL_ASSOC))
  {
    echo "\n\t\t\t\t\t<table style = \"width:100%\"; cellpadding = \"0\" cellspacing = \"0\" border = \"0\">";
    echo "\n\t\t\t\t\t\t<tr id = \"HoeheFrage\">";
    echo "\n\t\t\t\t\t\t\t<td></td>";
    echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\" style = \"vertical-align:top\">&nbsp;<span class = \"Text\">$counterFrage.</span></td>";
    echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\" colspan = \"" . ($rowFrage['FrageSel'] + 2) . "\"><span class = \"Text\">$rowFrage[FrageText]</span></td>";
    echo "\n\t\t\t\t\t\t\t<td></td>";
    echo "\n\t\t\t\t\t\t</tr>";
    
    echo "\n\t\t\t\t\t\t<tr id = \"HoeheAntwort\">";
    echo "\n\t\t\t\t\t\t\t<td></td>";
    echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\"></td>";
    echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\" width = \"49%\" style = \"text-align:right;\"><span class = \"Text\">$rowFrage[LinksText]</span></td>";
    
    for($i = 1; $i <= $rowFrage['FrageSel']; $i++)
    {
      echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\" align = \"center\">";
      if($rowFrage['ID_Frage'] == 99)
        echo "$i<br>";
      echo "&nbsp;<input type = \"radio\" name = \"Frage[$counterFrage][0]\" value = \"$i\">&nbsp;";
      echo "</td>";
      echo "\n\t\t\t\t\t\t\t<input type = \"hidden\" name = \"Frage[$counterFrage][1]\" value = \"$rowFrage[ID_Frage]\">";
    }
    echo "\n\t\t\t\t\t\t\t<td id = \"Frage_bg\" width = \"49%\"><span class = \"Text\">$rowFrage[RechtsText]</span></td>";
    echo "\n\t\t\t\t\t\t\t<td></td>";
    echo "\n\t\t\t\t\t\t<tr>";

    echo "\n\t\t\t\t\t\t<tr id = \"AbstandFrage\">";
    echo "\n\t\t\t\t\t\t\t<td><img src = \"pixel.gif\" id = \"Seitenabstand\"></td>";
    echo "\n\t\t\t\t\t\t\t<td><img src = \"pixel.gif\" width = \"30px\" height = \"1px\"></td>";
    echo "\n\t\t\t\t\t\t\t<td width =  \"99%\" colspan = \"" . ($rowFrage['FrageSel'] + 2) . "\"></td>";
    echo "\n\t\t\t\t\t\t\t<td><img src = \"pixel.gif\" id = \"Seitenabstand\"></td>";
    echo "\n\t\t\t\t\t\t</tr>";
	
    echo "\n\t\t\t\t\t</table>";
    $counterFrage++;
  }//Schleife ueber alle Fragen
}//Schleife ueber alle Fragengruppen
echo "\n\t\t<div align = \"center\"><input type = \"submit\" value = \"Absenden\"></div>";

echo ladeOszKopf_u();

echo ladeLink("../","<b>Interner&nbsp;Bereich</b>");
echo ladeLink("neuUmfrage.php","Neue&nbsp;Umfrage");
echo ladeLink("index.php","Meine&nbsp;Umfragen");
echo ladeLink("teilnehmer.php","Teilnehmer");
echo ladeLink("hilfe.php","Hilfe");

echo ladeOszFuss();
?>