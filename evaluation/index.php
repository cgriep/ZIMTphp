<?php
include_once("include/oszframe.inc.php");
include_once("include/evaluation.inc.php");
include_once("include/evaluation.vars.inc.php");
include_once("include/evaluation.stat.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla.inc.php");
include_once("include/Lehrer.class.php");

session_start();

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
mysql_select_db($DBname,$db);

$User = $_SERVER['REMOTE_USER'];
$LehrerDaten = new Lehrer($User, LEHRERID_EMAIL);

if(isset($_REQUEST['Status']))
  $Status  = $_REQUEST['Status'];
else
  $Status = "";

if(!isset($_SESSION['user_settings']))//Default-Werte einstellen
{
  readSchuljahre($SJ_list);
  foreach($SJ_list as $SJ)
  {
    $SJ_Beginn = strtotime($SJ['Beginn']);
    $SJ_Ende   = strtotime($SJ['Ende']);
    if(time() >= $SJ_Beginn && time() <= $SJ_Ende && $SJ['SJahr'] != 'alle')//User-Umfragen nur fuer aktuelles SJ anzeigen
      $user_settings[$SJ['SJahr']]['view']  = "open";
    else
      $user_settings[$SJ['SJahr']]['view']  = "close";
    $user_settings[$SJ['SJahr']]['sort']  = "Datum";
    $user_settings[$SJ['SJahr']]['order'] = "DESC";
  }
  $_SESSION['user_settings'] = $user_settings;
}

else
{
  //Vorhandene Einstellungen laden
  $user_settings = $_SESSION['user_settings'];
   
  if(isset($_REQUEST['SJahr']))
  {
    $SJahr = $_REQUEST['SJahr'];
    
    //Setzen der Sortierung
    if(isset($_REQUEST['sort']))
    {
      $sort  = $_REQUEST['sort'];
      if($sort != "Klasse" && $sort != "Fach")
        $sort = "Datum";
      if(isset($_REQUEST['order']))
      {
        $order  = $_REQUEST['order'];
        if($order != "ASC")
          $order = "DESC";
      }
      else
        $order = "DESC";
      $user_settings[$SJahr]['sort']  = $sort;
      $user_settings[$SJahr]['order'] = $order;
    }
    
    //Setzen der Anzeige (+/-)
    if(isset($_REQUEST['view']))
    {
      $view  = $_REQUEST['view'];
      if($view != "open")
        $view = "close";
      $user_settings[$SJahr]['view']  = $view;
    }
  } 
  
  //Einstellungen aktualisieren
  $_SESSION['user_settings'] = $user_settings;
}

echo ladeOszKopf_o("OSZ IMT - Unterrichtsbewertung","OSZ IMT - Unterrichtsbewertung");

//--------------------------------------------------------------------------
//-------------------------------INHALT-ANFANG------------------------------
//--------------------------------------------------------------------------

//-------------------------------Umfrage loeschen---------------------------
if($Status == "loescheUmfrage")
{
  $ID_Umfrage  = $_REQUEST['Umfrage'];
  $ID_Umfrage = ermittleID($ID_Umfrage);
  $sql = "DELETE FROM T_eval_Umfragen WHERE ID_Umfrage = $ID_Umfrage;";
  mysql_query($sql);
  $sql = "DELETE FROM T_eval_Ergebnisse WHERE F_ID_Umfrage = $ID_Umfrage;";
  mysql_query($sql);
}
//-------------------------------Umfrage sperren/freigeben------------------
if($Status == "setzeSperre")
{
  $ID_Umfrage  = $_REQUEST['Umfrage'];
  $ID_Umfrage = ermittleID($ID_Umfrage);  
  $akt = $_REQUEST['akt'];
  $sql = "UPDATE T_eval_Umfragen Set Gesperrt = $akt WHERE ID_Umfrage = $ID_Umfrage;";
  mysql_query($sql);
}

