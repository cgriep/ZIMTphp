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
$Status = $_REQUEST['Status'];

//--------------------------------------------------------------------------
//-------------------------------INHALT-ANFANG------------------------------
//--------------------------------------------------------------------------

//------------------------------Ergebnis Download---------------------------
if ( $Status == "downloadUmfrage" )
{
  $ID_Umfrage  = $_REQUEST['Umfrage'];
  $ID_Umfrage = ermittleID($ID_Umfrage, 2);
  
  //Allgemeine Infos zur Umfrage
  $sql = "SELECT * FROM T_umfrage_Umfragen WHERE ID_Umfrage = $ID_Umfrage;";
  $rs = mysql_query($sql);
  $row = mysql_fetch_array($rs, MYSQL_ASSOC);
  $ID_Bg = $row['F_ID_Bg'];
  $data = "\"Klasse\"\t\"Datum\"\t\"Teilnehmer\"";
  $data .= "\n\"" . $row['Klasse'] . "\"\t\"" . date("d.m.Y",$row['Datum']) . "\"\t\"" . $row['AnzStimmen'] . "\"";
  mysql_free_result($rs);

  //Fragen (Gruppe)
  $sql = "SELECT * FROM T_umfrage_Gruppen as G, T_umfrage_GruppenBildungsgaenge as GB WHERE G.ID_Gruppe = GB.F_ID_Gruppe AND GB.F_ID_Bg = $ID_Bg ORDER BY Gruppennummer;";
  $rsGruppen = mysql_query($sql);
  while ( $rowGruppen = mysql_fetch_array($rsGruppen, MYSQL_ASSOC) )//Schleife ueber alle Gruppen
  {
    $data .= "\n\"" . $rowGruppen['Gruppennummer'] . ": " . $rowGruppen['GruppeText'] . "\"";

    if ( $rowGruppen['Typ'] == 0 )//Einzel-Antwort-Fragen          
      $sql = "SELECT * FROM T_umfrage_Fragen WHERE F_ID_Gruppe = " . $rowGruppen['ID_Gruppe'] . " ORDER BY Fragennummer;";
    elseif ( $rowGruppen['Typ'] == 2 )//MC-Fragen
      $sql = "SELECT * FROM T_umfrage_Fragen_mc WHERE F_ID_Gruppe = " . $rowGruppen['ID_Gruppe'] . " ORDER BY Fragennummer;";
    $rsFragen = mysql_query($sql);
    
    while ( $rowFragen = mysql_fetch_array($rsFragen, MYSQL_ASSOC) )//Schleife ueber alle Fragen einer Gruppe
    {
      $data .= "\n\"" . $rowGruppen['Gruppennummer'] . "." . $rowFragen['Fragennummer'] . ": " . $rowFragen['FrageText'] . "\"\n";      
      if ( $rowGruppen['Typ'] == 0 )//Einzel-Antwort-Fragen          
      {
        for ( $NumSel = 1; $NumSel <= $rowFragen['FrageSel']; $NumSel++ )
        {
          $sql = "SELECT * FROM T_umfrage_Ergebnisse WHERE F_ID_Frage = " . $rowFragen['ID_Frage'] . " AND F_ID_Umfrage = $ID_Umfrage AND NumSel = $NumSel;";
	  $rsErgebnis = mysql_query($sql);
	  if ( mysql_num_rows($rsErgebnis) == 1 )
	  {
	    $rowErgebnis = mysql_fetch_array($rsErgebnis, MYSQL_ASSOC);
	    $data .= "\"" . $rowErgebnis['AnzStimmen'] . "\"\t";
	  }
	  else
	    $data .= "\"0\"\t";  
	  mysql_free_result($rsErgebnis);
        }//for
      }//if
      elseif ( $rowGruppen['Typ'] == 2 )//MC-Fragen
      {
        $sql = "SELECT * FROM T_umfrage_Ergebnisse_mc WHERE F_ID_Frage = " . $rowFragen['ID_Frage'] . " AND F_ID_Umfrage = $ID_Umfrage;";
	$rsErgebnis = mysql_query($sql);
	if ( mysql_num_rows($rsErgebnis) == 1 )
	{
	  $rowErgebnis = mysql_fetch_array($rsErgebnis, MYSQL_ASSOC);
	  $data .= "\"" . $rowErgebnis['AnzStimmen'] . "\"\t";
	}
	else
	{
	  $data .= "\"0\"\t";  
	}
	mysql_free_result($rsErgebnis);	
      }
    }    
    mysql_free_result($rsFragen);
  }
  mysql_free_result($rsGruppen);

  header('Content-type: application/octet-stream');
  header('Content-Disposition: attachment; filename=' . str_replace(" ","_",$row['Klasse']) .'.xls');
  echo $header . $data;
  die();
}

