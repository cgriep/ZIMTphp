<?php
/*----------------------------------------------------------------------------------------*/
//Name              : getTurnusListe()
//Kurzbeschreibung  : Schreibt alle Turnusse einer bestimmten KW in ein Array                
//Uebergabeparameter: $ID_Woche: ID der KW, &$TurnusListe: Referenz auf ein Array
//Rueckgabewert     : true / false
//Fehlerbehandlung  : Rueckgabewert false, wenn der KW kein Turnus zugeordnet ist
/**
 * @deprecated Wird ersetzt durch getTurnusListe() in StuPlaInfo.class
 */
function getTurnusListe($ID_Woche = -1, &$TurnusListe)
{
  if($ID_Woche == -1)
    return false;
  $sql = "SELECT T.Turnus FROM T_Turnus AS T, T_WocheTurnus AS WT, T_TurnusGruppe AS TG WHERE WT.F_ID_Woche = $ID_Woche AND WT.F_ID_Turnus = T.ID_Turnus AND T.F_ID_Gruppe = TG.ID_Gruppe ORDER BY TG.Nummer, T.Turnus;";
  $rs = mysql_query($sql);
  if(mysql_num_rows($rs) == 0)//z.B. Ferien

  {
    mysql_free_result($rs);
    return false;
  }
  while($row = mysql_fetch_row($rs))
    $TurnusListe[] = $row[0];
  mysql_free_result($rs);
  return true;
}
/*----------------------------------------------------------------------------------------*/
//Name              : istFrei()
//Kurzbeschreibung  : Liefert true, wenn am per Timestamp uebergebenen Tag Unterrichtsfrei ist                    
//Uebergabeparameter: Timestamp (Wenn die Fkt. ohne Argument aufgerufen wird, dann wird das aktuelle Datum geprueft)
//Rueckgabewert     : true / false
//Fehlerbehandlung  : ---  
function istFrei($datum = -1)
{
  if($datum == -1)
    $datum = time();
  $sql = "SELECT * FROM T_FreiTage WHERE ersterTag <= $datum AND letzterTag >= $datum;";
  $rs = mysql_query($sql);
  if(mysql_num_rows($rs) == 1)
    $istFrei = true;
  else
    $istFrei = false;
  mysql_free_result($rs);
  return $istFrei;
}
/*----------------------------------------------------------------------------------------*/
//Name              : getFerienKommmentar()
//Kurzbeschreibung  : Liefert den Kommentar des per Timestamp uebergebenen Tages                    
//Uebergabeparameter: Timestamp
//Rueckgabewert     : Kommentar
//Fehlerbehandlung  : Rueckgabewert Leerstring, wenn kein oder mehrere Kommentare vorhanden sind  
function getFerienKommentar($datum = -1)
{
  if($datum == -1)
    return " ";
  $sql = "SELECT Kommentar FROM T_FreiTage WHERE ersterTag <= $datum AND letzterTag >= $datum;";
  $rs = mysql_query($sql);
  if(mysql_num_rows($rs) == 1)
  {
    $row = mysql_fetch_row($rs);
    $Kommentar = $row[0];
  }
  else
    $Kommentar = " ";
  mysql_free_result($rs);
  return $Kommentar;

}
/*----------------------------------------------------------------------------------------*/
//Name              : getKW()
//Kurzbeschreibung  : Liefert die Nummer der Kalenderwoche                    
//Uebergabeparameter: ID der Woche
//Rueckgabewert     : Nummer KW
//Fehlerbehandlung  : Rueckgabewert -1, wenn keine Woche zur ID gefunden wird  
/**
 * @deprecated Wird ersetzt durch getNummerKW() in StuPlaInfo.class
 */
