<?php
include_once("include/oszframe.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla_vars.inc.php");
include_once("include/stupla.inc.php");
include_once("include/Vertretung.inc.php");

//Initialisierungen
for($i = 0; $i <= 4; $i++)
  $freierTag[$i] = false; 
$Ferien   = false;
$Tag      = array("Montag","Dienstag","Mittwoch","Donnerstag","Freitag");
$BZeit    = array("8:00-9:30","9:45-11:15","11:45-13:15","13:30-15:00","15:15-16:45","17:00-18:30");
$aVB = array();   //fuer Vertretungsbloecke
$aID_V = array(); //fuer Vertretungs-ID's	    

//Abbpruefen der Uebergabeargumente
if(!isset($_REQUEST['Raum']) || (!isset($_REQUEST['ID_Woche']) && !isset($_REQUEST['Uebersicht'])))
  dieMsgLink("Eingabedaten unvollständig!","RPlan.php","Zurück zur Auswahl");
$Raum = $_REQUEST['Raum'];
if(isset($_REQUEST['ID_Woche']))
  $ID_Woche = (int)$_REQUEST['ID_Woche'];
if(isset($_REQUEST['Druck']))
  $Druck = $_REQUEST['Druck'];
else
  $Druck = false;
if(isset($_REQUEST['Uebersicht']))
  $Uebersicht    = $_REQUEST['Uebersicht'];//true wenn die Raumbelegungsuebersicht gewaehlt wurde
else
  $Uebersicht = false;
if($Uebersicht)
{
  if(!isset($_REQUEST['Version']))
    dieMsgLink("Eingabedaten unvollständig!","RPlan.php","Zurück zur Auswahl");
  $OnlineVersion = $_REQUEST['Version'];
}

if(pruefeZeichen($Raum))
  dieMsgLink("Falsche Parameter!","RPlan.php","Raumübersicht");

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
if(!$db)
  dieMsg("Keine Verbindung zur Datenbank möglich!");
mysql_select_db($DBname,$db);

//css-Einstellungen
if($Druck)
{
  $rahmen_farbe      = "ra_sw";
  $zelle_beschr_bg   = "zelle_beschr_bg_dr";
  $zelle_inhalt_bg   = "zelle_inhalt_bg_dr";
  $zelle_frei_tag_bg = "zelle_frei_tag_bg_dr";
  $link_table        = "linktable_dr";
}
else
{
  $rahmen_farbe      = "ra_bl";
  $zelle_beschr_bg   = "zelle_beschr_bg";
  $zelle_inhalt_bg   = "zelle_inhalt_bg";
  $zelle_frei_tag_bg = "zelle_frei_tag_bg";
  $link_table        = "linktable";
}	

echo ladeOszKopf_o("OSZ IMT Raumpläne", "keine", $Druck);

echo "\n\n<p align = \"center\">";
echo "<span class = \"ueberschrift\"><br>Belegungsplan von Raum $Raum</span>";
if($Uebersicht)
  echo "</br>";  
else
{
  //KW einlesen
  $Woche = getKW($ID_Woche);
  $Montag = getMontag($ID_Woche);
  if($Woche == -1 || $Montag == -1)
    dieMsg("Zur Zeit sind keine Daten abrufbar!");   

  $OnlineVersion = getAktuelleVersion($Montag);
  if($OnlineVersion == -1)
    dieMsg("Zur Zeit sind keine Daten abrufbar!");

  //ID der naechsten KW ermitteln
  $ID_naechsteWoche = getID_Anschluss_KW($ID_Woche, '+');

  //ID der letzten KW ermitteln
  $ID_letzteWoche = getID_Anschluss_KW($ID_Woche, '-');

  //Freie Unterrichtstage in dieser KW ermitteln
  for($Wochentag = 0; $Wochentag <= 4; $Wochentag++)
  {
    $dieserTag = $Montag + $Wochentag * 24 * 60 * 60;//Berechnung des Unix-Timestamp
    if(istFrei($dieserTag))
    {
      $freierTag[$Wochentag] = true;
      $Kommentar[$Wochentag] = getFerienKommentar($dieserTag);
    }    
  }
  //Auswertung der Turnusse dieser KW
  if(getTurnusListe($ID_Woche, $TurnusListe))
  {
    $AusgabeString = " / " . implode(", ", $TurnusListe);
    //Erstellen des SQL-Abfragestrings
    foreach($TurnusListe as $Turnus)
      $sqlTurnus[] = "\"$Turnus\"";
    $sqlTurnus[] = "\"jede Woche\"";
    $sqlTurnus = "Turnus = " . implode(" OR Turnus = ", $sqlTurnus);
  }
  else
  {
    $sqlTurnus = "Turnus = \"jede Woche\"";
    $AusgabeString = "";
  }
  echo "<span class = \"smallmessage_gr\">&nbsp;&nbsp;($Woche. KW $AusgabeString)</span></br>";
}//if(!$Uebersicht)

