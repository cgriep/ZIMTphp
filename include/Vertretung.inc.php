<?php
/**
 * darfReservieren($Kuerzel
 * Gibt an, ob der aktuelle Benutzer den Raum mit der angegebenen Raumnummer 
 * reservieren darf.
 * @param $Kuerzel die Raumnummer
 * @return true, wenn der Benutzer reservieren darf, false sonst
 */
function darfReservieren($Kuerzel)
{
	include_once('include/raeume.inc.php');	
	// TODO
	// Raum langfristig reservierbar?
	$ergebnis = false;
	$Kuerzel = Raumbezeichnung($Kuerzel);
	if ($query = mysql_query('SELECT * FROM T_Raeume WHERE Raumnummer="'.$Kuerzel.'"'))
	{
	  if ( $raum = mysql_fetch_array($query))
	  {
	    // Raum reservierbar ?	
	  	if ( $raum['Reservierbar'])
	  	{
          // Person im Reservierbar-Personenkreis?
          if ( $raum['Reservierungsberechtigung'] == '' ||
	  	       in_array(strtoupper($_SERVER['REMOTE_USER']), 
	  	         explode("\n",str_replace(' ','',str_replace("\r",'',
                             strtoupper($raum['Reservierungsberechtigung']))))) ) 	  	     
	  	    $ergebnis = true; }
	  	
	  }	
	  mysql_free_result($query);
	}
	return $ergebnis;
}

/**
  * Entfernt ein führendes Sternchen sofern vorhanden
  * @param $wert die Zeichenkette
  * @return eine Zeichenkette ohne führendes Sternchen. Wenn kein Sternchen vorhanden ist,
  *          die Originalzeichenkette
  */
function ohneStern($wert)
{
  if ( strpos($wert,'*') !== false )
    return substr($wert,1);
  else
    return $wert;
}

/**
  Gibt an, ob ein Raum, eine Klasse oder ein Lehrer am betroffenen Datum eine 
  Sperrung hat. Achtung: Findet nur blockweise Sperrungen - die tagesweisen
  Sperrungen findet istVerhindert(). 
  @param $Datum Das Datum um das es geht
  @param $lehrer Die Bezeichnung von Lehrer, Klasse oder Raum (Kürzel)
  @param $Stunde numerisch, die Stunde um die es geht 
  @return true, wenn eine Sperrung vorliegt, false sonst
  */
function istGesperrt($Datum, $lehrer, $Stunde)
{
  if ( ! is_numeric($Datum) || ! is_numeric($Stunde)) return false;
  $lehrer = mysql_real_escape_string($lehrer);
  $query = mysql_query('SELECT Count(*) FROM T_Verhinderung_Sperrungen '.
    "WHERE LehrerKuerzel='$lehrer' AND Wochentag=".date('w',$Datum).
    " AND StundeVon <= $Stunde AND StundeBis >= $Stunde");
  $row = mysql_fetch_array($query);
  mysql_free_result($query);
  if ( $row[0] != 0 )
    return true;
  return false;
}

/**
  Gibt an, ob ein Raum, eine Klasse oder ein Lehrer am betroffenen Datum eine Verhinderung
  hat.
  @param $Datum Das Datum um das es geht
  @param $lehrer Die Bezeichnung von Lehrer, Klasse oder Raum (Kürzel)
  @param $Block der Block um den es geht
  @param $Art Zeichenkette 'Lehrer', 'Klasse' oder 'Raum'
  @return true, wenn eine Verhinderung vorliegt, false sonst
  */
function istVerhindert($Datum, $lehrer, $Block, $Art='Lehrer')
{
  if ( ! is_numeric($Datum) ) return false;
  if ( ! is_numeric($Block) || $Block < 1 || $Block > 6 ) return false;
  if ( $Art == 'Raum' ) 
  {
  	include_once('include/raeume.inc.php');	
  	$lehrer = Raumbezeichnung($lehrer);
  }
  $lehrer = mysql_real_escape_string($lehrer);
  $Art = mysql_real_escape_string($Art);
  $query = mysql_query("SELECT Count(*) FROM T_Verhinderungen WHERE Art='$Art' AND ".
                       "Wer='$lehrer' AND Von <= $Datum AND Bis >= $Datum AND UE$Block");
  $row = mysql_fetch_array($query);
  mysql_free_result($query);
  if ( $row[0] != 0 )
    return true;
  $Datum = date('w',$Datum);
  $query = mysql_query('SELECT Count(*) FROM T_Verhinderung_Sperrungen '.
    "WHERE LehrerKuerzel ='$lehrer' AND Wochentag=$Datum AND StundeVon=0");
  $row = mysql_fetch_array($query);
  mysql_free_result($query);
  if ( $row[0] != 0 )
    return true; 
  return false;
}