function getKW($ID_Woche = -1)
{
  if($ID_Woche == -1)
    return -1;
  $sql = "SELECT Woche FROM T_Woche WHERE ID_Woche = $ID_Woche;";
  $rs = mysql_query($sql);
  if(mysql_num_rows($rs) == 1)
  {
    $row = mysql_fetch_row($rs);
    $KW = $row[0];
  }
  else
    $KW = -1;
  mysql_free_result($rs);
  return $KW;
}
/*----------------------------------------------------------------------------------------*/
//Name              : getMontag()
//Kurzbeschreibung  : Liefert den Timestamp des Montags der Woche                    
//Uebergabeparameter: ID der Woche
//Rueckgabewert     : Timestamp Montag
//Fehlerbehandlung  : Rueckgabewert -1, wenn keine Woche zur ID gefunden wird  
/**
 * @deprecated Wird ersetzt durch getMontagKW() in StuPlaInfo.class
 */
function getMontag($ID_Woche = -1)
{
  if($ID_Woche == -1 || ! is_numeric($ID_Woche))
    return -1;
  $sql = "SELECT Montag FROM T_Woche WHERE ID_Woche = $ID_Woche;";
  $rs = mysql_query($sql);
  if(mysql_num_rows($rs) == 1)
  {
    $row = mysql_fetch_row($rs);
    $Montag = $row[0];
  }
  else
    $Montag = -1;
  mysql_free_result($rs);
  return $Montag;
}
/*----------------------------------------------------------------------------------------*/
//Name              : getSJahr()
//Kurzbeschreibung  : Liefert das Schuljahr (Syntax: 05/06 wie in T_Woche), liegt das uebergebene
//                    Datum nicht innerhalb eines Schuljahres dann wird das kommende Schuljahr 
//                    zurueckgegeben (wenn es fuer dieses noch keinen Turnusplan gibt, wird -1 zurueckgegeben)
//                    Die Umschaltung auf das neue Schuljahr erfolgt am letzten Freitag des Schuljahres
//                    um 23:59:59                     
//Uebergabeparameter: Timestamp, wenn kein Uebergabewert vorhanden wird das aktuelle Datum genommen
//Rueckgabewert     : Schuljahr
//Fehlerbehandlung  : Rueckgabewert -1, wenn kein Wochenplan vorhanden ist  
function getSJahr($datum = 0)
{
  if($datum == 0)
    $datum = time();
  $datum = strtotime(date("Y-m-d",$datum) . " -4 day");
  $sql = "SELECT MAX(Montag), SJahr FROM T_Woche GROUP BY SJahr ORDER BY SJahr;";
  $rs = mysql_query($sql);
  while ($row = mysql_fetch_row($rs))
  {
    if($datum <= $row[0])
    {
      mysql_free_result($rs);
      return $row[1];
    }
  }
  mysql_free_result($rs);
  return -1;
}
/*----------------------------------------------------------------------------------------*/
//Name              : getID_Woche()
//Kurzbeschreibung  : Liefert die ID der Woche in der das Datum liegt
//Uebergabeparameter: Datum (ohne Argument wird das aktuelle Datum genommen)
//Rueckgabewert     : ID der Woche
//Fehlerbehandlung  : Rueckgabewert -1, wenn keine passende ID gefunden wird   
/**
 * @deprecated Wird ersetzt durch getID_KW() in StuPlaInfo.class
 */
