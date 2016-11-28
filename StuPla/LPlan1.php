<?php

include_once("include/helper.inc.php");
include_once("include/stupla_vars.inc.php");
include_once("include/stupla.inc.php");
include_once("include/oszframe.inc.php");
include_once("include/Vertretung.inc.php");

//Initialisierungen
$Tag = array("Montag","Dienstag","Mittwoch","Donnerstag","Freitag");
$BZeit = array("8:00-9:30","9:45-11:15","11:45-13:15","13:30-15:00","15:15-16:45","17:00-18:30");
$linkVersion = -1;
$Montag = "";

if(isset($_REQUEST['ID_Woche']))
  $ID_Woche = (int)$_REQUEST['ID_Woche'];
else
  $ID_Woche = false;

if(isset($_REQUEST['selTurnus']))  
  $selTurnus = $_REQUEST['selTurnus'];//Liste der gewaehlten Turnusse
else
  $selTurnus = false;

$Lehrer = $_REQUEST['Lehrer'];

if(isset($_REQUEST['KW']))
  $KW = $_REQUEST['KW']; //true wenn KW-Ansicht
else
  $KW = false;

if(isset($_REQUEST['Druck']))
  $Druck = $_REQUEST['Druck']; //true -> Druckansicht
else
  $Druck = false;
  
//Parameter ueberpruefen
if(!$KW && !$selTurnus)//Weder KW noch Turnus gewaehlt
  dieMsgLink("Eingabedaten unvollständig!","LPlan.php","Zurück zur Auswahl");

if(!$Lehrer || (!$ID_Woche && $KW))//Kein Lehrer oder ID_Woche fehlt und KW gewaehlt
  dieMsgLink("Eingabedaten unvollständig!","LPlan.php","Zurück zur Auswahl");
if($selTurnus)
  foreach($selTurnus as $Wert)
    if(pruefeZeichen($Wert))
      dieMsgLink("Falsche Parameter!","LPlan.php","Lehrerübersicht");
if(pruefeZeichen($Lehrer))
  dieMsgLink("Falsche Parameter!","LPlan.php","Lehrerübersicht");
if(pruefeZeichen($KW))
  dieMsgLink("Falsche Parameter!","LPlan.php","Lehrerübersicht");

//Initialisierungen
for($i = 0; $i <= 4; $i++)
  $freierTag[$i] = false;

//Datenbankverbindung aufbauen
$db = @mysql_connect($DBhost,$DBuser,$DBpasswd);
if(!$db)
  dieMsg("Keine Verbindung zur Datenbank möglich!");
mysql_select_db($DBname,$db);

//Bei Aufruf ueber Link mit ID_Woche=-1 zeige aktuelle KW
if($ID_Woche == -1)
  $ID_Woche = (int)getID_Woche();

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

echo ladeOszKopf_o("OSZ IMT Unterrichtspläne der Lehrer","keine", $Druck);

if($KW)//Darstellung nach KW
{
  //KW einlesen
  $Woche = getKW($ID_Woche);
  $Montag = getMontag($ID_Woche);
  if($Woche == -1 || $Montag == -1)
    dieMsg("Zur Zeit sind keine Daten abrufbar!");   
	
  //Timestamp Online-Version ermitteln
  $OnlineVersion = getAktuelleVersion($Montag);
  if($OnlineVersion == -1)
    dieMsg("Zur Zeit sind keine Daten abrufbar!");

  //Gueltigkeits-Timestamp der aktuellsten Version aus DB holen    
  $OnlineGueltigAb = getGueltigAbVersion($OnlineVersion);
  if($OnlineGueltigAb == -1)
    dieMsg("Zur Zeit sind keine Daten abrufbar!");

  //Lehrername einlesen
  $sql = "SELECT DISTINCT Name, Vorname FROM T_StuPla WHERE Lehrer = \"$Lehrer\" AND Version = $OnlineVersion;";
  $rs = mysql_query($sql,$db);
  $row = mysql_fetch_row($rs);
  $Name = $row[0];
  $Vorname = $row[1];

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
 
  echo "\n\n<p align = \"center\">";
  echo "<span class = \"ueberschrift\"><br>Unterrichtseinsatz von $Vorname $Name</span><span class = \"smallmessage_gr\">&nbsp;&nbsp;($Woche. KW $AusgabeString)</span></p>";

}//if($KW)

