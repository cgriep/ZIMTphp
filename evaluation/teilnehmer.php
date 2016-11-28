<?php

include_once("include/oszframe.inc.php");
include_once("include/evaluation.inc.php");
include_once("include/evaluation.vars.inc.php");
include_once("include/evaluation.stat.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla.inc.php");
include_once("include/Lehrer.class.php");

echo ladeOszKopf_o("OSZ IMT - Unterrichtsbewertung","OSZ IMT - Unterrichtsbewertung");
echo "\n\n<div align = \"center\"><b>[Teilnehmerübersicht]</b></div>";

//--------------------------------------------------------------------------
//-------------------------------INHALT-ANFANG------------------------------
//--------------------------------------------------------------------------

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

$sql = "SELECT * FROM T_eval_Umfragen;";
$rs = mysql_query($sql);
$AnzUmfragen = mysql_num_rows($rs);
mysql_free_result($rs);
	
if($AnzUmfragen == 0)
  echo "<p><div align = \"center\"><h3>[Keine Umfragen vorhanden]</h3></div></p>";		
else
{
  //Teilnehmer und Anzahl einlesen
  $sql = "SELECT Lehrer, COUNT(*)  FROM T_eval_Umfragen GROUP BY Lehrer;";
  $rs = mysql_query($sql);
  $AnzTeilnehmer = mysql_num_rows($rs);

  //Lehrernamen holen
	$zaehler = 0;
	while($row = mysql_fetch_row($rs))
  {
    $Lehrer = new Lehrer($row[0], LEHRERID_EMAIL);
    $Teilnehmer[$zaehler]["Name"]   = $Lehrer->Name . ", " . $Lehrer->Vorname;
    $Teilnehmer[$zaehler]["AnzEval"] = $row[1];
    $Name[] = $Teiln["Name"];//fuer Sortierung
    $zaehler++;
  }
  array_multisort($Name, SORT_ASC, $Teilnehmer);
      
  echo "<p>&nbsp;</p>";
  echo "\n<table border = \"0\" width = \"100%\" cellpadding = \"5\" cellspacing = \"0\">";
  echo "\n\t<tr>";
  echo "\n\t\t<td width = \"40%\">&nbsp;</td>";
  echo "\n\t\t<td class = \"oszrahmen_bg ra_bl x1001\" align = \"center\"><b>Lehrer</b></td>";
  echo "\n\t\t<td class = \"oszrahmen_bg ra_bl x1101\" align = \"center\"><b>Umfragen</b></td>";
  echo "\n\t\t<td width = \"40%\">&nbsp;</td>";
  echo "\n\t</tr>";
	
  $zaehler = 0;
  foreach($Teilnehmer as $Teiln)
  {
    $zaehler++;
    if($zaehler != $AnzTeilnehmer)
    {
      $rahmen1 = "x1001";
      $rahmen2 = "x1101";
    } 
    else
    {
      $rahmen1 = "x1011";
      $rahmen2 = "x1111";
    }
    echo "\n\t<tr>";
    echo "\n\t\t<td>&nbsp;</td>";
    echo "\n\t\t<td class = \"oszrahmen_bg ra_bl $rahmen1\" nowrap>" . $Teiln["Name"] . "</td>";
    echo "\n\t\t<td align = \"center\" class = \"oszrahmen_bg ra_bl $rahmen2\">" . $Teiln["AnzEval"] . "</td>";
    echo "\n\t\t<td>&nbsp;</td>";
    echo "\n\t</tr>";
  }
  mysql_free_result($rs);
  echo "\n</table>";
}//Es gibt Umfragen
//--------------------------------------------------------------------------
//-------------------------------INHALT_ENDE--------------------------------
//--------------------------------------------------------------------------

echo ladeOszKopf_u();

echo ladeLink("../","<b>Interner&nbsp;Bereich</b>");
echo ladeLink("neuUmfrage.php","Neue&nbsp;Umfrage");
echo ladeLink("index.php","Meine&nbsp;Umfragen");
echo ladeLink("hilfe.php","Hilfe");

echo ladeOszFuss();
?>