function getID_Woche($datum = 0)
{
  if($datum == 0)
    $datum = time();
  $sql = "SELECT ID_Woche FROM T_Woche WHERE Montag <= $datum AND Montag > " . (strtotime(date("Y-m-d",$datum) . " -7 day")) . ";";
  $rs = mysql_query($sql);
  if(mysql_num_rows($rs) == 1)
  {
    $row = mysql_fetch_row($rs);
    $ID_Woche = $row[0];
  }
  else
    $ID_Woche = -1;
  mysql_free_result($rs);
  return $ID_Woche;
}
/*----------------------------------------------------------------------------------------*/
//Name              : getSJahr_AnschlussSchuljahr()
//Kurzbeschreibung  : Liefert das folgende oder vorherige Schuljahr 
//Uebergabeparameter: $strAktSJahr: Das aktuelle Schuljahr, $strRichtung: '+' -> naechstes Schuljahr, '-' -> vorheriges Schuljahr
//Rueckgabewert     : String des Jahres (z.B. 05/06)
//Fehlerbehandlung  : Rueckgabewert -1, wenn kein Schuljahr gefunden wird   
function getSJahr_AnschlussSchuljahr($strAktSJahr, $strRichtung)
{
  $arrSJahr = array();

  if ( $strRichtung == '+' || $strRichtung == '-' )
  {
    $sql = 'SELECT DISTINCT SJahr FROM T_Woche ORDER BY SJAHR';
    $rs = mysql_query($sql);

    while ( $row = mysql_fetch_assoc($rs) )
      $arrSJahr[] = $row['SJahr'];

    mysql_free_result($rs);

    for ( $i = 0; $i < count($arrSJahr); $i++ )
      if ( $strAktSJahr == $arrSJahr[$i] )
      {
        if ( $strRichtung == '+' && $i < count($arrSJahr) - 1 )
          return $arrSJahr[$i + 1];
        elseif ( $strRichtung == '-' && $i > 0 )
          return $arrSJahr[$i - 1];
      }
  }
  return -1;
}
/*----------------------------------------------------------------------------------------*/
//Name              : getID_Anschluss_KW()
//Kurzbeschreibung  : Liefert die ID der folgenden oder vorherigen KW
//Uebergabeparameter: $IDdieseKW: ID der aktuellen KW, $strRichtung: '+' -> naechste Woche, '-' -> vorherige Woche
//Rueckgabewert     : ID der Woche
//Fehlerbehandlung  : Rueckgabewert -1, wenn keine ID gefunden wird   
function getID_Anschluss_KW($intID_dieseKW, $strRichtung)
{
  if ( !is_numeric($intID_dieseKW) || ($strRichtung != '+' && $strRichtung != '-') )
    return -1;

  $strSql = "SELECT SJahr, Montag FROM T_Woche WHERE ID_Woche = $intID_dieseKW";
  $rs = mysql_query($strSql);

  if( !$rs || mysql_num_rows($rs) != 1 )
  {
    mysql_free_result($rs);
    return -1;
  }

  $row = mysql_fetch_assoc($rs);
  $strSJahr  = $row['SJahr'];
  $intMontagTS = $row['Montag'];

  mysql_free_result($rs);

  if( $strRichtung == '+' )
    $strSql = "SELECT ID_Woche FROM T_Woche WHERE Montag = (SELECT MIN(Montag) FROM T_Woche WHERE Montag > $intMontagTS AND SJahr = '$strSJahr')";
  else
    $strSql = "SELECT ID_Woche FROM T_Woche WHERE Montag = (SELECT MAX(Montag) FROM T_Woche WHERE Montag < $intMontagTS AND SJahr = '$strSJahr')";

  $rs = mysql_query($strSql);

  if( !$rs || mysql_num_rows($rs) != 1 )
    $intID_KW = -1;
  else
  {
    $row = mysql_fetch_assoc($rs);
    $intID_KW  = $row['ID_Woche'];
  }

  mysql_free_result($rs);

  if ( $intID_KW == -1 ) //Keine Anschluss-KW gefunden -> evtl. Sprung in naechstes/vorheriges Schuljahr

  {
    if ( $strRichtung == '+' )
    {
      $strSJahr = getSJahr_AnschlussSchuljahr($strSJahr, '+');
      if ( $strSJahr == -1 )
        return -1;
      $strSql = "SELECT ID_Woche FROM T_Woche WHERE Montag = (SELECT MIN(Montag) FROM T_Woche WHERE SJahr = '$strSJahr')";
    }
    else
    {
      $strSJahr = getSJahr_AnschlussSchuljahr($strSJahr, '-');
      if ( $strSJahr == -1 )
        return -1;
      $strSql = "SELECT ID_Woche FROM T_Woche WHERE Montag = (SELECT MAX(Montag) FROM T_Woche WHERE SJahr = '$strSJahr')";
    }
    $rs = mysql_query($strSql);

    if( !$rs || mysql_num_rows($rs) != 1 )
    {
      mysql_free_result($rs);
      return -1;
    }

    $row = mysql_fetch_assoc($rs);
    $intID_KW  = $row['ID_Woche'];

    mysql_free_result($rs);
  }
  return $intID_KW;
}
  /*----------------------------------------------------------------------------------------*/