/**
Prüft wann die letzte Vertretung an einem Datum vorliegt.

@param $Datum das Datum um das es geht
@param $Wofuer Zeichenkette, die angibt wofuer der Plan sein soll
              Zeichenkette:Lehrer,Klasse,Raum
@param $Anzeige - Der Wert des Feldes (also der Lehrer/Klasse/Raum, um den es geht
@return Die höchste Blocknummer als Zahl
Hinweis: Wenn die höchste Vertretung ein Ausfall ist wird sie dennoch als
Vertretung ausgewiesen.
*/
function holeMaxBlockVertretungen($Datum, $Wofuer, $Anzeige)
{
  $query = mysql_query('SELECT Max(Stunde) FROM T_Vertretungen '.
    "WHERE ($Wofuer='$Anzeige' OR {$Wofuer}_Neu='$Anzeige') AND Datum=$Datum");
  $row = mysql_fetch_row($query);
  mysql_free_result($query);
  return $row[0];
}
//Prüft wann die letzte Vertretung in einer KW vorliegt
//@param $ID_Woche die ID der Kalenderwoche, sonst wie oben
function holeMaxBlockVertretungenKW($ID_Woche, $Wofuer, $Anzeige)
{
  $Montag = getMontag($ID_Woche);
  if($Montag == -1)
    return -1;
  $Freitag = strtotime('+4 day',$Montag);
  $query = mysql_query('SELECT Max(Stunde) FROM T_Vertretungen '.
    "WHERE ($Wofuer='$Anzeige' OR {$Wofuer}_Neu='$Anzeige') AND " .
    "Datum BETWEEN $Montag AND $Freitag");
  $row = mysql_fetch_row($query);
  mysql_free_result($query);
  return $row[0];
}

/**
 * 
 * gibt die Anzahl der vorliegenden Vertretungen zurück
 * Dabei werden freie gewordene Blöcke abgezogen:
 * @return true, wenn eine Vertretung vorhanden ist
 * @return false, wenn keine Vertretung vorhanden ist bzw. nur Ausfall
 *
 */
function isVertretungVorhanden($Wofuer, $Anzeige, $Datum, $Stunde)
{
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE ({$Wofuer}_Neu='$Anzeige' ".
    "OR $Wofuer='$Anzeige') AND Datum=$Datum AND Stunde=$Stunde");
  $vorhanden = false;
  while ($vertretung = mysql_fetch_array($query) )
  {
    if ( $vertretung[$Wofuer.'_Neu'] != '' )
      $vorhanden = true;
  }
  mysql_free_result($query);
  return $vorhanden;
}

/**
  *  Erzeugt ein Feld von Lehrern/Klassen/Räumen die an einem bestimmten Zeitpunkt
  *  (Datum/Stunde) durch Vertretungen frei geworden sind (und normalerweise laut
  *  Stundenplan belegt wären
  * @param $Wofuer Zeichenkette 'Lehrer', 'Klasse' oder 'Raum'
  * @param $Datum das Datum (timestamp)
  * @param $Stunde die Stunde (1-6)
  * @return ein Feld der Befreiten, ein leeres Feld wenn niemand befreit ist.
  */