/*-----------------------------------------------------------*/
/*----- Raumbezeichnung und Kapazität anzeigen --------------*/
if(!$Druck)
{  
  include("include/raeume.inc.php");    
  $beschreibung = RaumbeschreibungLang($Raum, true);
  if ( $beschreibung != '' )
  {
    echo '<span class = "p_small smallmessage_gr">'.$beschreibung.'</span>';
  }
}
echo "</p>"; 
/*------------- Ende Bezeichnung und Kap. -------------------*/
/*-----------------------------------------------------------*/
  
//Gueltigkeits-Timestamp der aktuellen Version aus DB holen    
$GueltigAb = getGueltigAbVersion($OnlineVersion);
if($GueltigAb == -1)
  dieMsg("Zur Zeit sind keine Daten abrufbar!"); 	

/*-----------------------------------------------------------*/
/*------------------ Ausgabe Stundenplan --------------------*/
echo "\n<table border = \"0\" width = \"100%\" height = \"1%\" cellpadding = \"0\" cellspacing = \"0\">";

echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td>&nbsp;</td>";
if(!$Druck && isset($ID_letzteWoche) && $ID_letzteWoche != -1)
  echo "\n\t\t<td><a href=\"RPlan1.php?Raum=$Raum&ID_Woche=$ID_letzteWoche&KW=true\"><img src = \"http://img.oszimt.de/nav/pfeili_blau.gif\" alt = \"vorherige Woche\" border = \"0\"></a></td>";
else
  echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td colspan = \"3\" align = \"center\"><span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$GueltigAb) . "</b></span></td>";
if(!$Druck && isset($ID_naechsteWoche) && $ID_naechsteWoche != -1)
  echo "\n\t\t<td align = \"right\"><a href=\"RPlan1.php?Raum=$Raum&ID_Woche=$ID_naechsteWoche&KW=true\"><img src = \"http://img.oszimt.de/nav/pfeire_blau.gif\" alt = \"nächste Woche\" border = \"0\"></a></td>";
else
  echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