//Name              : getID_Anschluss_KW()
//Kurzbeschreibung  : Liefert die ID der folgenden oder vorherigen KW
//Uebergabeparameter: $IDdieseKW: ID der aktuellen KW, $Richtung: '+' -> naechste Woche, '-' -> vorherige Woche
//Rueckgabewert     : ID der Woche
//Fehlerbehandlung  : Rueckgabewert -1, wenn keine ID gefunden wird   
  function getID_Anschluss_KW_ALT($IDdieseKW, $Richtung)
  {
    if(is_numeric($IDdieseKW) && ($Richtung == '+' || $Richtung == '-'))
    {
      $sql1 = "SELECT SJahr, Montag FROM T_Woche WHERE ID_Woche = $IDdieseKW;";
      $rs1 = mysql_query($sql1);
      if(mysql_num_rows($rs1) == 1)
      {
        $row = mysql_fetch_row($rs1);
        $SJahr  = $row[0];
        $Montag = $row[1];
        if($Richtung == '+')
          $sql2 = "SELECT MIN(ID_Woche) FROM T_Woche WHERE Montag > $Montag AND SJahr = \"" . $SJahr . "\";";
        else
          $sql2 = "SELECT MAX(ID_Woche) FROM T_Woche WHERE Montag < $Montag AND SJahr = \"" . $SJahr . "\";";
        $rs2 = mysql_query($sql2);
        if(mysql_num_rows($rs2) == 1)
        {
          $row = mysql_fetch_row($rs2);
          $ID  = $row[0];
          if ( !is_numeric($ID) )
            $ID = -1;
        }
        else
          $ID = -1;

        mysql_free_result($rs2);
      }
      else
        $ID = -1;
      mysql_free_result($rs1);
    }
    else
      $ID = -1;
    return $ID;
  }
  /*----------------------------------------------------------------------------------------*/
//Name              : getGueltigAbVersion()
//Kurzbeschreibung  : Liefert das Gueltigkeitsdatum einer Version
//Uebergabeparameter: Timestamp der Version
//Rueckgabewert     : Timestamp GueltigAb
//Fehlerbehandlung  : Rueckgabewert -1, wenn fuer den uebergebenen Timestamp keine Version gefunden wird
  /**
   * @deprecated Wird ersetzt durch getGueltigAbVersionDatum() in StuPlaInfo.class
   */
  function getGueltigAbVersion($version = -1)
  {
    if($version == -1  || !is_numeric($version))
      $datum = -1;
    else
    {
      $sql = "SELECT DISTINCT GueltigAb FROM T_StuPla WHERE Version = $version;";
      $rs = mysql_query($sql);
      if($row = mysql_fetch_row($rs))
        $datum = $row[0];
      else
        $datum = -1;
      mysql_free_result($rs);
      return $datum;
    }
  }
  /*----------------------------------------------------------------------------------------*/