else//Darstellung nach Turnussen
{
  $OnlineVersion = getAktuelleVersionWE();

  if($OnlineVersion == -1)
    dieMsg("Zur Zeit sind keine Daten abrufbar!");

  //Gueltigkeits-Timestamp der aktuellsten Version aus DB holen
  $OnlineGueltigAb = getGueltigAbVersion($OnlineVersion);
  if($OnlineGueltigAb == -1)
    dieMsg("Zur Zeit sind keine Daten abrufbar!");

  //Lehrername einlesen
  $sql = "SELECT DISTINCT Name, Vorname FROM T_StuPla WHERE Lehrer = \"$Lehrer\" AND Version = $OnlineVersion;";
  $rs = mysql_query($sql,$db);
  $row = mysql_fetch_row($rs);
  $Name = $row[0];
  $Vorname= $row[1];

  //Abfragestring Turnus erstellen
  foreach($selTurnus as $Turnus)
    $sqlTurnus[] = "\"$Turnus\"";
  $sqlTurnus = "Turnus = " . implode(" OR Turnus = ", $sqlTurnus);
  $linkTurnus = "selTurnus[]=" . implode("&selTurnus[]=", $selTurnus);  
  $AusgabeString = implode(", ", $selTurnus);  

  echo "\n\n<p align = \"center\"><span class = \"ueberschrift\"><br>Unterrichtseinsatz von $Vorname $Name</span><span class = \"smallmessage_gr\">&nbsp;&nbsp;<b>(Turnus: $AusgabeString)</b></span></p>";
}//Darstellung nach Turnussen

/* ***NEU 12.09.05: Aufsichten */
$Aufsichten = array();
if($KW) 
  $AufsichtAb = getGueltigAb($Montag, "T_Aufsichten");
else
  $AufsichtAb = getGueltigAb(-1, "T_Aufsichten");
$sql = "SELECT * FROM T_Aufsichten INNER JOIN T_Aufsichtsorte ON F_Ort_id=Ort_id WHERE BINARY Lehrer='$Lehrer' AND GueltigAb=$AufsichtAb";
$query = mysql_query($sql, $db);
while($row = mysql_fetch_array($query))
  $Aufsichten[$row["VorStunde"]][$row["Wochentag"]] = $row["Ort"];
mysql_free_result($query);
$AnzAufsichten = 0;
for ( $i = 1; $i < 5; $i++) 
  if ( isset($Aufsichten[$i]) ) $AnzAufsichten++;
// Ende Aufsichten einlesen

//Max. Anzahl der Unterrichtsbloecke ermitteln
$sql = "SELECT MAX(Stunde) FROM T_StuPla";
$sql .= " WHERE Lehrer = \"$Lehrer\"";
$sql .= " AND Version = $OnlineVersion";
$sql .= " AND ($sqlTurnus);";

$rs = mysql_query($sql, $db);
$row = mysql_fetch_row($rs);
if($row[0] > 4)
  $MaxStunde = $row[0];
else
  $MaxStunde = 4;
if($KW)
{
  $MaxStundeVertretung = holeMaxBlockVertretungenKW($ID_Woche, "Lehrer", $Lehrer);
  if($MaxStundeVertretung > $MaxStunde)
    $MaxStunde = $MaxStundeVertretung;
}

/*-----------------------------------------------------------*/
/*-------------- Ausgabe des Stundenplans--------------------*/
echo "\n<table border = \"0\" width = \"100%\" height = \"1%\" cellpadding = \"0\" cellspacing = \"0\">";

echo "\n\t<tr>";
echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t\t<td>&nbsp;</td>";

if(!$Druck && isset($ID_letzteWoche) && $ID_letzteWoche != -1)
  echo "\n\t\t<td><a href=\"LPlan1.php?Lehrer=$Lehrer&ID_Woche=$ID_letzteWoche&KW=true\"><img src = \"http://img.oszimt.de/nav/pfeili_blau.gif\" alt = \"vorherige Woche\" border = \"0\"></a></td>";
else
  echo "\n\t\t<td>&nbsp;</td>";

if($Druck || $linkVersion == -1  || $KW) //Keine weitere Version vorhanden oder Ansicht nach KW
  echo "\n\t\t<td colspan = \"3\" align = \"center\"><span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$OnlineGueltigAb) . "</b></span></td>";
else //Es gibt eine weitere Version
  echo "\n\t\t<td colspan = \"3\" align = \"center\"><span class = \"smallmessage_gr\"><b>Gültig ab: " . date("d.m.Y",$OnlineGueltigAb) . "</b></span>&nbsp;&nbsp;&nbsp;&nbsp;<a class = \"smalllink\" href = \"LPlan1.php?Lehrer=$Lehrer&Version=$linkVersion&$linkTurnus\">[Gültig ab: " . date("d.m.Y",$naechsteGueltigAb) . "]</a></td>";