for($Block = 0; $Block <= 6; $Block++)
{
  if($Block == 0)
    echo "\n\t<tr height = \"1%\">";
  else
    echo "\n\t<tr>";
  echo "\n\t\t<td>&nbsp;</td>";
  for($Wochentag = 0; $Wochentag <= 5; $Wochentag++)
  {
    if(!$Uebersicht)
      $dieserTag = $Montag + ($Wochentag - 1) * 24 * 60 * 60;

    //Rahmeneinstellungen
    $rahmen = LayoutRahmen($Block, 6, $Wochentag, 5);
      
    if($Wochentag == 0)//Erste Spalte->Zeilenbeschriftung
    {
      if($Block == 0)//Zelle oben links ist leer
        echo "\n\t\t<td>&nbsp;</td>";
      else//Zeilenbeschriftung ab zweiter Spalte (Blockzeiten)
      {
        echo "\n\t\t<td nowrap align = \"center\" class = \"$zelle_beschr_bg $rahmen $rahmen_farbe\" id=\"form_zeile_beschr\">";
        echo "<span class = \"tab_beschr\">&nbsp;$Block. Block&nbsp;</span><br><span class = \"smallmessage_kl\">" . $BZeit[$Block-1] . "</span>";
        echo "</td>";
      }//else
    }//if
    else//Zweite Spalte bis Ende
    {
      if($Block == 0)//Erste Zeile->Spaltenbeschriftung (Namen der Tage)
      {
        echo "\n\t\t<td align = \"center\" class = \"$zelle_beschr_bg $rahmen $rahmen_farbe\" id=\"form_spalte_beschr\">";
        echo "<span class = \"tab_beschr\">" . $Tag[$Wochentag-1] . "</span>";
        if(!$Uebersicht)//Ausgabe Tagesdatum
          echo "<br><span class = \"smallmessage_kl\">" . date("d.m.y", $dieserTag) . "</span>";
        echo "</td>";
      }//if
      else//Ausgabe der Unterrichtsbloecke
      {
        if(!$Uebersicht && $Block == 1 && $freierTag[$Wochentag-1])//Ausgabe der Ferienspalte
        {
          $rahmenFerien = "x1011";
          if($Wochentag == 5)
            $rahmenFerien = "x1111";
          echo "\n\t\t<td class = \"$zelle_frei_tag_bg $rahmenFerien $rahmen_farbe\"rowspan = \"6\" nowrap align = \"center\"><span class = \"smallmessage_gr\">" . $Kommentar[$Wochentag-1] . "</span></td>";
        }
        else if(!$freierTag[$Wochentag-1])
        {
          echo "\n\t\t<td nowrap class = \"$zelle_inhalt_bg $rahmen $rahmen_farbe\" id = \"form_inhalt\" align = \"center\"><span class = \"smallmessage_gr\">";
					
          //StuPla abfragen
          $sql  = "SELECT DISTINCT Lehrer, Fach, Klasse, Turnus FROM T_StuPla";
          $sql .= " WHERE Stunde = $Block";
          $sql .= " AND Wochentag = $Wochentag";
          $sql .= " AND Raum = \"$Raum\"";
          $sql .= " AND Version = $OnlineVersion";
          if(!$Uebersicht)
            $sql .= " AND ($sqlTurnus);";
          else
            $sql .= " ORDER BY Turnus;";
          $rs = mysql_query($sql, $db);
	 
          $aFach   = array();
          $aKlasse = array();
          $aRaum   = array();
          $aLehrer = array();
  	    			
          $Bemerkung = false;	  	   
			
          while($row = mysql_fetch_row($rs))//Fuer den Fall das Mehrfacheintraege vorhanden sind
          {
            //Arrays leeren
            $aVB = array();
            $aID_V = array();	    

            //Speicherung Originaldatensatz in Feld aVB
            $aVB["Lehrer"] = $row[0];
            $aVB["Fach"]   = $row[1];            
            $aVB["Klasse"] = $row[2];
            $aVB["Turnus"] = $row[3];
            $aVB["Stunde"] = $Block;	
            $aVB["Raum"]   = $Raum;
            $aVB["Bemerkung"] = "";
	    $aVB["Vertretungen"] = "";		    

	    if(!$Uebersicht) //Vertretungsinfo nur bei KW-Anzeige
	    {
              //Ergaenzen von Feld aVB um Vertretungsplan-Infos / Auslesen der Vertretungs-ID's  
              $aVB = pruefeVertretung($aVB, $dieserTag, "Raum", $Raum);
	      if(is_array($aVB["Vertretungen"]))
                $aID_V = array_merge($aID_V, $aVB["Vertretungen"]);   
              if(!$Bemerkung && !is_empty($aVB["Bemerkung"]))
              {
                $txtBemerkung = $aVB["Bemerkung"];
                $Bemerkung = true;
              }     
	    } 
	     
            //Aufbauen der Felder fuer die HTML-Ausgabe
            erw_feld($aVB["Fach"], $aFach);
            erw_feld($aVB["Lehrer"], $aLehrer);
            erw_feld($aVB["Klasse"], $aKlasse);

            //Wenn Klasse vorhanden, dann um Turnus ergaenzen
            if(!is_empty($aVB["Klasse"]) && strcmp($aVB["Klasse"], "*") != 0)
              $aKlasse[count($aKlasse) - 1]["Turnus"] = $aVB["Turnus"];  

          }//while -Schleife ueber Mehrfacheintraege fuer diese Stunde-
          mysql_free_result($rs);

          if(!$Uebersicht) //Vertretungsinfo nur bei KW-Anzeige
	  {
            $aZB = array(); //fuer Zusatzbloecke
            $aZB = pruefeZusatzBlock($dieserTag, $Block, "Raum", $Raum, $aID_V);
            foreach($aZB as $ZB)
            {
	      erw_feld($ZB["Lehrer"], $aLehrer);
              erw_feld($ZB["Fach"], $aFach);
              erw_feld($ZB["Klasse"], $aKlasse);
	      if(!$Bemerkung)
	      {
                $txtBemerkung = $ZB["Bemerkung"];
                $Bemerkung = true;	        
	      }            
            }//foreach			  	
	  }

	  //Anzeige nur wenn Eintraege vorhanden        	        
          if(!istLeer($aLehrer) || !istLeer($aFach) || !istLeer($aKlasse))
          {
            if($Bemerkung)
            {
	      $schrift = set_schrift($Druck, 1, 1);
              echo "<span class = \"$schrift\">$txtBemerkung</span><br>";
	    }	  
            //Fach
            $zaehler = 0;
            foreach($aFach as $Fach)       
            {
              if($zaehler > 0)
		echo " / ";
              $schrift = set_schrift($Druck, $Fach["Neu"]);
              echo "<b><span class = \"$schrift\">" . $Fach["Name"] . "</span></b>";      
              $zaehler++;
            }
            if($zaehler != 0)
              echo "<br>";   
	      
            //Klasse
            $zaehler = 0;
            foreach($aKlasse as $Klasse)
            {
              if($zaehler > 0)
	      {
	        if($Uebersicht)
		  echo "<br>";
                else
		  echo " / ";
	      }
              $schrift = set_schrift($Druck, $Klasse["Neu"]);                
              echo "<span class = \"$schrift\"><a class = \"$link_table\" href=\"http://www.oszimt.de/0-schule/stundenplan/KPlan1.php?Klasse=" . urlencode($Klasse["Name"]) . "&Version=$OnlineVersion\" target=\"_blank\">" . $Klasse["Name"] . "</a></span>";
              if(isset($Klasse["Turnus"]) && $Klasse["Turnus"] != "jede Woche")
                echo " (" . $Klasse["Turnus"] . ")";
              $zaehler++;
            }
	    if($zaehler != 0)
              echo "<br>";
	    
            //Lehrer
            $zaehler = 0;
            foreach($aLehrer as $Lehrperson)
            {
              if($zaehler > 0)
	        echo " / ";
              $schrift = set_schrift($Druck, $Lehrperson["Neu"]);   
	      if(!$Uebersicht)                        
                echo "<span class = \"$schrift\"><a class = \"$link_table\" href=\"LPlan1.php?Lehrer=" . urlencode($Lehrperson["Name"]) . "&ID_Woche=$ID_Woche&KW=true\" target=\"_blank\">" . $Lehrperson["Name"] . "</a></span>";
              else
	        echo "<span class = \"$schrift\">" . $Lehrperson["Name"] . "</span>";		
              $zaehler++;
            }	    	   	      	  
	  }//Eintrag vorhanden
	  
          else//Keine Eintraege --> Raum reservierbar
	  {
	    if(!$Uebersicht && !$Druck && $dieserTag >= mktime(0,0,0,date("m,d,Y",time())) && !istVerhindert($dieserTag, $Raum, $Block, 'Raum') && !istGesperrt($dieserTag, $Raum, $Block) && darfReservieren($Raum))//Button fuer Raumreservierung anzeigen?
              echo "<a href=\"RaumReservierung.php?Raum=$Raum&Tag=$dieserTag&Stunde=$Block&ID_Woche=$ID_Woche\" target=\"_blank\"><img src=\"R1.gif\" border = \"0\"></a>";
            else//(Keine Reservierungsinfos anzeigen)
              echo "&nbsp;";
	  }
          echo "</span></td>";
        }//else if -kein freier Tag-
      }//else -Ausgabe der Unterrichtsbloecke-
    }//else -Zweite Spalte bis Ende-
  }//for -Wochentag-
  echo "\n\t\t<td>&nbsp;</td>";		
  echo "\n\t</tr>";
}//for -Block-
	
echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td align = \"right\" valign = \"top\" colspan = \"6\"><span class = \"smallmessage_kl\">Version: " . date("d.m.Y",$OnlineVersion) . "</span></td>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

echo "\n</table>\n";

mysql_close($db);

echo ladeOszKopf_u($Druck);
if(!$Druck)
{
  if($Uebersicht)
    $DruckLink = "RPlan1.php?Raum=$Raum&Version=$OnlineVersion&Uebersicht=true&Druck=true";
  else
    $DruckLink = "RPlan1.php?Raum=$Raum&ID_Woche=$ID_Woche&Druck=true";
  $RPlanBelegLink = "RPlan1.php?Raum=$Raum&Version=$OnlineVersion&Uebersicht=true";

  echo ladeLink("http://www.oszimt.de", "<b>Home</b>");
  echo ladeLink("../index.php", "Interner Bereich");    
  echo ladeLink("RPlan.php", "Raumauswahl");    
  echo ladeLink("$DruckLink", "Druckversion", "target = \"_blank\"");     
  if(!$Uebersicht)  
  {
    echo ladeLink("$RPlanBelegLink", "Übersicht", "target = \"_blank\"");                
    echo ladeLink("resHilfe.html", "Hilfe", "target = \"_blank\"");            
  }          
}
echo ladeOszFuss($Druck);
?>
