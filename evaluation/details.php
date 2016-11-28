<?php

include_once("include/oszframe.inc.php");
include_once("include/evaluation.inc.php");
include_once("include/evaluation.vars.inc.php");
include_once("include/evaluation.stat.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla.inc.php");
include_once("include/Lehrer.class.php");

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

$User = $_SERVER['REMOTE_USER'];
$LehrerDaten = new Lehrer($User, LEHRERID_EMAIL);

echo ladeOszKopf_o("OSZ-Umfrage","OSZ IMT - Ihre Meinung ist gefragt!");

echo "\n\n<div align = \"center\"><b>[Ergebnisse im Detail]</b></div>";
echo "<br>";

//Status: 'einzeln' -> einzelne Umfrage; 'user' -> alle Umfragen des angemeldeten User; 'alle' -> alle Umfragen
if(isset($_REQUEST['Status']))
  $Status = $_REQUEST['Status'];
else
  dieMsg("Unvollständige Eingabedaten!");
if(isset($_REQUEST['Umfrage']))
  $ID_Umfrage = $_REQUEST['Umfrage'];
elseif($Status == "einzeln")
  dieMsg("Unvollständige Eingabedaten!");

if($Status == "einzeln")
{
  //Unverschluesseltes Passwort ermitteln
  $ID_Umfrage = ermittleID($ID_Umfrage);

  $sqlID_Fragen   = "SELECT DISTINCT F_ID_Frage FROM T_eval_Ergebnisse WHERE F_ID_Umfrage = $ID_Umfrage;";
  $sqlErgebnisse  = "SELECT AnzStimmen FROM T_eval_Ergebnisse AS E WHERE E.F_ID_Umfrage = $ID_Umfrage AND";
  $sqlUmfragen    = "SELECT Anz_Note_1, Anz_Note_2, Anz_Note_3, Anz_Note_4, Anz_Note_5, Anz_Note_6, Datum, Klasse, Fach, Lehrer FROM T_eval_Umfragen WHERE ID_Umfrage = $ID_Umfrage;";
}

if($Status == "alle")
{
  $sqlID_Fragen   = "SELECT DISTINCT F_ID_Frage FROM T_eval_Ergebnisse;";
  $sqlErgebnisse  = "SELECT SUM(E.AnzStimmen) FROM T_eval_Ergebnisse AS E WHERE";
  $sqlUmfragen    = "SELECT SUM(Anz_Note_1), SUM(Anz_Note_2), SUM(Anz_Note_3), SUM(Anz_Note_4), SUM(Anz_Note_5), SUM(Anz_Note_6) FROM T_eval_Umfragen;";
}

if($Status == "user")
{
  $sqlID_Fragen  = "SELECT DISTINCT F_ID_Frage FROM T_eval_Ergebnisse AS E, T_eval_Umfragen AS U WHERE U.Lehrer = \"$User\" AND U.ID_Umfrage = E.F_ID_UMFRAGE;";
  $sqlErgebnisse = "SELECT SUM(E.AnzStimmen) FROM T_eval_Ergebnisse AS E, T_eval_Umfragen AS U WHERE U.Lehrer = \"$User\" AND U.ID_Umfrage = E.F_ID_UMFRAGE AND";
  $sqlUmfragen   = "SELECT SUM(Anz_Note_1), SUM(Anz_Note_2), SUM(Anz_Note_3), SUM(Anz_Note_4), SUM(Anz_Note_5), SUM(Anz_Note_6) FROM T_eval_Umfragen WHERE Lehrer = \"$User\";";
}

