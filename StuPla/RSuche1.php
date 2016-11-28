<?php
include_once("include/oszframe.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla_vars.inc.php");
include_once("include/stupla.inc.php");
include_once("include/raeume.inc.php");						
include_once("include/Vertretung.inc.php");						

$Tag = array("Montag","Dienstag","Mittwoch","Donnerstag","Freitag");

$Block    = (int)$_POST['Block'];
$WTag	  = (int)$_POST['WTag'];
$ID_Woche = (int)$_POST['ID_Woche'];

$freiRaum = array();

if(!$Block || !$ID_Woche || !$WTag)
  dieMsgLink("Eingabedaten unvollständig!","RSuche.php","Zurück zur Auswahl");

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
if(!$db)
  dieMsg("Keine Verbindung zur Datenbank möglich!");
mysql_select_db($DBname,$db);
	
//KW einlesen
$Woche = getKW($ID_Woche);
$Montag = getMontag($ID_Woche);
if($Woche == -1 || $Montag == -1)
  dieMsg("Zur Zeit sind keine Daten abrufbar!");  

$dieserTag = $Montag + ($WTag - 1) * 24 * 60 * 60;

//Timestamp der Onlineversion einlesen
$OnlineVersion = getAktuelleVersion($Montag);
if($OnlineVersion == -1)
  dieMsg("Zur Zeit sind keine Daten abrufbar!");

//Gueltigkeits-Timestamp der aktuellsten Version aus DB holen    
$GueltigAb = getGueltigAbVersion($OnlineVersion);
if($GueltigAb == -1)
  dieMsg("Zur Zeit sind keine Daten abrufbar!");  

//Auswertung der Turnusse dieser KW
if(getTurnusListe($ID_Woche, $TurnusListe))
{
  //Erstellen des SQL-Abfragestrings
  foreach($TurnusListe as $Turnus)
    $sqlTurnus[] = "\"$Turnus\"";
  $sqlTurnus[] = "\"jede Woche\"";
  $sqlTurnus = "Turnus = " . implode(" OR Turnus = ", $sqlTurnus);
}
else
  $sqlTurnus = "Turnus = \"jede Woche\"";

//Raeume abfragen
$sql  = "SELECT DISTINCT Raum FROM T_StuPla ORDER BY Raum;";
$rsRaum = mysql_query($sql, $db);
			
while($rowRaum = mysql_fetch_row($rsRaum))//Alle Raeume
{
  if(!istVerhindert($dieserTag, $rowRaum[0], $Block, 'Raum') && !istGesperrt($dieserTag, $rowRaum[0], $Block) && darfReservieren($rowRaum[0]))
  {
    $aFach   = array();
    $aKlasse = array();
    $aRaum   = array();
    $aLehrer = array();

    //Raumbelegung abfragen
    $sql  = "SELECT DISTINCT Lehrer, Fach, Klasse, Turnus FROM T_StuPla";
    $sql .= " WHERE Stunde = $Block";
    $sql .= " AND Wochentag = $WTag";
    $sql .= " AND Raum = \"$rowRaum[0]\"";
    $sql .= " AND Version = $OnlineVersion";
    $sql .= " AND ($sqlTurnus);";
    
    $rsBelegung = mysql_query($sql, $db);

    if(mysql_num_rows($rsBelegung) == 0)//Raum lt. T_StuPla frei --> auf Zusatzbelegung pruefen
    {
      $aZB = array(); //fuer Zusatzbloecke
      $aZB = pruefeZusatzBlock($dieserTag, $Block, "Raum", $rowRaum[0], $aID_V);
      foreach($aZB as $ZB)
      {
        erw_feld($ZB["Lehrer"], $aLehrer);
        erw_feld($ZB["Fach"], $aFach);
        erw_feld($ZB["Klasse"], $aKlasse);
      }//foreach   
    }
    else
    {
      while($rowBelegung = mysql_fetch_row($rsBelegung))//Ueber alle Belegungen eines Raumes
      {
        $aVB = array(); //fuer Vertretungsbloecke
        $aID_V = array();  
      
        //Speicherung Originaldatensatz in Feld aVB
        $aVB["Lehrer"]       = $rowBelegung[0];
        $aVB["Fach"]         = $rowBelegung[1];            
        $aVB["Klasse"]       = $rowBelegung[2];
        $aVB["Turnus"]       = $rowBelegung[3];
        $aVB["Raum"]         = $rowRaum[0];
        $aVB["Stunde"]       = $Block;   
        $aVB["Vertretungen"] = "";   	
 
        //Ergaenzen von Feld aVB um Vertretungsplan-Infos / Auslesen der Vertretungs-ID's  
        $aVB = pruefeVertretung($aVB, $dieserTag, "Raum", $rowRaum[0]);
	if(is_array($aVB["Vertretungen"]))
          $aID_V = array_merge($aID_V, $aVB["Vertretungen"]);   

        //Erweitern der Felder
        erw_feld($aVB["Fach"], $aFach);
        erw_feld($aVB["Lehrer"], $aLehrer);
        erw_feld($aVB["Klasse"], $aKlasse); 
      
        //Achtung: Wenn jetzt kein Eintrag mehr vorhanden ist --> Ausfall lt. Vertretungsplan (auf Zusatzblock pruefen)
        if(istLeer($aLehrer) && istLeer($aFach) && istLeer($aKlasse))      
        {
          $aZB = pruefeZusatzBlock($dieserTag, $Block, "Raum", $rowRaum[0], $aID_V);
          foreach($aZB as $ZB)
          {
            erw_feld($ZB["Lehrer"], $aLehrer);
            erw_feld($ZB["Fach"], $aFach);
            erw_feld($ZB["Klasse"], $aKlasse);
          }//foreach         
        }//if
      }//while -Schleife ueber alle Raumbelegungen
    }//else
    mysql_free_result($rsBelegung);
  
    //Wenn hier kein Eintrag vorhanden, ist dieser Raum frei
    if(istLeer($aLehrer) && istLeer($aFach) && istLeer($aKlasse))
      $freiRaum[] = $rowRaum[0];
  }//if (Sperrung, Verhinderung, ...) 
}//while -Schleife ueber alle Raeume