if(!$Druck && isset($ID_naechsteWoche) && $ID_naechsteWoche != -1)
  echo "\n\t\t<td align = \"right\"><a href=\"LPlan1.php?Lehrer=$Lehrer&ID_Woche=$ID_naechsteWoche&KW=true\"><img src = \"http://img.oszimt.de/nav/pfeire_blau.gif\" alt = \"nächste Woche\" border = \"0\"></a></td>";
else
  echo "\n\t\t<td>&nbsp;</td>";

echo "\n\t\t<td>&nbsp;</td>";
echo "\n\t</tr>";

$FruehUndFerien = false;

for($Block = 0; $Block <= $MaxStunde; $Block++)
{
  /*-----------------------------------------------------------*/
  /*------------------ Aufsichten -----------------------------*/
  if ( isset($Aufsichten[$Block]) )
  {
    $rahmen = "x1001";
    echo "\n\t<tr>";
    echo "\n\t\t<td>&nbsp;</td>"; // Leere Zelle links
    echo "\n\t\t<td class = \"$zelle_beschr_bg $rahmen $rahmen_farbe\" align = \"center\">";
    echo '<span class="smallmessage_kl">Aufsicht</span></td>';
    for ( $Wochentag = 1; $Wochentag <= 5; $Wochentag++ )
    {
      if ( ! $freierTag[$Wochentag-1] )
      {
        if($Wochentag == 5)
          $rahmen = "x1101";
        echo "\n\t\t<td class = \"$zelle_inhalt_bg $rahmen $rahmen_farbe\" align = \"center\">";
        if ( isset($Aufsichten[$Block][$Wochentag]) )
        {
          echo '<span class="smallmessage_kl">';
          echo $Aufsichten[$Block][$Wochentag];
          echo '</span>';
        }
        else
          echo '&nbsp;';
        echo '</td>';
      }
      else if($Block == 1)//Wenn Fruehaufsicht (vor erstem Block) und Ferien dann beginnt die Ferienspalte hier!
      {
        $FruehUndFerien = true;
        $rahmenFerien = "x1011";
        if($Wochentag == 5)
          $rahmenFerien = "x1111";
        echo "\n\t\t<td class = \"$zelle_frei_tag_bg $rahmenFerien $rahmen_farbe\"rowspan = \"".($MaxStunde+$AnzAufsichten)."\" nowrap align = \"center\"><span class = \"smallmessage_gr\">" . $Kommentar[$Wochentag-1] . "</span></td>";        
      }
    }
    echo "\n\t\t<td>&nbsp;</td>";  // Spalte rechts
    echo "\n\t</tr>\n";
  }
  /*---------------- Ende Aufsichten --------------------------*/
  /*-----------------------------------------------------------*/  

  if($Block == 0)
    echo "\n\t<tr height = \"1%\">";
  else
    echo "\n\t<tr>";
  echo "\n\t\t<td>&nbsp;</td>";

  for($Wochentag = 0; $Wochentag <= 5; $Wochentag++)
  {
    $dieserTag = $Montag + ($Wochentag - 1) * 24 * 60 * 60;
    
    //Rahmeneinstellungen
    $rahmen = LayoutRahmen($Block, $MaxStunde, $Wochentag, 5);
      
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
      if($Block == 0)//Erste Zeile->Spaltenbeschriftung (Namen der Tage, evtl. mit Datum)
      {
        echo "\n\t\t<td align = \"center\" class = \"$zelle_beschr_bg $rahmen $rahmen_farbe\" id=\"form_spalte_beschr\">";
        if($KW)//Wenn Ausgabe nach KW, dann Tagesdatum ausgeben
          echo "<span class = \"tab_beschr\">" . $Tag[$Wochentag-1] . "</span><br><span class = \"smallmessage_kl\">" . date("d.m.y", $dieserTag) . "</span>";
        else
          echo "<span class = \"tab_beschr\">" . $Tag[$Wochentag-1] . "</span>";
        echo "</td>";
      }//if

      else//Ausgabe der Unterrichtsbloecke
      {
        if($freierTag[$Wochentag-1] && $Block == 1  && !$FruehUndFerien)//Ausgabe der Ferienspalte
        {
          $rahmenFerien = "x1011";
          if($Wochentag == 5)
            $rahmenFerien = "x1111";
          echo "\n\t\t<td class = \"$zelle_frei_tag_bg $rahmenFerien $rahmen_farbe\" rowspan = \"".($MaxStunde+$AnzAufsichten)."\" nowrap align = \"center\"><span class = \"smallmessage_gr\">" . $Kommentar[$Wochentag-1] . "</span></td>";
        }

        else if(!$freierTag[$Wochentag-1])
        {
          echo "\n\t\t<td nowrap class = \"$zelle_inhalt_bg $rahmen $rahmen_farbe\" id = \"form_inhalt\" align = \"center\"><span class = \"smallmessage_gr\">";
         
          /*-----------------------------------------------------------*/
          /*------------- Ausgabe nach Kalenderwoche ------------------*/          
          if($KW)
          {
	    /*-----------------------------------------------------------*/
            /*------------- Abfrage der Hauptlehrer ------------------*/                      
            $sql = "SELECT DISTINCT Lehrer, Fach, Klasse, Raum, Turnus FROM T_StuPla";
            $sql .= " WHERE Stunde = $Block";
            $sql .= " AND Wochentag = $Wochentag";
            $sql .= " AND Lehrer = \"$Lehrer\"";
            $sql .= " AND Version = $OnlineVersion";
            $sql .= " AND ($sqlTurnus) ORDER BY Turnus;";
            $rs = mysql_query($sql, $db);
            
            //Arrays leeren
	    $aFach   = array();
            $aKlasse = array();
            $aRaum   = array();
            $aLehrer = array();
            $aVB = array();   //fuer Vertretungsbloecke
            $aZB = array();   //fuer Zusatzbloecke
            $aID_V = array(); //fuer Vertretungs-ID's
        
            $aVB["Stunde"] = $Block;				
            $Bemerkung = false;
          
            while($row = mysql_fetch_row($rs))//Fuer den Fall das Mehrfacheintraege vorhanden sind
            {
              //Speicherung Originaldatensatz in Feld aVB
              $aVB["Lehrer"] = $row[0];
              $aVB["Fach"]   = $row[1];            
              $aVB["Klasse"] = $row[2];
              $aVB["Raum"]   = $row[3];
              $aVB["Turnus"] = $row[4];
	      $aVB["Bemerkung"] = "";
	      $aVB["Vertretungen"] = "";  
             
              //Ergaenzen von Feld aVB um Vertretungsplan-Infos / Auslesen der Vertretungs-ID's  
              $aVB = pruefeVertretung($aVB, $dieserTag, "Lehrer", $Lehrer);
	      if(is_array($aVB["Vertretungen"]))
                $aID_V = array_merge($aID_V, $aVB["Vertretungen"]);   
              if(!$Bemerkung && !is_empty($aVB["Bemerkung"]))
              {
                $txtBemerkung = $aVB["Bemerkung"];
                $Bemerkung = true;
              }       
              //Aufbauen der Felder fuer die HTML-Ausgabe
              erw_feld($aVB["Fach"], $aFach);
              erw_feld($aVB["Raum"], $aRaum);
              erw_feld($aVB["Lehrer"], $aLehrer);
              erw_feld($aVB["Klasse"], $aKlasse);

              //Wenn Klasse vorhanden, dann um Turnus ergaenzen
              if(!is_empty($aVB["Klasse"]) && strcmp($aVB["Klasse"], "*") != 0)
                $aKlasse[count($aKlasse) - 1]["Turnus"] = $aVB["Turnus"];  
            }//while

            mysql_free_result($rs);
            $Eintrag = true;          

            $aZB = pruefeZusatzBlock($dieserTag, $Block, "Lehrer", $Lehrer, $aID_V);
            foreach($aZB as $ZB)
            {
	      erw_feld($ZB["Lehrer"], $aLehrer);
              erw_feld($ZB["Fach"], $aFach);
              erw_feld($ZB["Raum"], $aRaum);
              erw_feld($ZB["Klasse"], $aKlasse);      
	      if(!$Bemerkung)
	      {
                $txtBemerkung = $ZB["Bemerkung"];
                $Bemerkung = true;	        
	      }            	            
            }//foreach			  	
	
            //Pruefung auf Ausfall
            if(istLeer($aLehrer) && istLeer($aFach) && istLeer($aRaum) && istLeer($aKlasse))
              $Eintrag = false;
          
            if($Bemerkung)
	    {
	      $schrift = set_schrift($Druck, 1, 1);
              echo "<span class = \"$schrift\">$txtBemerkung</span><br>";
	    }

            if($Eintrag)
            {
              
              /*-----------------------------------------------------------*/
              /*------------- Abfrage der Teilungslehrer ------------------*/ 
              // (Teilungslehrer wenn: Klasse + Fach identisch oder gleicher Raum)
              $sql = "SELECT DISTINCT Lehrer, Fach, Klasse, Raum, Turnus FROM T_StuPla";
              $sql .= " WHERE Stunde = $Block";
              $sql .= " AND Wochentag = $Wochentag";
              $sql .= " AND ((Fach IN (\"" . implode("\",\"",erz_Liste($aFach)) . "\")";
              $sql .= " AND Klasse IN (\"" . implode("\",\"",erz_Liste($aKlasse)) . "\"))";
              $sql .= " OR Raum IN (\"" . implode("\",\"",erz_Liste($aRaum)) . "\"))";
              $sql .= " AND Lehrer <> \"$Lehrer\"";
              $sql .= " AND Version = $OnlineVersion";
              $sql .= " AND ($sqlTurnus) ORDER BY Lehrer;";
              $rs = mysql_query($sql, $db);	      
              while($row = mysql_fetch_row($rs))
              {
	        //echo "<br>GOTCHA<br>";
	        $aTL = array();

                //Speicherung Originaldatensatz des Teilungslehrers in Feld aTL
                $aTL["Lehrer"] = $row[0];
                $aTL["Fach"]   = $row[1];            
                $aTL["Klasse"] = $row[2];
                $aTL["Raum"]   = $row[3];
                $aTL["Turnus"] = $row[4];
		$aTL["Stunde"] = $Block;      

                $aTL = pruefeVertretung($aTL, $dieserTag, "Lehrer", $row[0]);
		
                //Suche nach Teilung ueber Klasse/Fach
                $findKlasse = false;
                $findFach   = false;
		$findRaum   = false;
  
                foreach(erz_Liste($aKlasse) as $Klasse)
                  if(ohneStern($aTL['Klasse']) == $Klasse)
                    $findKlasse = true;

                foreach(erz_Liste($aFach) as $Fach)
                  if(ohneStern($aTL['Fach']) == $Fach)
                    $findFach = true;
  
                //Suche nach Teilung ueber Raum
                foreach(erz_Liste($aRaum) as $Raum)
		{
                  //echo "<br><b>" . $aTL['Raum'] . "</b><br>";
		  if(ohneStern($aTL['Raum']) == $Raum)
                    $findRaum = true;      
                }
		
                if($findRaum || ($findKlasse && $findFach))
		{
	          erw_feld($aTL["Lehrer"], $aLehrer);
                  erw_feld($aTL["Fach"], $aFach);
                  erw_feld($aTL["Raum"], $aRaum);
                  erw_feld($aTL["Klasse"], $aKlasse);		  
                }		
	      }//while
              mysql_free_result($rs);	    

              /*-----------------------------------------------------------*/
              /*------------- Ausgabe des Plans ---------------------------*/
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
                  echo " / ";
                $schrift = set_schrift($Druck, $Klasse["Neu"]);                
                echo "<span class = \"$schrift\"><a class = \"$link_table\" href=\"http://www.oszimt.de/0-schule/stundenplan/KPlan1.php?Klasse=" . urlencode($Klasse["Name"]) . "&Version=$OnlineVersion\" target=\"_blank\">" . $Klasse["Name"] . "</a></span>";
                if(isset($Klasse["Turnus"]) && $Klasse["Turnus"] != "jede Woche")
                  echo " (" . $Klasse["Turnus"] . ")";
                $zaehler++;
              }
	      if($zaehler != 0)
                echo "<br>";    
  
              //Raum
              $zaehler = 0;
              foreach($aRaum as $Raum)
              {
                if($zaehler > 0)
                  echo " / ";
                $schrift = set_schrift($Druck, $Raum["Neu"]);    
                echo "<span class = \"$schrift\"><a class = \"$link_table\" href=\"RPlan1.php?Raum=" . urlencode($Raum["Name"]) . "&ID_Woche=$ID_Woche\" target=\"_blank\">" . $Raum["Name"] . "</a></span>";
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
                echo "<span class = \"$schrift\"><a class = \"$link_table\" href=\"LPlan1.php?Lehrer=" . urlencode($Lehrperson["Name"]) . "&ID_Woche=$ID_Woche&KW=true\" target=\"_blank\">" . $Lehrperson["Name"] . "</a></span>";
                $zaehler++;
              }
            }//if($Eintrag)

            else//leere Zelle
              echo "&nbsp;";
          }//if($KW)

          /*-----------------------------------------------------------*/
          /*------------- Ausgabe nach Turnussen ----------------------*/
          else
          {
            $sql = "SELECT DISTINCT Fach, Klasse, Turnus FROM T_StuPla";
            $sql .= " WHERE Stunde = $Block";
            $sql .= " AND Wochentag = $Wochentag";
            $sql .= " AND Lehrer = \"$Lehrer\"";
            $sql .= " AND Version = $OnlineVersion";
            $sql .= " AND ($sqlTurnus) ORDER BY Turnus;";
            $rs = mysql_query($sql, $db);
            
            $doppelt = 0;
            
            while($row = mysql_fetch_row($rs))//Fuer den Fall das Mehrfacheintraege vorhanden sind
            {
              //Abfrage Raeume
              $sql = "SELECT DISTINCT Raum FROM T_StuPla";
              $sql .= " WHERE Stunde = $Block";
              $sql .= " AND Wochentag = $Wochentag";
              $sql .= " AND Fach = \"$row[0]\"";
              $sql .= " AND Klasse = \"$row[1]\"";
              $sql .= " AND Version = $OnlineVersion";
              $sql .= " AND ($sqlTurnus) ORDER BY Raum;";
              $rsRaum = mysql_query($sql, $db);

              //Abfrage Lehrer
              $sql = "SELECT DISTINCT Lehrer FROM T_StuPla";
              $sql .= " WHERE Stunde = $Block";
              $sql .= " AND Wochentag = $Wochentag";
              $sql .= " AND Fach = \"$row[0]\"";
              $sql .= " AND Klasse = \"$row[1]\"";
              $sql .= " AND Version = $OnlineVersion";
              $sql .= " AND ($sqlTurnus) ORDER BY Lehrer;";
              $rsLehrer = mysql_query($sql, $db);

              //Fach
              if($doppelt > 0)
                echo "<br><br>";
              echo "<b>$row[0]</b><br>";

              //Klasse
              if($row[2] != "jede Woche")
                echo "<a class = \"$link_table\" href=\"http://www.oszimt.de/0-schule/stundenplan/KPlan1.php?Klasse=" . urlencode("$row[1]") . "\" target=\"_blank\">$row[1]</a> ($row[2])<br>";
              else
                echo "<a class = \"$link_table\" href=\"http://www.oszimt.de/0-schule/stundenplan/KPlan1.php?Klasse=" . urlencode("$row[1]") . "\" target=\"_blank\">$row[1]</a><br>";

              //Raum
              $counter1=0;
              $AnzRaeume = mysql_num_rows($rsRaum);
              while($row = mysql_fetch_row($rsRaum))
              {
                echo "$row[0]";
                if($counter1 < $AnzRaeume-1 && $AnzRaeume > 1)
                  echo " / ";
                $counter1++;
              }
              echo "<br>";

              //Lehrer
              $counter1=0;
              $AnzLehrer = mysql_num_rows($rsLehrer);
              while($row = mysql_fetch_row($rsLehrer))
              {
                echo "<a class = \"$link_table\" href=\"LPlan1.php?Lehrer=" . urlencode("$row[0]") . "&$linkTurnus\" target=\"_blank\">$row[0]</a>";
                if($counter1 < $AnzLehrer-1 && $AnzLehrer > 1)
                  echo " / ";
               $counter1++;
              }//while      
              $doppelt++;
              mysql_free_result($rsLehrer);              
              mysql_free_result($rsRaum);            
            }//while(Mehrfacheintraege)
            mysql_free_result($rs);
            if($doppelt == 0)//leere Zelle
              echo "&nbsp;";
          }//else (Ausgabe nach Turnussen)
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
  $LPlanLink = "LPlan1.php?Lehrer=$Lehrer&Druck=true&Version=$OnlineVersion";
  if($KW)
    $LPlanLink .= "&ID_Woche=$ID_Woche&KW=true";
  else
    foreach($selTurnus as $turnus)
      $LPlanLink .= "&selTurnus[]=$turnus";  
 
  echo ladeLink("http://www.oszimt.de", "<b>Home</b>");
  echo ladeLink("../index.php", "Interner Bereich");        
  echo ladeLink("LPlan.php", "Lehrerauswahl");
  echo ladeLink("$LPlanLink", "Druckversion", "target = \"_blank\"");
}
echo ladeOszFuss($Druck);
?>