echo ladeOszKopf_o("OSZ IMT - Anfangsumfrage","OSZ IMT - Anfangsumfrage");

//-------------------------------Umfrage loeschen---------------------------
if($Status == "loescheUmfrage")
{
  $ID_Umfrage  = $_REQUEST['Umfrage'];
  $ID_Umfrage = ermittleID($ID_Umfrage, 2);
  $sql = "DELETE FROM T_umfrage_Umfragen WHERE ID_Umfrage = $ID_Umfrage;";
  mysql_query($sql);
  $sql = "DELETE FROM T_umfrage_Ergebnisse WHERE F_ID_Umfrage = $ID_Umfrage;";
  mysql_query($sql);
  $sql = "DELETE FROM T_umfrage_Ergebnisse_mc WHERE F_ID_Umfrage = $ID_Umfrage;";
  mysql_query($sql);  
}

//-------------------------------Umfrage sperren/freigeben------------------
if($Status == "setzeSperre")
{
  $ID_Umfrage  = $_REQUEST['Umfrage'];
  $ID_Umfrage = ermittleID($ID_Umfrage, 2);  
  $akt = $_REQUEST['akt'];
  $sql = "UPDATE T_umfrage_Umfragen Set Gesperrt = $akt WHERE ID_Umfrage = $ID_Umfrage;";
  mysql_query($sql);
}

//-------------------------------neue Umfrage-------------------------------
if($Status == "neuUmfrage")
{
  $Bg      = (int)$_REQUEST['Bg'];
  $Lehrer  = $_REQUEST['Lehrer'];
  $Klasse  = $_REQUEST['Klasse'];
  $Fach    = $_REQUEST['Fach'];
  $Datum   = (int)$_REQUEST['Datum'];
  
  //Sicherheitsueberpruefung zur Vermeidung doppelter Eintraege
  $sql = "SELECT * FROM T_umfrage_Umfragen WHERE Lehrer = \"$Lehrer\" AND Klasse = \"$Klasse\" AND Datum = $Datum;";
  $rs = mysql_query($sql);
  if(mysql_num_rows($rs) == 0)
  {
    mysql_free_result($rs);
    do//Keine doppelten Passwoerter
    {
      $pwd = makePwd(8);
      $sql = "SELECT Passwort FROM T_umfrage_Umfragen WHERE Passwort = \"$pwd\";";
      $rs = mysql_query($sql);
      $doppelt = false;
      if(mysql_num_rows($rs) != 0)
        $doppelt = true;
    }
    while($doppelt);
    $sql = "INSERT INTO T_umfrage_Umfragen (F_ID_Bg,Lehrer,Klasse,AnzStimmen,Passwort,Datum,Gesperrt) VALUES ($Bg,\"$Lehrer\",\"$Klasse\",0,\"$pwd\",$Datum,0);";
    if(!mysql_query($sql))
      dieMsg("Das Anlegen der Umfrage ist fehlgeschlagen!");
  }
  mysql_free_result($rs);
}
//---------------------Umfragenuebersicht aufbauen----------------

echo "\n<!---------------------------------------------------------->";
echo "\n<!------------- Uebersicht Umfragen ------------------->";

echo "\n<table border = \"0\" width = \"100%\" cellpadding = \"5\" cellspacing = \"0\">";

//1.Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"9\" align = \"center\"><b>Übersicht Umfragen</b></td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

//2.Zeile
echo "\n\t<tr>";
echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\"><b>Datum</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\"><b>Schulart</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\"><b>Bildungsgang</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\"><b>Klasse</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\"><b>Lehrer</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\"><b>Teilnehmer</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\"><b>Passwort</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\"><b>Sperre</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\"><b>Löschen</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1111\" align = \"center\"><b>iLink</b></td>";
echo "\n\t\t<td width = \"49%\">&nbsp;</td>";	
echo "\n\t</tr>";

//3.Zeile
$sql =" SELECT * FROM T_umfrage_Umfragen;";
$rs = mysql_query($sql);
$anzUmfragen = mysql_num_rows($rs);
if ( $anzUmfragen == 0 )
{
  echo "\n\t<tr>";
  echo "\n\t\t<td>&nbsp;</td>";
  echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x0111\" align = \"center\"colspan = \"10\" align = \"center\">Keine Umfragen vorhanden</td>";
  echo "\n\t\t<td>&nbsp;</td>";
  echo "\n\t</tr>";
}