//Name              : get_LPlanEintrag()
//Kurzbeschreibung  : Gibt einen Eintrag fuer den Lehrerplan (KW-Ansicht) zurueck.
//Uebergabeparameter: Datum des Eintrags, Name des Lehrers (StuPla-Kuerzel), Block
//Rueckgabewert     : Formatierter Lehrerplaneintrag
//Fehlerbehandlung  : Rueckgabewert -1, wenn ein Fehler aufgetreten ist  
  function get_LPlanEintrag($datum, $lehrer, $block)
  {
    $id_Woche  = getID_Woche($datum);
    $wochentag = date('w',$datum);
    $montag    = getMontag($id_Woche);
    $stringLPlanEintrag = '';

    if($montag == -1)
      return -1;

    //Timestamp Online-Version ermitteln
    $onlineVersion = getAktuelleVersion($montag);
    if($onlineVersion == -1)
      return -1;

    //Auswertung der Turnusse dieser KW
    if(getTurnusListe($id_Woche, $TurnusListe))
    {
      //Erstellen des SQL-Abfragestrings
      foreach($TurnusListe as $Turnus)
        $sqlTurnus[] = "\"$Turnus\"";
      $sqlTurnus[] = "\"jede Woche\"";
      $sqlTurnus = "Turnus = " . implode(" OR Turnus = ", $sqlTurnus);
    }
    else
      $sqlTurnus = "Turnus = \"jede Woche\"";

    /*-----------------------------------------------------------*/
    /*------------- Abfrage der Hauptlehrer ------------------*/
    $sql = "SELECT DISTINCT Lehrer, Fach, Klasse, Raum, Turnus FROM T_StuPla";
    $sql .= " WHERE Stunde = $block";
    $sql .= " AND Wochentag = $wochentag";
    $sql .= " AND Lehrer = \"$lehrer\"";
    $sql .= " AND Version = $onlineVersion";
    $sql .= " AND ($sqlTurnus) ORDER BY Turnus;";
    $rs = mysql_query($sql);

    //Arrays leeren
    $aFach   = array();
    $aKlasse = array();
    $aRaum   = array();
    $aLehrer = array();
    $aVB = array();   //fuer Vertretungsbloecke
    $aZB = array();   //fuer Zusatzbloecke
    $aID_V = array(); //fuer Vertretungs-ID's

    $aVB["Stunde"] = $block;
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
      $aVB = pruefeVertretung($aVB, $datum, "Lehrer", $lehrer);
      if(is_array($aVB["Vertretungen"]))
        $aID_V = array_merge($aID_V, $aVB["Vertretungen"]);
      if( !$Bemerkung && trim($aVB["Bemerkung"]) != "" )
      {
        $txtBemerkung = trim($aVB["Bemerkung"]);
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
    $eintrag = true;

    $aZB = pruefeZusatzBlock($datum, $block, "Lehrer", $lehrer, $aID_V);
    foreach($aZB as $ZB)
    {
      erw_feld($ZB["Lehrer"], $aLehrer);
      erw_feld($ZB["Fach"], $aFach);
      erw_feld($ZB["Raum"], $aRaum);
      erw_feld($ZB["Klasse"], $aKlasse);
      if( !$Bemerkung && trim($ZB["Bemerkung"]) != '' )
      {
        $txtBemerkung = trim($ZB["Bemerkung"]);
        $Bemerkung = true;
      }
    }//foreach

    //Pruefung auf Ausfall
    if(istLeer($aLehrer) && istLeer($aFach) && istLeer($aRaum) && istLeer($aKlasse))
      $eintrag = false;

    if($Bemerkung)
      echo "<span class = \"info\" onMouseOver=\"return overlib('" . $txtBemerkung . "',CAPTION,'Hinweis zur &Auml;nderung');\" onMouseOut=\"return nd();\">Info</span><br />";

    if($eintrag)
    {
      /*-----------------------------------------------------------*/
      /*------------- Abfrage der Teilungslehrer ------------------*/
      // (Teilungslehrer wenn: Klasse + Fach identisch oder gleicher Raum)
      $sql = "SELECT DISTINCT Lehrer, Fach, Klasse, Raum, Turnus FROM T_StuPla";
      $sql .= " WHERE Stunde = $block";
      $sql .= " AND Wochentag = $wochentag";
      $sql .= " AND ((Fach IN (\"" . implode("\",\"",erz_Liste($aFach)) . "\")";
      $sql .= " AND Klasse IN (\"" . implode("\",\"",erz_Liste($aKlasse)) . "\"))";
      $sql .= " OR Raum IN (\"" . implode("\",\"",erz_Liste($aRaum)) . "\"))";
      $sql .= " AND Lehrer <> \"$lehrer\"";
      $sql .= " AND Version = $onlineVersion";
      $sql .= " AND ($sqlTurnus) ORDER BY Lehrer;";

      $rs = mysql_query($sql);

      while($row = mysql_fetch_row($rs))
      {
        $aTL = array();

        //Speicherung Originaldatensatz des Teilungslehrers in Feld aTL
        $aTL["Lehrer"] = $row[0];
        $aTL["Fach"]   = $row[1];
        $aTL["Klasse"] = $row[2];
        $aTL["Raum"]   = $row[3];
        $aTL["Turnus"] = $row[4];
        $aTL["Stunde"] = $block;

        $aTL = pruefeVertretung($aTL, $datum, "Lehrer", $row[0]);

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
          if(ohneStern($aTL['Raum']) == $Raum)
            $findRaum = true;

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
      /*------------- Erzeugen des Ausgabestrings -----------------*/
      //Fach
      $zaehler = 0;
      foreach($aFach as $Fach)
      {
        if($Fach["Neu"])
          $class = 'spanPlanGeaendert';
        else
          $class = 'spanPlanNormal';

        if($zaehler > 0)
          $stringLPlanEintrag .= " / ";

        $stringLPlanEintrag .= "<span class = \"$class\"><b>" . $Fach["Name"] . '</b></span>';

        $zaehler++;
      }
      if($zaehler != 0)
        $stringLPlanEintrag .= "<br>";

      //Klasse
      $zaehler = 0;
      foreach($aKlasse as $Klasse)
      {
        if($Klasse["Neu"])
          $class = 'spanPlanGeaendert';
        else
          $class = 'spanPlanNormal';

        if($zaehler > 0)
          $stringLPlanEintrag .= " / ";

        $stringLPlanEintrag .= "<span class = \"$class\"><a class = \"linktable\" href=\"http://www.oszimt.de/0-schule/stundenplan/KPlan1.php?Klasse=" . urlencode($Klasse["Name"]) . "&Version=$onlineVersion\" target=\"_blank\">" . $Klasse["Name"] . "</a></span>";

        if(isset($Klasse["Turnus"]) && $Klasse["Turnus"] != "jede Woche")
          $stringLPlanEintrag .= " (" . $Klasse["Turnus"] . ")";

        $zaehler++;
      }
      if($zaehler != 0)
        $stringLPlanEintrag .= "<br>";

      //Raum
      $zaehler = 0;
      foreach($aRaum as $Raum)
      {
        if($Raum["Neu"])
          $class = 'spanPlanGeaendert';
        else
          $class = 'spanPlanNormal';

        if($zaehler > 0)
          $stringLPlanEintrag .= " / ";

        $stringLPlanEintrag .= "<span class = \"$class\"><a class = \"linktable\" href=\"RPlan1.php?Raum=" . urlencode($Raum["Name"]) . "&ID_Woche=$id_Woche\" target=\"_blank\">" . $Raum["Name"] . "</a></span>";

        $zaehler++;
      }
      if($zaehler != 0)
        $stringLPlanEintrag .= "<br>";

      //Lehrer
      $zaehler = 0;
      foreach($aLehrer as $Lehrperson)
      {
        if($Lehrperson["Neu"])
          $class = 'spanPlanGeaendert';
        else
          $class = 'spanPlanNormal';

        if($zaehler > 0)
          $stringLPlanEintrag .= " / ";

        $stringLPlanEintrag .= "<span class = \"$class\"><a class = \"linktable\" href=\"LPlan1.php?Lehrer=" . urlencode($Lehrperson["Name"]) . "&ID_Woche=$id_Woche&KW=true\" target=\"_blank\">" . $Lehrperson["Name"] . "</a></span>";

        $zaehler++;
      }
    }//if($eintrag)
    return $stringLPlanEintrag;
  }
  /*----------------------------------------------------------------------------------------*/
  /**
   * @deprecated Wird ersetzt durch getGueltigAbDatum() in StuPlaInfo.class
   */
  function getGueltigAb($vonWann = -1, $Tabelle = "T_StuPla" )
  {
    if ( $vonWann == -1 || ! is_numeric($vonWann) )
      $vonWann = time();
    $query = mysql_query("SELECT MAX(GueltigAb) ".
            "FROM $Tabelle WHERE GueltigAb <= $vonWann AND GueltigAb > 0");
    $datum = -1;
    if ( $Gueltig = mysql_fetch_row($query) )
      if ( is_numeric($Gueltig[0]) )
        $datum = $Gueltig[0];
    mysql_free_result($query);
    if ( $datum == -1 )
    {
      // Datum zu alt - die Stundenplanversion gibt es bereits nicht mehr
      // dann nimm die �lteste Version die da ist und bei der GueltigAb > 0 ist
      $query = mysql_query("SELECT Min(GueltigAb) FROM $Tabelle WHERE GueltigAb > 0");
      if ( $Gueltig = mysql_fetch_row($query) )
        if ( is_numeric($Gueltig[0]) )
          $datum = $Gueltig[0];
      mysql_free_result($query);
    }
    return $datum;
  }
  /*----------------------------------------------------------------------------------------*/
  /**
   * @deprecated Wird ersetzt durch getGueltigeVersionDatum() in StuPlaInfo.class
   */
  function getAktuelleVersion($vonWann = -1 )
  {
    $datum = getGueltigAb($vonWann);
    $Version = -1;
    if ( $datum != -1 )
    {
      $query = mysql_query("SELECT DISTINCT Version FROM T_StuPla WHERE GueltigAb = $datum");
      if ( $Version = mysql_fetch_row($query) )
        $Version = $Version[0];
      mysql_free_result($query);
    }
    return $Version;
  }
  /*----------------------------------------------------------------------------------------*/
// Berechnet die aktuelle Version am dem Timestamp $vonWann
// wenn $vonWann ein Samstag oder Sonntag ist, wird so getan als sei schon 
// Montag, weil Montags evtl. Versionswechsel erfolgen
  /**
   * @deprecated Wird ersetzt durch getGueltigeVersionDatumWE() in StuPlaInfo.class
   */
  function getAktuelleVersionWE($vonWann = -1)
  {
    if ( $vonWann == -1 )
      $vonWann = time();
    if ( date("w",$vonWann) == 0 ) // Sonntag
      $vonWann = strtotime("+1 day", $vonWann);
    if ( date("w",$vonWann) == 6 ) // Samstag
      $vonWann = strtotime("+2 day", $vonWann);
    return getAktuelleVersion($vonWann);
  }
  /*----------------------------------------------------------------------------------------*/
  /**
   * @deprecated Wird ersetzt durch getNaechsteVersion() in StuPlaInfo.class
   */
  function getNaechsteVersion($aktuelleVersion = -1)
  {
    //Liefert den timestamp der naechsten Version, wenn es keine naechste Version gibt -1
    if ($aktuelleVersion == -1)
      $aktuelleVersion = time();

    $sql = "SELECT DISTINCT GueltigAb FROM T_StuPla WHERE Version = $aktuelleVersion;";
    $rs = mysql_query($sql);

    if ($row = mysql_fetch_row($rs))
    {
      $aktuellGueltigAb = $row[0];
      mysql_free_result($rs);
    }
    else
    {
      mysql_free_result($rs);
      return -1;
    }

    $naechsteVersion = -1;
    $sql = "SELECT DISTINCT Version FROM T_StuPla WHERE GueltigAb > $aktuellGueltigAb ORDER BY GueltigAb;";
    $rs = mysql_query($sql);
    if ($row = mysql_fetch_row($rs))
      $naechsteVersion = $row[0];

    mysql_free_result($rs);
    return $naechsteVersion;
  }

?>