//-------------------------------neue Umfrage-------------------------------
if($Status == "neuUmfrage")
{
  $Lehrer  = $_REQUEST['Lehrer'];
  $Klasse  = $_REQUEST['Klasse'];
  $Fach    = $_REQUEST['Fach'];
  $Datum   = (int)$_REQUEST['Datum'];
  //Sicherheitsueberpruefung zur Vermeidung doppelter Eintraege
  $sql = "SELECT * FROM T_eval_Umfragen WHERE Lehrer = \"$Lehrer\" AND Klasse = \"$Klasse\" AND Fach = \"$Fach\" AND Datum = $Datum;";
  $rs = mysql_query($sql);
  if(mysql_num_rows($rs) == 0)
  {
    mysql_free_result($rs);
    do//Keine doppelten Passwoerter
    {
      $pwd = makePwd(8);
      $sql = "SELECT Passwort FROM T_eval_Umfragen WHERE Passwort = \"$pwd\";";
      $rs = mysql_query($sql);
      $doppelt = false;
      if(mysql_num_rows($rs) != 0)
        $doppelt = true;
    }
    while($doppelt);
    $sql = "INSERT INTO T_eval_Umfragen (Fach,Lehrer,Klasse,AnzStimmen,Passwort,Datum,Gesperrt) VALUES (\"$Fach\",\"$Lehrer\",\"$Klasse\",0,\"$pwd\",$Datum,0);";
    if(!mysql_query($sql))
      dieMsg("Das Anlegen der Umfrage ist fehlgeschlagen!");
  }
  mysql_free_result($rs);
}
//---------------------Umfragenuebersicht aufbauen----------------

//Anzahl Umfragen/Teilnehmer (alle)
$sql = "SELECT COUNT(*), SUM(AnzStimmen) FROM T_eval_Umfragen;";
$rs = mysql_query($sql);
$row = mysql_fetch_row($rs);
mysql_free_result($rs);
$AnzAlleUmfragen = (int)$row[0];
$AnzAlleTeilnehmer = (int)$row[1];

//Anzahl teilnehmender Lehrer
$sql = "SELECT COUNT(*) FROM T_eval_Umfragen GROUP BY Lehrer;";
$rs = mysql_query($sql);
$row = mysql_fetch_row($rs);
$AnzAlleLehrer = mysql_numrows($rs);
mysql_free_result($rs);

//Anzahl Umfragen/Teilnehmer (User)
$sql = "SELECT COUNT(*), SUM(AnzStimmen) FROM T_eval_Umfragen WHERE Lehrer = \"$User\";";
$rs = mysql_query($sql);
$row = mysql_fetch_row($rs);
mysql_free_result($rs);
$AnzUserUmfragen = (int)$row[0];
$AnzUserTeilnehmer = (int)$row[1];

//Mittelwert Umfragen (alle)
$alleMittelwert = round(ergUmfragen_2(),2);
//Mittelwert Umfragen (User)
$UserMittelwert = round(ergUmfragen_2($User),2);


echo "\n<!---------------------------------------------------------->";
echo "\n<!------------- Uebersicht alle Umfragen ------------------->";

echo "\n<table border = \"0\" width = \"700\" cellpadding = \"5\" cellspacing = \"0\">";
//1.Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"5\" align = \"center\"><b>Alle Umfragen</b></td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
//2.Zeile
echo "\n\t<tr>";
echo "\n\t\t<td width = \"100\">&nbsp;</td>";
echo "\n\t\t<td width = \"40\" class = \"oszRahmen_bg ra_ro x1001\" align = \"center\"><b>Umf.</b></td>";
echo "\n\t\t<td width = \"40\" class = \"oszRahmen_bg ra_ro x1001\" align = \"center\"><b>Le.</b></td>";
echo "\n\t\t<td width = \"40\" class = \"oszRahmen_bg ra_ro x1001\" align = \"center\"><b>Sü.</b></td>";
echo "\n\t\t<td width = \"40\" class = \"oszRahmen_bg ra_ro x1001\" align = \"center\"><b>&#216;</b></td>";
echo "\n\t\t<td width = \"250\"class = \"oszRahmen_bg ra_ro x1101\" align = \"center\"><b>Notenspiegel</b></td>";
echo "\n\t\t<td>&nbsp;</td>";	
echo "\n\t</tr>";
//3.Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_ro x1011\" align = \"center\">$AnzAlleUmfragen</td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_ro x1011\" align = \"center\">$AnzAlleLehrer</td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_ro x1011\" align = \"center\">$AnzAlleTeilnehmer</td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_ro x1011\" align = \"center\">" . number_format($alleMittelwert, 2, ",", ".") . "</td>";
//Ausgabe Notenspiegel
echo "\n\t\t<td class = \"oszRahmen_bg ra_ro x1111\" align = \"center\" width = \"1%\">";
$sql = "SELECT SUM(Anz_Note_1), SUM(Anz_Note_2), SUM(Anz_Note_3), SUM(Anz_Note_4), SUM(Anz_Note_5), SUM(Anz_Note_6) FROM T_eval_Umfragen;";
$rsNoten = mysql_query($sql);
printNotSpi(mysql_fetch_row($rsNoten));
mysql_freeresult($rsNoten);	
echo "\n\t\t</td>";
echo "\n\t\t<td><a href=\"details.php?Status=alle\"><img src = \"image/i.gif\" border = \"0\" alt= \"Details\"></a></td>";
echo "\n\t</tr>";