mysql_free_result($rsRaum);

echo ladeOszKopf_o("OSZ IMT Raumsuche", "keine");
	
echo "\n\n<p style=\"margin-top:0.2cm; margin-bottom:0.2cm; text-align:center\"><span class = \"ueberschrift\"><br>Freie Räume</span></p>";
echo "\n\n<p style=\"margin-top:0.1cm; margin-bottom:0.1cm; text-align:center\"><span class = \"smallmessage_gr\">$Woche. KW&nbsp;/&nbsp;" . $Tag[$WTag-1] . "&nbsp;" . date("d.m.Y",($Montag + ($WTag-1) * 24 * 60 * 60)) . "&nbsp;/&nbsp;$Block. Block</span></p>";

//Erstellen der Tabelle
echo "\n<table border = \"0\" width = \"100%\" height = \"60%\" cellpadding = \"0\">";

echo "\n\t<colgroup>";
echo "\n\t\t<col width=\"25%\">";
echo "\n\t\t<col width=\"50%\">";
echo "\n\t\t<col width=\"25%\">";
echo "\n\t</colgroup>";

echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td align = \"center\" valign = \"bottom\"><span class = \"p_small\"><span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$GueltigAb) . "</b></span></span></td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";

echo "\n\t\t<td class = \"zelle_inhalt_bg ra_bl x1111\" id=\"form_ausw_inhalt\" align = \"center\">";
echo "\n\t\t\t<table border=\"0\">";

foreach($freiRaum as $Raum)//Ueber alle freien Raeume
{	
  echo "\n\t\t\t\t<tr>";
  echo"\n\t\t\t\t\t<td>";
  echo "<a class = \"linktable\" href=\"RPlan1.php?Raum=$Raum&ID_Woche=$ID_Woche\" target=\"_blank\">$Raum</a>";
  echo"</td>";  
  // Raumbezeichnung und Kapazität anzeigen
  $beschreibung = RaumBeschreibungLang($Raum);  
  if ( $beschreibung != '' )
  {
    echo "\n\t\t\t\t\t<td>";
    echo"<span class=\"smallmessage_gr\">" . $beschreibung."</span></td>";
  }
  echo "\n\t\t\t\t</tr>";
}

echo "\n\t\t\t</table>";
echo "\n\t\t</td>";
echo "\n\t</tr>";
echo "\n</table>";
mysql_close($db);

echo ladeOszKopf_u();

echo ladeLink("http://www.oszimt.de", "<b>Home</b>");
echo ladeLink("../index.php", "Interner Bereich"); 
echo ladeLink("RSuche.php", "Raumsuche");  

echo ladeOszFuss();
?>