function holeBefreite($Wofuer, $Datum, $Stunde)
{
  if ( ! is_numeric($Stunde) || ! is_numeric($Datum) ) return array();
  $befreite = array();
  $arbeitende = array();
  $sql = "SELECT DISTINCT $Wofuer, {$Wofuer}_Neu FROM T_Vertretungen ".
    "WHERE Datum=$Datum AND Stunde=$Stunde";
  $query = mysql_query($sql);
  while ($vertretung = mysql_fetch_array($query) )
  {
    if ( $vertretung[$Wofuer.'_Neu'] != '' )
      $arbeitende[] = $vertretung[$Wofuer.'_Neu'];
    //else   // geändert 29.11.2010 else auskommentiert um alle fraglichen Einträge als Befreit zu haben
      $befreite[] = $vertretung[$Wofuer];
  }
  mysql_free_result($query);
  $ergebnis = array();
  foreach ( $befreite as $befreiter )
    if ( ! in_array($befreiter, $arbeitende) )
      $ergebnis[] = $befreiter;
  return $ergebnis;
}

/**
Prüft ob ein Vertretungseintrag zu einem Stundenplaneintrag gehört.
@param $eintrag der Stundenplaneintrag
@param $vertretung der Vertretungsplaneintrag
@return true, wenn die Einträge zusammengehören, false sonst
*/
function isVertretung($eintrag, $vertretung)
{
  $Felder = array('Klasse','Lehrer','Raum','Fach');
  $gleich = true;
  foreach ( $Felder as $Feld )
    if ( $eintrag[$Feld] != $vertretung[$Feld] ) $gleich = false;
  return $gleich;
}

/**
Prüft ob ein Vertretungseintrag ein Ausfall ist.
@param $eintrag der Stundenplaneintrag mit Vertretungen
@return true, wenn die Einträge einen Ausfall anzeigen, false sonst
*/
function isAusfall($eintrag)
{
  $Felder = array('Klasse','Lehrer','Raum','Fach');
  if ( ! is_array($eintrag)) return false;
  $gleich = true;
  foreach ( $Felder as $Feld )
    if ( ! isset($eintrag[$Feld])) 
      $gleich = false;
    else
    foreach ( $eintrag[$Feld] as $wert )
      if ( ohneStern($wert) != '' ) $gleich = false;
  return $gleich;
}

/**
Prüft, ob zusätzliche Einträge im Stundenplan notwendig sind.

Voraussetzungen: Es muss eine aktive Datenbankverbindung bestehen.
Es erfolgt ein Lesezugriff auf T_Vertretungen.

@param $Datum das Datum um das es geht
@param $Block der Block um den es geht
@param $Wofuer Zeichenkette, die angibt wofuer der Plan sein soll
              Zeichenkette:Lehrer,Klasse,Raum
@param $Anzeige - Der Wert des Feldes (also der Lehrer/Klasse/Raum, um den es geht
@param $Vertretungen ein Feld mit den Indizes der Vertretungen, die bereits berücksichtigt
       worden sind. Dieses Feld ergibt sich aus einem array_merge aller mit
       pruefeVertretung erhaltenen ID im Index Vertretungen, z.B.
       if ( isset($eintrag['Vertretungen']) )
         $Vertretungsid = array_merge($Vertretungsid, $eintrag['Vertretungen'])
@return die Einträge im Stundenplanformat als assoziatives Feld:
         Klasse,Lehrer,Fach,Raum,Stunde
         alle Felder beginnen mit einem '*' als Zeichen dass sich was geändert hat.
         zusätzlich nutzbare Felder Bemerkung, Vertretung_id und F_Vertretung_id
         Hinweis: intern gibt es weitere Felder (wichtig für foreach-Schleifen)

Achtung: Es ist darauf zu achten, dass evtl. eine Verschmelzung mit vorhandenen
Stundenplaneinträgen notwendig ist, z.B. bei zusätzlicher Zuordnung eines zweiten
Kollegen.
*/
function pruefeZusatzBlock($Datum, $Block, $Wofuer, $Anzeige, $Vertretungen=array())
{
  $eintraege = array();
  if ( Count($Vertretungen) > 0 )
    $SchonWeg = 'AND Vertretung_id NOT IN ('.implode(',',$Vertretungen).')';
  else
    $SchonWeg = '';
  $SchonWeg .= " AND ($Wofuer='$Anzeige' OR {$Wofuer}_Neu='$Anzeige')";
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE Datum=$Datum AND ".
                       "Stunde=$Block $SchonWeg");
  while ( $eintrag = mysql_fetch_array($query) )
  {
    if ( $Wofuer != '' )
      $Ausfall = ($eintrag[$Wofuer.'_Neu'] != $Anzeige);
    else
      $Ausfall = false;
    
    foreach ( array('Lehrer','Fach','Klasse','Raum') as $Feld )
    {     
      if ( $Ausfall  )
      {
        // Eintrag löschen - nicht betroffen (Ausfall/Vertretung)
        $eintrag[$Feld.'_Org'] = $eintrag[$Feld];
        if ( $eintrag[$Feld.'_Neu'] != $eintrag[$Feld])
          $eintrag[$Feld.'_Ver'] = $eintrag[$Feld.'_Neu'];
        $eintrag[$Feld] = '*';
      }
      else
      {
        $eintrag[$Feld.'_Org'] = '';
        $eintrag[$Feld] = '*'.$eintrag[$Feld.'_Neu'];
        $eintrag[$Feld.'_Neu'] = '*'.$eintrag[$Feld.'_Neu'];
      }
    }
    $eintrag['Bemerkung'] .= '';
    $eintraege[] = $eintrag;
  }
  return $eintraege;
}