echo "\n\t<tr>";
echo "\n\t\t<td colspan = \"7\">&nbsp;</td>";
echo "\n\t</tr>";

echo "\n<!---------------------------------------------------------->";
echo "\n<!------------- Uebersicht User-Umfragen ------------------->";

//1.Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"5\" align = \"center\"><b>Meine Umfragen</b> [$LehrerDaten->Vorname $LehrerDaten->Name]</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
//2.Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1001\" align = \"center\"><b>Umf.</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1001\" align = \"center\" colspan = \"2\"><b>Sü.</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1001\" align = \"center\"><b>&#216;</b></td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1101\" align = \"center\"><b>Notenspiegel</b></td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";
//3.Zeile
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\">$AnzUserUmfragen</td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\" colspan = \"2\">$AnzUserTeilnehmer</td>";
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1011\" align = \"center\">" . number_format($UserMittelwert, 2, ",", ".") . "</td>";
//Ausgabe Notenspiegel
echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1111\" align = \"center\" width = \"1%\">";
$sql = "SELECT SUM(Anz_Note_1), SUM(Anz_Note_2), SUM(Anz_Note_3), SUM(Anz_Note_4), SUM(Anz_Note_5), SUM(Anz_Note_6) FROM T_eval_Umfragen WHERE Lehrer = \"$User\";";
$rsNoten = mysql_query($sql);
printNotSpi(mysql_fetch_row($rsNoten));
mysql_freeresult($rsNoten);	
echo "\n\t\t</td>";
echo "\n\t\t<td><a href=\"details.php?Status=user\"><img src = \"image/i.gif\" border = \"0\" alt= \"Details\"></a></td>";
echo "\n\t</tr>";

echo "\n</table>";


//--------------------------------------------------------------------------

echo "\n\n<br><br>";

echo "\n<!---------------------------------------------------------->";
echo "\n<!------------- Uebersicht Einzelne-Umfragen --------------->";

echo "\n<table border = \"0\" width = \"100%\" cellpadding = \"5\" cellspacing = \"0\">";

echo "\n\t<tr>";
echo "\n\t\t<td width = \"100\"></td>";
echo "\n\t\t<td align = \"center\" colspan = \"7\"><b>Meine Umfragen im Einzelnen</b> [$LehrerDaten->Vorname $LehrerDaten->Name]</td>";
echo "\n\t\t<td width = \"100\"></td>";
echo "\n\t</tr>";
      
readSchuljahre($SJ_list);