while ( $row = mysql_fetch_array($rs, MYSQL_ASSOC) )//Schleife ueber alle Umfragen
{
  echo "\n\t<tr>";
  echo "\n\t\t<td width = \"49%\">&nbsp;</td>";
  echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x0011\" align = \"center\">" . date("d.m.Y",$row['Datum']) . "</td>";

  //ID_Schulart anhand von ID_Bildungsgang ermitteln
  $sql = "SELECT F_ID_SArt FROM T_Bildungsgaenge WHERE ID_Bg = " . $row['F_ID_Bg'] . ";";
  $rs_1 = mysql_query($sql);
  $row_1 = mysql_fetch_row($rs_1);
  mysql_free_result($rs_1);    
  $ID_SArt = $row_1[0];

  //Bezeichnung der Schulart ermitteln
  $sql = "SELECT Bezeichnung FROM T_Schularten WHERE ID_SArt = $ID_SArt;";
  $rs_1 = mysql_query($sql);
  $row_1 = mysql_fetch_row($rs_1);  
  mysql_free_result($rs_1);  
  echo "\n\t\t<td nowrap class = \"oszRahmen_bg ra_bl x0011\" align = \"center\">$row_1[0]</td>";

  //Bezeichnung des Bildungsgangs ermitteln
  $sql = "SELECT Bezeichnung FROM T_Bildungsgaenge WHERE ID_Bg = " . $row['F_ID_Bg'] . ";";
  $rs_1 = mysql_query($sql);
  $row_1 = mysql_fetch_row($rs_1);  
  mysql_free_result($rs_1);
  echo "\n\t\t<td nowrap class = \"oszRahmen_bg ra_bl x0011\" align = \"center\">$row_1[0]</td>";
  echo "\n\t\t<td nowrap class = \"oszRahmen_bg ra_bl x0011\" align = \"center\">" . $row['Klasse'] . "</td>";  
  
  $LehrerInfo = new Lehrer($row['Lehrer'], LEHRERID_EMAIL);
  echo "\n\t\t<td nowrap class = \"oszRahmen_bg ra_bl x0011\" align = \"center\">$LehrerInfo->Vorname $LehrerInfo->Name</td>";
  echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x0011\" align = \"center\">" . $row['AnzStimmen'] . "</td>";
  echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x0011\" align = \"center\">" . $row['Passwort'] . "</td>";
  echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x0011\" align = \"center\">";
  //Sperre
  if($row['Gesperrt'] == 1)
    echo "\n\t\t\t<a href=\"index.php?Status=setzeSperre&akt=0&Umfrage=" . md5($row['ID_Umfrage']) . "\"><img src = \"image/stop.gif\" border = \"0\" alt= \"Sperre ausschalten\"></a>";
  else
    echo "\n\t\t\t<a href=\"index.php?Status=setzeSperre&akt=1&Umfrage=" . md5($row['ID_Umfrage']) . "\"><img src = \"image/go.gif\" border = \"0\" alt= \"Sperre einschalten\"></a>";
  echo"</td>";
  //Umfrage loeschen
  echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x0011\" align = \"center\"><a href=\"index.php?Status=loescheUmfrage&Umfrage=" . md5($row['ID_Umfrage']) . "\"><img src = \"image/papierkorb.gif\" border = \"0\" alt= \"Umfrage löschen\"></a></td>";
  //iLink
  echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x0111\" align = \"center\"><a href=\"index.php?Status=downloadUmfrage&Umfrage=" . md5($row['ID_Umfrage']) . "\">iLink</a></td>";
  echo "\n\t\t<td width = \"49%\">&nbsp;</td>";	
  echo "\n\t</tr>";
}
echo "\n</table>";

//--------------------------------------------------------------------------
//-------------------------------INHALT_ENDE--------------------------------
//--------------------------------------------------------------------------

echo ladeOszKopf_u();

echo ladeLink("../","<b>Interner&nbsp;Bereich</b>");
echo ladeLink("neuUmfrage.php","Neue&nbsp;Umfrage");
//echo ladeLink("teilnehmer.php","Teilnehmer");
//echo ladeLink("hilfe.php","Hilfe");

echo ladeOszFuss();
?>