//FragenID's einlesen (Umfragebezogen -> geht nur ueber F_ID_Frage in T_eval_Ergebnisse)
$counter = 0;
$rsID_Frage = mysql_query($sqlID_Fragen);
$orString_ID_Frage = "";
if(mysql_num_rows($rsID_Frage) != 0) //Ergebnisse vorhanden
{
  while($rowID_Frage= mysql_fetch_array($rsID_Frage, MYSQL_ASSOC))
  {
    if($counter != 0)
      $orString_ID_Frage .= " || ";   
    $orString_ID_Frage .= "ID_Frage = " . $rowID_Frage['F_ID_Frage'];
    $counter++;
  }
  mysql_free_result($rsID_Frage);
  
  //Ergebnisse einlesen
  $rsErgebnisse = mysql_query($sqlErgebnisse);    

  //Umfragen einlesen
  $rsUmfragen = mysql_query($sqlUmfragen);
  $rowUmfragen = mysql_fetch_array($rsUmfragen);
  for($i = 0; $i <= 5; $i++)
    $listNoten[$i] = $rowUmfragen[$i];

  //Ueberschrift
  echo "\n<table width = \"100%\" cellpadding = \"10\">";
  echo "\n\t<tr>";
  echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
  switch($Status)
  {
    case "einzeln":
      echo "\n\t\t<td class = \"oszRahmen_bg ra_ro x1111\" nowrap align = \"center\"><b>Datum:</b>&nbsp;" . date("d.m.Y",$rowUmfragen[6]) . "&nbsp;&nbsp;&nbsp;<b>Klasse:</b>&nbsp;$rowUmfragen[7]&nbsp;&nbsp;&nbsp;<b>Fach:</b>&nbsp;$rowUmfragen[8]&nbsp;&nbsp;&nbsp;<b>Lehrer/in:</b>&nbsp;$LehrerDaten->Vorname $LehrerDaten->Name</td>";
      break;
    case "user":
      echo "\n\t\t<td class = \"oszRahmen_bg ra_ro x1111\" nowrap align = \"center\"><b>Alle Umfragen von Lehrer/in:</b>&nbsp;$LehrerDaten->Vorname $LehrerDaten->Name</td>";
      break;
    case "alle":
      echo "\n\t\t<td class = \"oszRahmen_bg ra_ro x1111\" nowrap align = \"center\"><b>Alle vorhandenen Umfragen</td>";
  }
  echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
  echo "\n\t</tr>";
  echo "\n</table>";
  echo "\n<br><br>";
  
  //Gruppen einlesen
  $sql = "SELECT * FROM T_eval_Gruppen ORDER BY Gruppennummer;";
  $rsGruppen = mysql_query($sql);

  $counterFrage = 1;

  //Schleife ueber alle Fragengruppen
  while($rowGruppe = mysql_fetch_array($rsGruppen, MYSQL_ASSOC))
  {
    echo "\n<div id = \"GruppeText\"><span class = \"Text-fett\">$rowGruppe[GruppeText]</class></div>";

    //Fragen einlesen (Gruppenbezogen)
    $sql = "SELECT * FROM T_eval_Fragen WHERE F_ID_Gruppe = $rowGruppe[ID_Gruppe] AND ($orString_ID_Frage || ID_Frage = 99) ORDER BY Fragennummer;";
    $rsFragen = mysql_query($sql);
    //Schleife ueber alle Fragen
    while($rowFrage = mysql_fetch_array($rsFragen, MYSQL_ASSOC))
    {
      echo "\n<table style = \"width:100%\"; cellpadding = \"0\" cellspacing = \"0\" border = \"0\">";
	
      echo "\n\t<tr id = \"HoeheFrage\">";
      echo "\n\t\t<td>&nbsp;</td>";
      echo "\n\t\t<td id = \"Frage_bg\" style = \"vertical-align:top\">&nbsp;<span class = \"Text\">$counterFrage.</span></td>";
      echo "\n\t\t<td id = \"Frage_bg\" colspan = \"" . ($rowFrage['FrageSel'] + 2) . "\"><span class = \"Text\">$rowFrage[FrageText]</span></td>";
      echo "\n\t\t<td>&nbsp;</td>";
      echo "\n\t</tr>";
	
      echo "\n\t<tr id = \"HoeheAntwort_d\">";
      echo "\n\t\t<td>&nbsp;</td>";
      echo "\n\t\t<td id = \"Frage_bg\"></td>";
      echo "\n\t\t<td id = \"Frage_bg\" width = \"39%\" style = \"text-align:right;\"><span class = \"Text\">" . $rowFrage['LinksText'] . "</span></td>";
      
      //Schleife ueber Selektoren der Frage
      if($rowFrage['ID_Frage'] == 99)//Allgemeine Unterrichtsbenotung -> Notenspiegel ausgeben
      {
        echo "\n\t\t<td colspan = \"6\" id = \"Frage_bg\" align = \"center\">";
        printNotSpi($listNoten);	
        echo "\n\t\t</td>";	
      }
      else//Ergebnisse der Fragen ausgeben
      {
        $listErgebnisse = array();
        for($i = 1; $i <= $rowFrage['FrageSel']; $i++)
        {
          echo "\n\t\t<td id = \"Frage_bg\" align = \"center\">";
          echo "<div id = \"ErgWerte\">";

          //Ergebniss der Frage ermitteln (fuer jeweiligen Selektor)
          $sql = $sqlErgebnisse . " E.F_ID_Frage = $rowFrage[ID_Frage] AND E.NumSel = $i;";
          $rsErgebnis = mysql_query($sql);
          $rowErgebnis = mysql_fetch_array($rsErgebnis);
        
          if(isset($rowErgebnis[0]))
          {
            echo "$rowErgebnis[0]";
	    $listErgebnisse[$i - 1] = $rowErgebnis[0];
          }
          else
	  {
            echo "0";  
	    $listErgebnisse[$i - 1] = 0;
	  }
	  echo "</div></td>";
        }
      }//for
       
      echo "\n\t\t<td id = \"Frage_bg\" width = \"39%\"><span class = \"Text\">$rowFrage[RechtsText]</span></td>";
      echo "\n\t\t<td>&nbsp;</td>";
      echo "\n\t</tr>";  
    
      if($rowFrage['ID_Frage'] != 99)//Bild mit Schnitt anzeigen
      {
        $schnitt = getAritMittel($listErgebnisse);
	$standardAbweichung = getStandardAbweichung($listErgebnisse);
        echo "\n\t<tr>";
        echo "\n\t\t<td>&nbsp;</td>";
        echo "\n\t\t<td id = \"Frage_bg\" colspan = \"2\"></td>";      
        echo "\n\t\t<td id = \"Frage_bg\" align = \"center\" colspan = \"" . ($rowFrage['FrageSel']) . "\"><img src = \"erzeugeImageSchnitt.php?schnitt=$schnitt&staabw=$standardAbweichung\" border = \"0\"></td>";
        echo "\n\t\t<td id = \"Frage_bg\" align = \"left\"><span style = \"background-color:#FFFFCA;\"><b>&#216;:&nbsp;" . number_format($schnitt, 2, ",", ".") . "</b></span>&nbsp;&nbsp;(&#963;:" . number_format($standardAbweichung, 2, ",", ".") . ")</td>";
        echo "\n\t\t<td>&nbsp;</td>";
        echo "\n\t</tr>";
      }
      
      echo "\n\t<tr height = \"4\">";
      echo "\n\t\t<td></td>";
      echo "\n\t\t<td id = \"Frage_bg\" colspan = \"" . ($rowFrage['FrageSel'] + 3) . "\"></td>";
      echo "\n\t\t<td></td>";
      echo "\n\t</tr>";
      
      echo "\n\t<tr id = \"AbstandFrage\">";
      echo "\n\t\t<td><img src = \"pixel.gif\" id = \"Seitenabstand\"></td>";
      echo "\n\t\t<td><img src = \"pixel.gif\" width = \"30px\" height = \"1px\"></td>";
      echo "\n\t\t<td width =  \"99%\" colspan = \"" . ($rowFrage['FrageSel'] + 2) . "\"></td>";
      echo "\n\t\t<td><img src = \"pixel.gif\" id = \"Seitenabstand\"></td>";
      echo "\n\t</tr>";
	
      $counterFrage++;
      echo "\n</table>";
    }//Schleife ueber alle Fragen
  }//Schleife ueber alle Fragengruppen
}//if (Umfrageergebnisse vorhanden?)

else//Keine Ergebnisse vorhanden
  echo "\n\n<div align = \"center\"><b>[Keine Ergebnisse vorhanden]</b></div>";

echo ladeOszKopf_u();

echo ladeLink("../","<b>Interner&nbsp;Bereich</b>");
echo ladeLink("neuUmfrage.php","Neue&nbsp;Umfrage");
echo ladeLink("index.php","Meine&nbsp;Umfragen");
echo ladeLink("teilnehmer.php","Teilnehmer");
echo ladeLink("hilfe.php","Hilfe");

echo ladeOszFuss();
?>