foreach($SJ_list as $SJ)//Schleife ueber alle SJ
{
  $SJ_Beginn = strtotime($SJ['Beginn']);
  $SJ_Ende   = strtotime($SJ['Ende']);
  
  if(time() >= $SJ_Beginn)//Keine zukuenftigen SJ anzeigen
  {
    $sql = "SELECT * FROM T_eval_Umfragen WHERE Lehrer = \"$User\" AND Datum >= $SJ_Beginn AND Datum <= $SJ_Ende ORDER BY " . $user_settings[$SJ['SJahr']]['sort']  . " " . $user_settings[$SJ['SJahr']]['order'] . ";";
    $rs = mysql_query($sql);
    $AnzUmfragen = mysql_num_rows($rs);
    if($user_settings[$SJ['SJahr']]['view'] == "open" && $AnzUmfragen != 0)//Anzeige der Umfrage
    {
      $NumUmfrage = 0;
      //----------Schliessen-Leiste----------------
      echo "\n\t<tr>";
      echo "\n\t\t<td></td>";
      echo "\n\t\t<td bgcolor = \"#FFFFCA\" class = \"ra_bl x1101\" align = \"left\" colspan = \"7\" nowrap>";
      echo "<a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&view=close\"><img src = \"image/minus.gif\" border = \"0\" alt= \"Ansicht schließen\"></a>";
      echo "&nbsp;<b>Schuljahr: " . $SJ['SJahr'] . "</b>";
      echo "</td>";
      echo "\n\t\t<td></td>";
      echo "\n\t</tr>";
      
      //----------Tabellen-Ueberschriften----------
      echo "\n\t<tr>";
      echo "\n\t\t<td>&nbsp;</td>";
      echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1001\" align = \"center\" nowrap>";
      if($user_settings[$SJ['SJahr']]['sort'] == "Datum")
      {
        if($user_settings[$SJ['SJahr']]['order'] == "DESC")
          echo "<a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&sort=Datum&order=ASC\"><img src = \"image/spitze_up.gif\" border = \"0\" alt= \"Aufsteigend sortieren\"></a>";
        else
          echo "<a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&sort=Datum&order=DESC\"><img src = \"image/spitze_down.gif\" border = \"0\" alt= \"Absteigend sortieren\"></a>";
        echo "&nbsp;";
      }
      echo "<b><a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&sort=Datum\">Datum</a></b>";
      echo "</td>";
      echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1001\" align = \"center\" nowrap>";
      if($user_settings[$SJ['SJahr']]['sort'] == "Klasse")
      {
        if($user_settings[$SJ['SJahr']]['order'] == "DESC")
          echo "<a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&sort=Klasse&order=ASC\"><img src = \"image/spitze_up.gif\" border = \"0\" alt= \"Aufsteigend sortieren\"></a>";
        else
          echo "<a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&sort=Klasse&order=DESC\"><img src = \"image/spitze_down.gif\" border = \"0\" alt= \"Absteigend sortieren\"></a>";
        echo "&nbsp;";
      }  
      echo "<b><a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&sort=Klasse\">Klasse</a></b>";
      echo "</td>";
      echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1001\" align = \"center\" nowrap>";
      if($user_settings[$SJ['SJahr']]['sort'] == "Fach")
      {
        if($user_settings[$SJ['SJahr']]['order'] == "DESC")
          echo "<a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&sort=Fach&order=ASC\"><img src = \"image/spitze_up.gif\" border = \"0\" alt= \"Aufsteigend sortieren\"></a>";
        else
          echo "<a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&sort=Fach&order=DESC\"><img src = \"image/spitze_down.gif\" border = \"0\" alt= \"Absteigend sortieren\"></a>";
        echo "&nbsp;";
      }
      echo "<b><a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&sort=Fach\">Fach</a></b>";
      echo "</td>";
      echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1001\" align = \"center\"><b>Sü.</b></td>";
      echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1001\" align = \"center\"><b>&#216;</b></td>";
      echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1001\" align = \"center\"><b>Notenspiegel</b></td>";
      echo "\n\t\t<td class = \"oszRahmen_bg ra_bl x1101\" align = \"center\"><b>Passwort</b></td>";
      echo "\n\t\t<td>&nbsp;</td>";
      echo "\n\t</tr>";
      
      //----------Ausgabe der Umfragen----------
      while($row = mysql_fetch_array($rs, MYSQL_ASSOC))//Schleife ueber alle Umfragen des Lehrers (Fuer ein SJ)
      {
        $NumUmfrage++;
    
        //Mittelwert der Umfrage ermitteln
        $sql = "SELECT AnzStimmen FROM T_eval_Umfragen WHERE ID_Umfrage = $row[ID_Umfrage];";
        $rsErgebnis = mysql_query($sql);
        $rowErgebnis = mysql_fetch_row($rsErgebnis);
        $Stimmen = $rowErgebnis[0];
        $Mittelwert = round(ergUmfrage($row['ID_Umfrage']),2);
        mysql_free_result($rsErgebnis);
    
        $loeschlink = "<a href=\"index.php?Status=loescheUmfrage&Umfrage=" . md5($row['ID_Umfrage']) . "\"><img src = \"image/papierkorb.gif\" border = \"0\" alt= \"Umfrage löschen\"></a>";
    		
        if($NumUmfrage != $AnzUmfragen)
        {
          $rahmen1 = "x1001";
          $rahmen2 = "x1101";
          $rahmen3 = "x1001";
          $rahmen4 = "x1000";
    		}
        else//letzte Frage
        {
          $rahmen1 = "x1011";
          $rahmen2 = "x1111";
          $rahmen3 = "x1011";
          $rahmen4 = "x1010";          
        }
        echo "\n\t<tr>";
        echo "\n\t\t<td>&nbsp;</td>";
        echo "\n\t\t<td class = \"oszRahmen_bg ra_bl $rahmen1\" align = \"center\">" . date("d.m.Y",$row['Datum']) . "</td>";
        echo "\n\t\t<td class = \"oszRahmen_bg ra_bl $rahmen1\" align = \"center\"><nobr>$row[Klasse]</nobr></td>";
        echo "\n\t\t<td class = \"oszRahmen_bg ra_bl $rahmen1\" align = \"center\"><nobr>$row[Fach]</nobr></td>";
        echo "\n\t\t<td class = \"oszRahmen_bg ra_bl $rahmen1\" align = \"center\">$Stimmen</td>";
        echo "\n\t\t<td class = \"oszRahmen_bg ra_bl $rahmen1\" align = \"center\">" . number_format($Mittelwert, 2, ",", ".") . "</td>";
        echo "\n\t\t<td class = \"oszRahmen_bg ra_bl $rahmen3\" align = \"center\">";
    		
        //Ausgabe Notenspiegel
        $sql = "SELECT Anz_Note_1, Anz_Note_2, Anz_Note_3, Anz_Note_4, Anz_Note_5, Anz_Note_6 FROM T_eval_Umfragen WHERE ID_Umfrage = " . $row['ID_Umfrage'] . ";";
        $rsNoten = mysql_query($sql);
        printNotSpi(mysql_fetch_row($rsNoten));	
        mysql_freeresult($rsNoten);	
        
        echo "\n\t\t</td>";
        echo "\n\t\t<td class = \"oszRahmen_bg ra_bl $rahmen2\" align = \"center\"><div style = \"font-family:Courier\">$row[Passwort]</div></td>";
    	
        //Ausgabe Sperre/Details/Loeschen
        echo "\n\t\t<td nowrap>";
        if($row['Gesperrt'] == 1)
          echo "\n\t\t\t<a href=\"index.php?Status=setzeSperre&akt=0&Umfrage=" . md5($row['ID_Umfrage']) . "\"><img src = \"image/stop.gif\" border = \"0\" alt= \"Sperre ausschalten\"></a>";
        else
          echo "\n\t\t\t<a href=\"index.php?Status=setzeSperre&akt=1&Umfrage=" . md5($row['ID_Umfrage']) . "\"><img src = \"image/go.gif\" border = \"0\" alt= \"Sperre einschalten\"></a>";
        echo "\n\t\t\t<a href=\"details.php?Status=einzeln&Umfrage=" . md5($row['ID_Umfrage']) . "\"><img src = \"image/i.gif\" border = \"0\" alt= \"Details\"></a>";
        echo "\n\t\t\t$loeschlink";
        echo "\n\t\t</td>";
       echo "\n\t</tr>";
      }//Schleife alle Umfragen (User)
      mysql_free_result($rs);
    }//Umfragen anzeigen
    
    else//Oeffnen-Leiste anzeigen
    {
      echo "\n\t<tr>";
      echo "\n\t\t<td width = \"100\"></td>";
      echo "\n\t\t<td bgcolor = \"#FFFFCA\" class = \"ra_bl x1111\" align = \"left\" colspan = \"7\" nowrap>";
      echo "<a href=\"index.php?SJahr=" . $SJ['SJahr'] . "&view=open\"><img src = \"image/plus.gif\" border = \"0\" alt= \"Ansicht öffnen\"></a>";
      echo "&nbsp;<b>Schuljahr: " . $SJ['SJahr'] . "</b> [$AnzUmfragen Umfragen]";
      echo "</td>";
      echo "\n\t\t<td width = \"100\"></td>";
      echo "\n\t</tr>";
    }
    //Abstandszeile    
    echo "\n\t<tr height = \"5\">";
    echo "\n\t\t<td width = \"100\"></td>";
    echo "\n\t\t<td align = \"left\" colspan = \"7\"></td>";
    echo "\n\t\t<td></td>";
    echo "\n\t</tr>";    
    	      
  }//Keine zukuenftigen SJ anzeigen
}//Schleife ueber alle SJ
echo "\n</table>";

//--------------------------------------------------------------------------
//-------------------------------INHALT_ENDE--------------------------------
//--------------------------------------------------------------------------

echo ladeOszKopf_u();

echo ladeLink("../","<b>Interner&nbsp;Bereich</b>");
echo ladeLink("neuUmfrage.php","Neue&nbsp;Umfrage");
echo ladeLink("teilnehmer.php","Teilnehmer");
echo ladeLink("hilfe.php","Hilfe");

echo ladeOszFuss();
?>