/**
  * interne Funktion, ersetzt einen Stundenplaneintrag mit einer Vertretung
  * Alle veränderten Felder haben ein führendes Sternchen '*'
  * Der resultierende Datensatz hat neben den Stundenplanfeldern die zusätzlichen
  * Felder 'Vertretung_id' (id des Vertretungseintrages)
  * 'F_Verhinderung_id' (id der Verhinderung)
  * 'Bemerkung' Bemerkung des Vertretungseintrages
  * @param $eintrag Ein Feld mit dem Stundenplaneintrag aus T_StuPla
  * @param $rs Ein Feld mit dem Vertretungseintrag aus T_Vertretungen
  * @param $Wofuer Zeichenkette, die angibt wofür der zu erstellende Plan ist ('Lehrer'/'Klasse'/'Raum')
  * @param $Anzeige Zeichenkette mit dem Kürzel des Betroffenen -Lehrer/Klasse/Raum)
  * @return ein Feld mit den veränderten Stundenplaneintrag und den zusätzlichen Feldern
  *
  */
function ersetzeStundenplanEintrag($eintrag, $rs, $Wofuer, $Anzeige)
{
  $Felder = array('Lehrer', 'Klasse', 'Raum', 'Fach');
  // Ersetzungen vornehmen
  if ( $Wofuer != '' && $rs[$Wofuer.'_Neu'] != $Anzeige )
  {
    // Eintrag löschen - nicht betroffen (Ausfall/Vertretung)
    foreach ( $Felder as $Feld )
    {
      $eintrag[$Feld.'_Org'] = $eintrag[$Feld];
      if ( $rs[$Feld.'_Neu'] != $eintrag[$Feld])
        $eintrag[$Feld.'_Ver'] = $rs[$Feld.'_Neu'];
      $eintrag[$Feld] = '*';
    }
  }
  else
    foreach ( $Felder as $Feld )
      if ( $eintrag[$Feld] != $rs[$Feld.'_Neu'] )
      {
        $eintrag[$Feld.'_Org'] = $eintrag[$Feld];
        $eintrag[$Feld] = '*'.$rs[$Feld.'_Neu'];
      }
      else
        $eintrag[$Feld.'_Org'] = $eintrag[$Feld];
  $eintrag['Bemerkung'] = $rs['Bemerkung'].''; // Leerer String - Bemerkung ist gesetzt
  $eintrag['Vertretungen'][] = $rs['Vertretung_id'];
  $eintrag['F_Vertretungen'][] = $rs['F_Verhinderung_id'];
  return $eintrag;
}

/**
Prüft ob ein Stundenplaneintrag aufgrund von Vertretungen geändert werden muss.

Voraussetzungen: Es muss eine aktive Datenbankverbindung bestehen.
Es erfolgt ein Lesezugriff auf T_Vertretungen.

@param $eintrag ein Feld mit dem kompletten Eintrag aus dem Stundenplan.
              (im einfachsten Fall per fetch_array geholt)
              Berücksichtigt werden: Turnus, Lehrer, Klasse, Raum, Fach, Stunde
@param $Datum Das Datum, um das es geht als Timestamp
@param $Wofuer Zeichenkette, die angibt wofuer der Plan sein soll
              Zeichenkette:Lehrer,Klasse,Raum
@param $Anzeige - Der Wert des Feldes (also der Lehrer/Klasse/Raum, um den es geht

@return einen Eintrag im Stundenplanformat, bei dem die entsprechenden Felder
         durch die Vertretungen ersetzt sind. In diesem Falle haben die Werte ein
         führendes '*'.
         Fällt der Block aus, enthalten die Felder Lehrer/Klasse/Raum/Fach nur ein '*'.
         Zusätzlich enthält das Feld in diesem Falle die Indizes
         Bemerkung: ein Hinweistext, der im Vertretungsplan angegeben wurde.
         Darüber kann geprüft werden, ob eine Vertretung vorliegt
         isset($eintrag['Bemerkungen']) zeigt an dass (mindestens) eine Änderung vorliegt
         Vertretungen: ein Feld mit den IDs der Vertretungen
         F_Vertretungen: ein Feld mit den F_IDs der Vertretungen
*/
function pruefeVertretung($eintrag, $Datum, $Wofuer='', $Anzeige='')
{
  $Felder = array('Lehrer', 'Klasse', 'Raum', 'Fach', 'Stunde');
  // prüfen. Ggf. geänderte Einträge mit führendem Stern ersetzen
  $sql = 'SELECT * FROM T_Vertretungen WHERE ';
  foreach ($Felder as $Feld )
    $sql .= $Feld."='".$eintrag[$Feld]."' AND ";
  $sql .= 'Datum='.$Datum;
  $query = mysql_query($sql);
  while ( $rs = mysql_fetch_array($query) )
  {
    $eintrag = ersetzeStundenplanEintrag($eintrag, $rs, $Wofuer, $Anzeige);
  }
  mysql_free_result($query);
  return $eintrag;
}
/*----------------------------------------------------------------------------------------*/
//Name              : istTeilungslehrer()
//Kurzbeschreibung  : Ueberprueft, ob der Teilungslehrer fuer eine Vertretung vorgesehen ist 
//                    (und damit kein Teilungslehrer mehr ist)
//Uebergabeparameter: Die Daten des Teilungslehrers ($teil...) und die Informationen der Belegung 
//                    nach dem Hauptlehrer (Klasse, Fach, Raum) als Arrays ($aHaupt). Die Haupt-
//                    belegung sollte schon um Vertretungsinfos ergaenzt sein.
//Rueckgabewert     : true / false
//Fehlerbehandlung  : ---  
function istTeilungslehrer($teilLehrer, $teilFach, $teilKlasse, $teilRaum, $teilTurnus, $Block, $Datum, $aHauptKlasse, $aHauptFach, $aHauptRaum)
{
  $aTeil = array();
  $aTeil['Lehrer'] = $teilLehrer;
  $aTeil['Fach']   = $teilFach;            
  $aTeil['Klasse'] = $teilKlasse;
  $aTeil['Raum']   = $teilRaum;
  $aTeil['Turnus'] = $teilTurnus;
  $aTeil['Stunde'] = $Block;
  
  $aTeil = pruefeVertretung($aTeil, $Datum, 'Lehrer', $teilLehrer);

  //Suche nach Teilung ueber Klasse/Fach
  $findKlasse = false;
  $findFach = false;
  
  foreach($aHauptKlasse as $HauptKlasse)
    if(ohneStern($aTeil['Klasse']) == $HauptKlasse)
      $findKlasse = true;

  foreach($aHauptFach as $HauptFach)
    if(ohneStern($aTeil['Fach']) == $HauptFach)
      $findFach = true;
  
  if($findKlasse && $findFach)
    return true;

  //Suche nach Teilung ueber Raum
  foreach($aHauptRaum as $HauptRaum)
    if(ohneStern($aTeil['Raum']) == $HauptRaum)
      return true;      
  return false;    
}
/*----------------------------------------------------------------------------------------*/
?>
