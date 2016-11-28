<?php
/**
 *  Bietet Möglichkeiten zum Bearbeiten der Vertretungen
 * Zeigt in der Liste normalerweise nur Einträge an, die noch aktuell sind und 
 * innerhalb von 30 Tagen relevant werden. Weiter in der Zukunft liegende oder 
 * in der Vergangenheit liegende Verhinderungen werden nicht angezeigt.
 * Dazu muss ggf. das Datum explizit angegeben werden.
 * 
 * Letzte Änderung: 25.02.06 C. Griep
 * 06.03.06 - Tauschen von Lehrern
 *    
 * Parameter:
 * ZeigeDatum - Zeigt die Verhinderungen am einem Datum an
 * Datum - zeigt den Vertretungsplan für ein bestimmtes Datum an (Blättermöglichkeit)
 * KlasseWoche - zeigt den Klassenplan für eine Vertretung zu einer bestimmten Woche an
 * 
 * (c) 2006 Christoph Griep
 */
$Ueberschrift = 'Vertretung planen';
define('USE_KALENDER', 1);
define('USE_OVERLIB', 1);
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/stupla.css">
<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszframe.css">
<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/stupla.css">
<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
include('include/header.inc.php');
include('include/helper.inc.php');
include('include/stupla.inc.php');
include('include/Lehrer.class.php');
include('include/Vertretungen.inc.php');
include('include/Vertretungsliste.inc.php');
include_once('include/Abteilungen.class.php');

$Abteilungen = new Abteilungen($db);

echo "<tr><td>\n";

// Setzt die Anzeige für die händische Vertretungsregelung zurück
session_unregister('DerEintrag');
session_unregister('DieAnzeige');

$Felder = array('Lehrer', 'Raum', 'Klasse');
// Parameter auswerten
$_SESSION['JetztDatum'] = time();
if ( ! session_is_registered('ListeVerhinderung'))
    $_SESSION['ListeVerhinderung'] = array();   
  
if ( isset($_REQUEST['ZeigeDatum']))
{
	$Datum = explode('.', $_REQUEST['ZeigeDatum']);
	if ( checkdate($Datum[1],$Datum[0],$Datum[2]) )
    {
      $_SESSION['JetztDatum'] = mktime(0,0,0,$Datum[1],$Datum[0],$Datum[2]);      
    }
    else
      echo '<div class="Fehler">Es wurde kein gültiges Datum eingegeben!</div>';
}
if ( isset($_REQUEST['KlasseWoche']) && is_numeric($_REQUEST['KlasseWoche']))
{
	$_SESSION['KlasseWoche'] = $_REQUEST['KlasseWoche'];
}
if ( isset($_REQUEST['Datum']) )
{
  if ( ! is_numeric($_REQUEST['Datum']) )
  {
    $Datum = explode('.',$_REQUEST['Datum']);
    if ( checkdate($Datum[1],$Datum[0],$Datum[2]) )
    {
      $_SESSION['Datum'] = mktime(0,0,0,$Datum[1],$Datum[0],$Datum[2]);
      $_SESSION['Woche'] = getID_Woche($_SESSION['Datum']);
      $_SESSION['Stunde'] = 1;
    }
    else
      echo '<div class="Fehler">Es wurde kein gültiges Datum eingegeben!</div>';
  }
  else
  {
    $_SESSION['Datum'] = $_REQUEST['Datum'];
    $_SESSION['Woche'] = getID_Woche($_SESSION['Datum']);
    $_SESSION['Stunde'] = 1;
  }
  $_SESSION['KlasseWoche'] = $_SESSION['Woche'];
  $_SESSION['AnzeigeKlasse'] = true;
  $_SESSION['AnzeigeUnterricht'] = false;
  $_SESSION['AnzeigeFach'] = false;
  $_SESSION['AnzeigeAnschliessend'] = false;
  $_SESSION['AnzeigeLehrer'] = false;
  session_unregister('LastParams');
}
if ( isset($_REQUEST['Stunde']) && is_numeric($_REQUEST['Stunde']) &&
     session_is_registered('Datum') )
{
  if ( $_SESSION['Stunde'] != $_REQUEST['Stunde'] )
    session_unregister('LastParams');
  $_SESSION['Stunde'] = $_REQUEST['Stunde'];
}

if ( isset($_REQUEST['Verhinderung_id']) && is_numeric($_REQUEST['Verhinderung_id']) 
     && session_is_registered('Datum') )
{
  $query = mysql_query('SELECT * FROM T_Verhinderungen '.
    "WHERE Verhinderung_id={$_REQUEST["Verhinderung_id"]}");
  if( $verhinderung = mysql_fetch_array($query) )
  {
    $_SESSION['Verhinderung_id'] = $verhinderung['Verhinderung_id'];
    $_SESSION['Wofuer'] = $verhinderung['Wer'];
    $_SESSION['Art'] = $verhinderung['Art'];
    $_SESSION['Hinweis'] = $verhinderung['Hinweis'];
    $_SESSION['Von'] = $verhinderung['Von'];
    $_SESSION['Bis'] = $verhinderung['Bis'];
    $_SESSION['Grund'] = $verhinderung['Grund'];
    $_SESSION['Stand'] = $verhinderung['Stand'];
    $_SESSION['Bearbeiter'] = $verhinderung['Bearbeiter'];
    for ( $i = 6; $i >= 1; $i--)
    {
      if ( $verhinderung["UE$i"] ) $_SESSION['Stunde']=$i;
      $_SESSION['UE'][$i] = $verhinderung["UE$i"];
    }
    session_unregister('LastParams');
  }
  mysql_free_result($query);  
}
if ( isset($_REQUEST['Save']) )
{
  session_unregister('LastParams');
  $_SESSION['AnzeigeKlasse'] = false;
  $_SESSION['AnzeigeUnterricht'] = false;
  $_SESSION['AnzeigeFach'] = false;
  $_SESSION['AnzeigeAnschliessend'] = false;
  $_SESSION['AnzeigeLehrer'] = false;
  if ( isset($_REQUEST['Anzeige']) && is_array($_REQUEST['Anzeige']) )
    foreach ( $_REQUEST['Anzeige'] as $key => $value )
      $_SESSION["Anzeige$key"] = true;
}
if ( isset($_REQUEST['Liste']) )
{
  switch ( $_REQUEST['Liste'] )
  {
    case 0:
    case 1: // Reset - die Anzeige der unvollständigen Verhinderungen aktivieren
  	   $Liste = $_SESSION['ListeVerhinderung']; 
  	   session_unset();  	   
  	   $_SESSION['JetztDatum'] = time();
  	   if ( $_REQUEST['Liste'] != 0 ) $_SESSION['ListeVerhinderung'] = $Liste;
  	   else
  	   // Löschen des Cache
  	     mysql_query('DELETE FROM T_Vertretung_Liste');
            break;
    case 2: $_SESSION['AnzeigeLehrer'] = true;
  } // switch
}

// Vertretungen speichern
if ( session_is_registered('Datum') && session_is_registered('Verhinderung_id') &&
     session_is_registered('Stunde') &&
     isset($_REQUEST['Art']) && is_numeric($_REQUEST['Art']) )
{
  if ( isset($_REQUEST['Neu'])) $_REQUEST['Neu'] = ohneStern($_REQUEST['Neu']);
  if ( session_is_registered('LastParams') && 
       $_SESSION['LastParams'] == $_SERVER['QUERY_STRING'] )
    echo '<div class="Fehler">Fehler: Sie haben diese Daten bereits gespeichert!</div>';
  else
  {
    // Cache leeren 
    mysql_query('DELETE FROM T_Vertretung_Liste WHERE F_Verhinderung_id='.$_SESSION['Verhinderung_id']);
    // Vertretung abspeichern
    if ( $_REQUEST['Art'] != VERTRETUNG_ENTFERNEN )
    {
      switch ( $_REQUEST['Art'] )
      {
        case VERTRETUNG_TAUSCH:
          // Austausch der Lehrer, Neu enthält den neuen Lehrer:Klasse
          // Geht nur wenn ein Lehrer ausgewählt ist (SESSION).
          $Neu = explode(':',$_REQUEST['Neu']);
          $Lehrer = $Neu[0];
          $Klasse = $Neu[1];
          trageVertretungEin('Lehrer', $_SESSION['Wofuer'], $_SESSION['Datum'], 
              $_SESSION['Stunde'], array('Lehrer' => $Lehrer) ,
              0, '', $_SESSION['Verhinderung_id'],
              $_REQUEST['Art']);
          trageVertretungEin('Klasse', $Klasse, $_SESSION['Datum'], 
              $_SESSION['Stunde'], array('Lehrer' => $_SESSION['Wofuer']),
              0, '', $_SESSION['Verhinderung_id'],
              $_REQUEST['Art']);          
          break;
        case VERTRETUNG_AUSFALLOHNEBERECHNUNG:
          // Überall leer eintragen. Feld Neu enthält die Klasse oder Lehrer
          // Separiert durch : z.B. Lehrer:XXX
          $Neu = explode(':',$_REQUEST['Neu']);
          if ( $Neu[1] != '' && ($Neu[0] == 'Lehrer' || $Neu[0] == 'Klasse') )
            trageVertretungEin($Neu[0], $Neu[1], 
              $_REQUEST['VonDatum'], $_REQUEST['VonStunde'],
              array('Fach'=>'','Lehrer'=>'','Raum'=>'','Klasse'=>''),
              0, '', $_SESSION['Verhinderung_id'],
              $_REQUEST['Art']);
          break;          
        case VERTRETUNG_AUSFALL:
        case VERTRETUNG_TEILUNGAUSFALL:
          $Hinweis = '';
          if ( $_REQUEST['Art'] == VERTRETUNG_TEILUNGAUSFALL )
            $Hinweis = 'nur Gruppe '.$_SESSION['Wofuer'].' fällt aus';
          // Überall leer eintragen. Feld Neu wird nicht beachtet.
          trageVertretungEin($_SESSION['Art'], $_SESSION['Wofuer'], $_SESSION['Datum'], $_SESSION['Stunde'],
            array('Fach'=>'','Lehrer'=>'','Raum'=>'','Klasse'=>''),
            $_SESSION['Grund'], $Hinweis, $_SESSION['Verhinderung_id'],
            $_REQUEST['Art']);
          break;
        case VERTRETUNG_VERLEGUNGOHNEAUSFALLMITRAUMWECHSEL:
          // zunächst fällt der Block aus, dann wird ein neuer Block hinzugefügt
          $eintraegeAlt = liesStundenplanEintrag($_SESSION['Art'], $_SESSION['Wofuer'],
            $_SESSION['RaumwechselDatumOrg'], $_SESSION['RaumwechselStundeOrg']);
          // Hinzufügen an neuer Stelle
          foreach ($eintraegeAlt as $eintrag )
          {
            $ersetzen = array('Fach'=>$eintrag['Fach'],'Lehrer'=>$eintrag['Lehrer'],
              'Raum'=>$_REQUEST['Neu'],'Klasse'=>$eintrag['Klasse']);
            trageVertretungEin($_SESSION['Art'], $_SESSION['Wofuer'], 
              $_SESSION['Datum'],
              $_SESSION['Stunde'], $ersetzen, $_SESSION['Grund'], 'vom '.
              date('d.m.Y',$_SESSION['RaumwechselDatumOrg']).' '.
              $_SESSION['RaumwechselStundeOrg'].'. Block verlegt',
                $_SESSION['Verhinderung_id'], $_REQUEST['Art']);
          }
          // Entfernen an alter Stelle
          trageVertretungEin($_SESSION['Art'], $_SESSION['Wofuer'], $_SESSION['RaumwechselDatumOrg'],
            $_SESSION['RaumwechselStundeOrg'],
            array('Fach'=>'','Lehrer'=>'','Raum'=>'','Klasse'=>''),
            $_SESSION['Grund'], 'zum '.date('d.m.Y',$_SESSION['Datum']).' '.
            $_SESSION['Stunde'].'. Block verlegt', $_SESSION['Verhinderung_id'],
            VERTRETUNG_VERLEGUNGOHNEAUSFALLMITRAUMWECHSEL);          
          break;          
        case VERTRETUNG_VERLEGUNGOHNEAUSFALL:
          // zunächst fällt der Block aus, dann wird ein neuer Block hinzugefügt
          $eintraegeAlt = liesStundenplanEintrag($_SESSION['Art'], $_SESSION['Wofuer'],
            $_SESSION['Datum'], $_SESSION['Stunde']);
          // Hinzufügen an neuer Stelle
          foreach ($eintraegeAlt as $eintrag )
          {
            $ersetzen = array('Fach'=>$eintrag['Fach'],'Lehrer'=>$eintrag['Lehrer'],
              'Raum'=>$eintrag['Raum'],'Klasse'=>$eintrag['Klasse']);
            trageVertretungEin($_SESSION['Art'], $_SESSION['Wofuer'], $_REQUEST['VonDatum'],
              $_REQUEST['VonStunde'], $ersetzen, $_SESSION['Grund'], 'vom '.
              date('d.m.Y',$_SESSION['Datum']).' '.$_SESSION['Stunde'].'. Block verlegt',
                $_SESSION['Verhinderung_id'], $_REQUEST['Art']);
          }
          // Entfernen an alter Stelle
          trageVertretungEin($_SESSION['Art'], $_SESSION['Wofuer'], $_SESSION['Datum'],
            $_SESSION['Stunde'],
            array('Fach'=>'','Lehrer'=>'','Raum'=>'','Klasse'=>''),
            $_SESSION['Grund'], 'zum '.date('d.m.Y',$_REQUEST['VonDatum']).' '.
            $_REQUEST['VonStunde'].'. Block verlegt', $_SESSION['Verhinderung_id'],
            VERTRETUNG_VERLEGUNGOHNEAUSFALL);
          
          break;
        case VERTRETUNG_VERLEGUNGMITRAUM:
        case VERTRETUNG_VERLEGUNG:
          // zunächst fällt der Block aus, dann wird ein neuer Block hinzugefügt
          $eintraegeAlt = liesStundenplanEintrag($_SESSION['Art'], $_SESSION['Wofuer'],
            $_SESSION['Datum'], $_SESSION['Stunde']);
          $eintraegeNeu = liesStundenplanEintrag('Lehrer', $_REQUEST['Neu'],
            $_REQUEST['VonDatum'], $_REQUEST['VonStunde']);
          // Hinzufügen an neuer Stelle
          foreach ($eintraegeNeu as $eintrag )
          {
            $ersetzen = array('Fach'=>$eintrag['Fach'],'Lehrer'=>$eintrag['Lehrer']);
            if ( $_REQUEST['Art'] == VERTRETUNG_VERLEGUNGMITRAUM )
              $ersetzen = array_merge($ersetzen, array('Raum'=>$eintrag['Raum']));
            trageVertretungEin('Lehrer', $_SESSION['Wofuer'], $_SESSION['Datum'],
              $_SESSION['Stunde'], $ersetzen, $_SESSION['Grund'], 'vom '.
              date('d.m.Y',$_REQUEST['VonDatum']).' '.$_REQUEST['VonStunde'].
              '. Block verlegt',              
              $_SESSION['Verhinderung_id'], $_REQUEST['Art']);
          }
          // Entfernen an alter Stelle
          trageVertretungEin('Lehrer', $_REQUEST['Neu'], $_REQUEST['VonDatum'],
            $_REQUEST['VonStunde'],
            array('Fach'=>'','Lehrer'=>'','Raum'=>'','Klasse'=>''),
            $_SESSION['Grund'], 
            'zum '.date('d.m.Y',$_SESSION['Datum']).' '.$_SESSION['Stunde'].
            '. Block verlegt',  $_SESSION['Verhinderung_id'],
            VERTRETUNG_AUSFALL);
          break;
        case VERTRETUNG_RAUMWECHSEL:
           // wie gehabt, neuen Raum eintragen
           // prüfen, ob ein Raumwechsel vorliegt, dann auf jeden Fall Raum ändern
           $NeuArray = array('Raum'=>$_REQUEST['Neu']);
           if ( $_SESSION['Art'] != 'Raum')
           {
           	  $eintraege = liesStundenplanEintragMitVertretung($_SESSION['Art'], 
           	    $_SESSION['Wofuer'], $_SESSION['Datum'], $_SESSION['Stunde']);
           	  // Teilungslehrer überprüfen und auch in den neuen Raum stecken
           	  foreach ($eintraege as $eintrag )
           	  {  
           	    $stunden = holeTeilungsStunden($_SESSION['Datum'], $_SESSION['Stunde'], 
           	      $eintrag);
           	    foreach ( $stunden as $stunde )
           	    {
           	    	// allen Teilungslehrern den neuen Raum zuweisen
           	    	trageVertretungEin('Lehrer', $stunde['Lehrer'], $_SESSION['Datum'],
                       $_SESSION['Stunde'], $NeuArray, $_SESSION['Grund'],
                       '', $_SESSION['Verhinderung_id'], VERTRETUNG_RAUMWECHSEL);
           	    }
           	  }           	  
           }
           trageVertretungEin($_SESSION['Art'], $_SESSION['Wofuer'], $_SESSION['Datum'],
             $_SESSION['Stunde'], $NeuArray, $_SESSION['Grund'],
             '', $_SESSION['Verhinderung_id'], $_REQUEST['Art']);
           break;        
        case VERTRETUNG_ANDERERUNTERRICHT:
          // Teilungslehrer geht in andere Klasse
          // 1. bei NEU in der regulären Klasse Teilung aufheben
          // Stundenplan des neuen holen
          $eintraege = liesStundenplanEintragMitVertretung('Lehrer', 
          	    $_REQUEST['Neu'], $_SESSION['Datum'], $_SESSION['Stunde']);
          foreach ($eintraege as $eintrag )
          {  
            if ( ohneStern($eintrag['Klasse']) != '' )
            {
              // diese Stunden als Teilung aufheben eintragen
              // dazu festellen, wer der Teilungslehrer ist
              $stunden = holeTeilungsStunden($_SESSION['Datum'], $_SESSION['Stunde'], 
          	      $eintrag);
          	  $Teilungslehrer = reset($stunden);
              $Teilungslehrer = $Teilungslehrer['Lehrer'];
              // allen Teilungslehrern den neuen Raum zuweisen
              // Der Grund muss 0 sein, damit das ganze nicht doppelt gezählt wird
              if ($Teilungslehrer != '')
                 trageVertretungEin('Lehrer', $_REQUEST['Neu'], $_SESSION['Datum'],
                    $_SESSION['Stunde'], $Teilungslehrer, 0, // $_SESSION['Grund'],
                    '', $_SESSION['Verhinderung_id'], VERTRETUNG_TEILUNGAUFHEBEN);
           	}
          }           	  
          // 2. NEU als Vertretung einsetzen (passiert im folgenden automatisch)   
        case VERTRETUNG_TEILUNGAUFHEBEN:
          if ( isset($_REQUEST['VonDatum']))
          {
          	// Sonderfall: Teilung eines fremden Kollegen wird aufgehoben
          	// Neu enthält hier den Kollegen in der Form 'Lehrer:XXX', der frei wird
          	$Lehrer = explode(':',$_REQUEST['Neu']);
          	$eintraege = liesStundenplanEintragMitVertretung('Lehrer', 
          	    $Lehrer[1], $_REQUEST['VonDatum'], $_REQUEST['VonStunde']);
          	foreach ( $eintraege as $eintrag )
          	{
          	  $teintraege = holeTeilungsStunden($_REQUEST['VonDatum'], 
          	    $_REQUEST['VonStunde'], $eintrag);
          	  if ( Count($teintraege) > 0 && isset($teintraege[0]['Lehrer']))
          	  {
          	    $Teilungslehrer = $teintraege[0]['Lehrer'];
          	    trageVertretungEin('Lehrer',$Lehrer[1],$_REQUEST['VonDatum'],
          	        $_REQUEST['VonStunde'], $Teilungslehrer, 0, '', 
          	        $_SESSION['Verhinderung_id'], VERTRETUNG_TEILUNGAUFHEBEN);
          	  }  
          	}
          	break;
          }
        case VERTRETUNG_ZUSATZKLASSE:
          // im Datensatz von Lehrer wird der Teilungslehrer eintragen. Neu muss den
          // Teilungslehrer enthalten
        case VERTRETUNG_VERTRETEN:
         // wie gehabt, neuen Lehrer eintragen
         if ( ! isset($NeuArray)) $NeuArray = array($_SESSION['Art']=>$_REQUEST['Neu']);
         trageVertretungEin($_SESSION['Art'], $_SESSION['Wofuer'], $_SESSION['Datum'],
           $_SESSION['Stunde'], $NeuArray, $_SESSION['Grund'],
           '', $_SESSION['Verhinderung_id'], $_REQUEST['Art']);
         break;
         default:
           dieMsg('falscher Arbeits-Parameter angegeben!');
      } // switch
      echo "<div class=\"Hinweis\">Vertretung für {$_SESSION["Art"]} {$_SESSION["Wofuer"]} am ".
        date('d.m.Y', $_SESSION['Datum'])." im {$_SESSION['Stunde']} Block gespeichert</div>";
    }
    elseif ( isset($_REQUEST['ID']) )
    {
      // Vertretung muss entfernt werden
      foreach ( explode(',',$_REQUEST['ID']) as $ID )
        if ( is_numeric($ID) ) entferneVertretung($ID);
      echo '<div class="Hinweis">Vertretung entfernt</div>';
    }
    else
       dieMsg('falsche Parameter angegeben!');
    $_SESSION['LastParams'] = $_SERVER['QUERY_STRING'];
  }
} // Vertretung speichern

// Beginn des eigentlichen Formulars

// 1. Schritt: Auswahl, für wen eine Vertretung erfolgen soll
if ( !session_is_registered('Datum') )
{
  ?>
<script language="javascript">
  function Anzeigen(was)
  {    
  	if ( was == "")
  	  document.getElementById("Styles").innerHTML = ""; 
  	else  	
  	  document.getElementById("Styles").innerHTML = ".Tag { visibility: hidden; } " +
  	     ".Tag[name=AC"+was+"] { visibility: visible; }";
  }
</script>
<style type="text/css" id="Styles">
  
</style>
  <?php
  echo '<br />';
  
  
  echo "<div class=\"Hinweis\">Angezeigt werden Verhinderungen ab ".
    date('d.m.Y',$_SESSION['JetztDatum']).'</div>';
  echo "<table class=\"Liste\">\n";
  echo '<tr><th>Art</th><th>Name</th><th>Von</th><th>Bis</th><th>Grund / Hinweis</th>';
  echo "<th>vorh.<br />Änderungen</th><th>Betroffen";  
  for ( $i = time(); $i < strtotime('+5 day'); $i = strtotime('+1 day', $i))
    echo ' <a href="javascript:Anzeigen(\''.date('dm',$i).
         '\')" title="Nur Betroffene am '.date('d.m.',$i).' anzeigen">'.date('d',$i).'</a> ';
  echo '<a href="javascript:Anzeigen(\'\')" title="alle Betroffenen anzeigen">A</a></th></tr>'."\n";
  $query = mysql_query('SELECT * FROM T_Verhinderungen WHERE Bis >= '.
    strtotime('-1 day',$_SESSION['JetztDatum']).' AND Von <= '.
    strtotime('+30day',$_SESSION['JetztDatum']).' ORDER BY Art, Wer, Von');
  if ( isset($_REQUEST['Betroffen']))
    $_SESSION['Betroffen'] = $_REQUEST['Betroffen'];
  else
    $_SESSION['Betroffen'] = 'A';  
  $dieWoche = getID_Woche();
  if ( $dieWoche == getID_Woche($_SESSION['JetztDatum']))
    $useCache = true;
  else
    $useCache = false;
  while ( $eintrag = mysql_fetch_array($query) )
  {
  	if ( $useCache )
  	{  	  
  	  $cache = mysql_query('SELECT * FROM T_Vertretung_Liste ' .
  	  		'WHERE F_Verhinderung_id='.$eintrag['Verhinderung_id']);
  	  if ( $cacheeintrag = mysql_fetch_array($cache)) 
  	  {
  		//   Cache Vorhanden - Woche zu klein?
  		  if ( $cacheeintrag['Woche'] < $dieWoche )
  		  {
  		    unset($cacheeintrag);
  		    // zu alt: Eintrag löschen
  		    mysql_query('DELETE FROM T_Vertretung_Liste ' .
  		    		'WHERE F_Verhinderung_id='.$eintrag['Verhinderung_id']);
  		  }  	  
  	  }  	 
  	  else
  	    unset($cacheeintrag);
  	  mysql_free_result($cache);
  	}
  	else
  	  unset($cacheeintrag);
  	// Prüfen, ob ein Eintrag in der Liste neu berechnet werden muss
    $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']] = array();
    if ( ! isset($cacheeintrag))
    {
        $Wochen = array();   
        $ID_Woche = getID_Woche($eintrag['Von']);
        $Wochen[] = $ID_Woche;      
        for ( $i = getMontag($ID_Woche); $i <= $eintrag['Bis']; $i=strtotime('+7 day',$i) )
        {
          $ID_Woche = getID_Woche($i);
          if ( ! in_array($ID_Woche, $Wochen) ) $Wochen[] = $ID_Woche;
        }   
        $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Wochen'] = $Wochen;
    }
    echo '<tr>'."\n";
    echo '<td>'.$eintrag['Art'].'</td><td><a href="../StuPla/PlanAnzeigen.php?'.
        $eintrag['Art'].'='.$eintrag['Wer'].'&Woche=';
    echo '" target="_blank" title="Stundenplan anzeigen">';
    $Lehrername = KuerzelToLehrer($eintrag['Wer'], LEHRERID_KUERZEL);
    echo $Lehrername['Name'];
    if ( $Lehrername['Vorname']!= '' ) echo '<br />'.$Lehrername['Vorname'];
    echo "</a></td>\n";
    echo '<td>';
    echo '<a href="../Verhinderung/Verhinderung.php?Verhinderung_id='.
      $eintrag["Verhinderung_id"];
    echo '#Link'.$eintrag['Art'].'" title="Verhinderung (Zeitraum, Grund) bearbeiten">';
    echo date('d.m.Y',$eintrag['Von']);
    echo '</a></td><td>'.date('d.m.Y',$eintrag['Bis']).'</td>'."\n";
    echo '<td>';
    $alle = true;
    $anzeige = array();
    for ( $i = 1; $i < 7; $i++ )
    {
    	if ( $eintrag['UE'.$i] ) 
  	    $anzeige[] = $i;
  	  else
    	  $alle = false; 
    }
    if ( !$alle )
    {
  	  echo '<div style="font-style:italic">';
  	  if ( Count($anzeige) == 1)
    	  echo 'Block: ';
  	  else
        echo 'Blöcke: ';
      echo implode(',',$anzeige).'</div>';
    }
    echo $Gruende[$eintrag['Grund']];        
    if ( trim($eintrag['Hinweis']) != '' ) 
      echo '<br/>'.nl2br(stripslashes($eintrag['Hinweis']))."\n";
    echo '<br /><span class="mini">'.$eintrag['Bearbeiter'].'/'.$eintrag['Stand'].'</small>';
    echo "</td>\n";
    echo '<td>';
    // wer ist so alles betroffen        
    if ( ! isset($cacheeintrag))
    //if ( !isset($_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Stand']))
    {
      $vquery = mysql_query('SELECT * FROM T_Vertretungen WHERE '. 
        " F_Verhinderung_id={$eintrag['Verhinderung_id']}" .
       ' AND Datum > '.strtotime('-1day',$_SESSION['JetztDatum']));
      $Klassen = array();
      $Lehrer = array();
      $Ausfall = 0;
      $Da = false;
      $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Stand'] = 0;
      while ( $veintrag = mysql_fetch_array($vquery) )
      {
      	// Keine leeren Klassen zulassen
        if ( $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]
             ['Stand'] < strtotime($veintrag['Stand'])) 
             $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]
             ['Stand'] = strtotime($veintrag['Stand']);
        if ( $veintrag['Klasse'] == '' )
          $veintrag['Klasse'] = '(Zusatz)';  
        if ( $veintrag['Lehrer_Neu'] == '' && $veintrag['Klasse_Neu'] != '' )
        {
          $zeintrag = liesStundenplanEintragMitVertretung('Klasse',
                $veintrag['Klasse_Neu'], $veintrag['Datum'], $veintrag['Stunde']);
          foreach ($zeintrag as $turnus )
            if ( $turnus['Lehrer'] != '*' && $turnus['Lehrer'] != $veintrag['Lehrer'] &&
                 $turnus['Lehrer'] != '' )
              $veintrag['Lehrer_Neu'] = $turnus['Lehrer'];
          if ( $veintrag['Lehrer_Neu'] == '' ) $veintrag['Lehrer_Neu'] = '(Zusatz)';
        }

        if ( ! isset($Klassen[$veintrag['Klasse']]) )
          $Klassen[$veintrag['Klasse']] = 0;
        $Klassen[$veintrag['Klasse']]++;
        if ( $eintrag['Art'] == 'Klasse' && $veintrag['Lehrer_Neu'] == '' )
        {
          if ( ! isset($Lehrer[$veintrag['Lehrer']]) )
            $Lehrer[$veintrag['Lehrer']] = 0;
          $Lehrer[$veintrag['Lehrer']]++;
        }
        if ( $veintrag['Lehrer_Neu'] != '' )
        {
          if ( ! isset($Lehrer[$veintrag['Lehrer_Neu']]) )
            $Lehrer[$veintrag['Lehrer_Neu']] = 0;
          $Lehrer[$veintrag['Lehrer_Neu']]++;
        }
        else
          $Ausfall++;
        $Da = true;
      }
      mysql_free_result($vquery);
      $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Klassen'] = $Klassen;
      $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Lehrer'] = $Lehrer;
      $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Ausfall'] = $Ausfall;
      $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Da'] = $Da;
    }
    
    if ( isset($cacheeintrag) )
    {
    	echo $cacheeintrag['Aenderungen'].'</td><td>'.$cacheeintrag['Betroffen'];
    }
    else
    {
      $zeile = '';
      if ( $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Da'] )
       $zeile .= '<a href="../StuPla/Vertretungsplan.php?'.$eintrag['Art'].
        "={$eintrag['Wer']}&Woche=".implode(',',
        $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Wochen']).
        '" target="_blank">'.
          $eintrag['Wer']."</a><br />\n";
      // Bei Prüfung wird nur die betroffene Klasse angezeigt
      if ( $eintrag['Grund'] != KLASSEPRUEFUNG )
      {
        foreach ( $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Lehrer'] as $name => $anz )
        {
          $zeile .= '<a href="../StuPla/Vertretungsplan.php?Lehrer='.$name;
          $zeile .= "&Woche=".implode(',',$_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Wochen']).
            '" target="_blank" title="Vertretungsplan '.$name.'">';
          $zeile .= $name.'</a>';
          if ( $anz > 1 ) $zeile .= " ($anz)";
          $zeile .= "<br />\n";
        }
        foreach ( $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Klassen'] as $name => $anz )
        {
          $zeile .= '<a href="../StuPla/Vertretungsplan.php?Klasse='.$name.'&Woche=';
          $zeile .= implode(',',$_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Wochen']).
               '" target="_blank" title="Vertretungsplan '.$name.'">';
          $zeile .= $name.'</a>';
          if ( $anz > 1 ) $zeile .= " ($anz)";
          $zeile .= "<br />\n";
        }       
        if ( $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Ausfall'] > 0 )
          $zeile .= "Ausfall: {$_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Ausfall']}<br />\n";
      } // wenn nicht Prüfung
      // Nur wenn es Einträge gibt 
      if ( $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Ausfall']+
           Count($_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Klassen'])+
           Count($_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Lehrer']) > 0 )
      {
        if ( in_array(getID_Woche(time()), 
             $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Wochen']) )
          $zeile .= '<a href="../StuPla/Vertretungsplan.php?Verhinderung_id='.
            $eintrag['Verhinderung_id'].
            '&Tag='.time().'" target="_blank" title="aktueller Vertretungsplan' .
      		    ' für alle Betroffenen">' .
      		    '<img src="Raster.png" alt="Raster"/></a> ';
        
        for ( $i=1;$i<5;$i++)
        {
          $nextDay = strtotime('+'.(7*$i).' days',time());
          if ( in_array(getID_Woche($nextDay), 
               $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['Wochen']) )        
            $zeile .= ' <a href="../StuPla/Vertretungsplan.php?Verhinderung_id='.
              $eintrag['Verhinderung_id'].
              '&Tag='.$nextDay.'" target="_blank" title="' .
        		    'Vertretungsplan für alle Betroffenen in '.$i.' Wochen">' .
      		    '<span class="small">'.$i.'</span><img src="Raster.png" alt="N-Raster" /></a>';
        }
      } // wenn Vertretungseinträge vorhanden 
      echo $zeile;
      echo '</td>';
      echo "<td>\n";
      $betroffen = '';
      //   Anzahl der betroffenen Stunden
      $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['betroffen'] = 
          berechneStunden($db, $eintrag); 
      $betroffen = bestimmeBetroffeneAlsHTML(
          $_SESSION['ListeVerhinderung'][$eintrag['Verhinderung_id']]['betroffen'], 
          $zeile != '', $eintrag, $Abteilungen);              
      echo $betroffen;    
      if ( $useCache )
        mysql_query('INSERT INTO T_Vertretung_Liste (F_Verhinderung_id, Woche, Aenderungen, ' .
    		'Betroffen) VALUES ('.$eintrag['Verhinderung_id'].','.$dieWoche.",'".
    		mysql_real_escape_string($zeile)."','".mysql_Real_escape_string($betroffen)."')");
    }
    echo "</td>\n";
    echo "</tr>\n";
  }
  mysql_free_result($query);
  echo "</table>\n";
  echo '<p><a href="../Verhinderung/Verhinderung.php">Neue Verhinderung eingeben.</a>';
  echo '<form action="'.$_SERVER["PHP_SELF"].'" name="SEingabe" methode="post">';
  echo 'Spezialverhinderung für ';
  echo '<select name="Speziallehrer">';
  $Version = getAktuelleVersion();
  $sql = "SELECT DISTINCT Lehrer, Name, Vorname FROM T_StuPla " .
         	   		"WHERE Version=$Version ORDER BY Name,Vorname";
  $query = mysql_query($sql);
  while ( $lehrer = mysql_fetch_array($query))
    echo '<option value="'.$lehrer['Kuerzel'].'">'.$lehrer['Name'].', '.
      $lehrer['Vorname'].'</option>';
  mysql_free_result($query);     	 
  echo '</select>'." am \n";
  echo '<input type="text" name="ZeigeDatum" value="'.date("d.m.Y", $_SESSION['JetztDatum']);
  echo '" onClick="popUpCalendar(this,SEingabe[';
  echo "'ZeigeDatum'],'dd.mm.yyyy')\" ";
  echo 'onBlur="autoCorrectDate';
  echo "('SEingabe','ZeigeDatum',false)\" ";
  echo "/>\n";
  echo '<input type="submit" value="erstellen" /></form>';
  echo '&nbsp;&nbsp;<form action="'.$_SERVER["PHP_SELF"].'" name="Eingabe" methode="post">';
  echo "Verhinderungen ab Datum \n";
  echo '<input type="text" name="ZeigeDatum" value="'.date("d.m.Y");
  echo '" onClick="popUpCalendar(this,Eingabe[';
  echo "'ZeigeDatum'],'dd.mm.yyyy')\" ";
  echo 'onBlur="autoCorrectDate';
  echo "('Eingabe','ZeigeDatum',false)\" ";
  echo "/>\n"; 
  echo '<input type="submit" value="anzeigen" /></form>';
  echo "</p>\n";
  
}
else
{ // Datum ist registriert - wir planen Vertretung
  $derLehrer = KuerzelToLehrer($_SESSION['Wofuer']);
  $LehrerName = $derLehrer['Name'];
  $LehrerVorname = $derLehrer['Vorname']; 
  if ( $LehrerVorname != '' ) $LehrerName = $LehrerVorname." ".$LehrerName;
  echo '<h1>Vertretungsplanung für '.$_SESSION["Art"].' '.$LehrerName.' ';
  echo 'am '.date("d.m.Y",$_SESSION['Datum'])."</h1>\n";
  if ( $_SESSION['Datum'] < $_SESSION['Von'] || 
       $_SESSION['Datum'] > $_SESSION['Bis'])
       echo '<div class="Fehler">Das Datum liegt außerhalb der Verhinderung!</div>';
  if ( session_is_registered('Stunde') && !$_SESSION['UE'][$_SESSION['Stunde']])
       echo '<div class="Fehler">Dieser Block liegt außerhalb der Verhinderung!</div>';
  echo '<div class="small">Aufgenommen von '.$_SESSION['Bearbeiter'].' am '.
    $_SESSION['Stand']."</div>\n";
  echo '<strong>Gesamte Fehlzeit:</strong> '.date("d.m.Y",$_SESSION['Von']);
  if ( $_SESSION['Von'] != $_SESSION['Bis'])
    echo ' bis '.date('d.m.Y',$_SESSION['Bis']).' ('.
      ceil(($_SESSION['Bis']-$_SESSION['Von'])/86400+1).' Tage)';
  $alle = true;
  $anzeige = array();
  for ( $i = 1; $i < 7; $i++ )
  {
  	if ( $_SESSION['UE'][$i] ) 
  	  $anzeige[] = $i;
  	else
  	  $alle = false; 
  }
  if ( !$alle )
  {
  	echo ' &rarr; <span style="font-style:italic">Gilt nur für ';
  	if ( Count($anzeige) == 1)
  	  echo 'Block: ';
  	else
      echo 'die Blöcke: ';
    echo implode(',',$anzeige).'</span>';
  }      
  echo "<br />\n";
  echo '<strong>Grund:</strong> ';
  echo $Gruende[$_SESSION['Grund']];
  if ( trim($_SESSION['Hinweis']) != '' )
    echo '<br /><strong>Hinweise:</strong> '.nl2br($_SESSION["Hinweis"]);
  echo '<br/><hr />'."\n";
  // Vertretung organisieren
  include_once('include/Vertretungbehandeln.inc.php');
  switch ( $_SESSION['Art'] )
  {
      case 'Klasse':
        // TODO Änderung: Hier wird der gesamte betroffene Bereich der Klasse abgefragt
        flush();
  		// Raumwechsel mit Verlegung???
        if ( isset($_REQUEST['Raumwechsel']) && isset($_REQUEST['VonStunde']) && 
             isset($_REQUEST['VonDatum']))
        {
        	// Raumwechsel und Verlegung
        	$_SESSION['RaumwechselDatumOrg'] = $_SESSION['Datum'];
        	$_SESSION['RaumwechselStundeOrg'] = $_SESSION['Stunde'];
        	$_SESSION['Datum'] = $_REQUEST['VonDatum'];
        	$_SESSION['Stunde'] = $_REQUEST['VonStunde'];
        	$_SESSION['Woche'] = getID_Woche($_SESSION['Datum']);
        	echo '<div class="Hinweis">Unterrichtsverlegung vom '.
        	   date('d.m.Y',$_SESSION['RaumwechselDatumOrg']).' im '.
        	   $_SESSION['RaumwechselStundeOrg'].' Block</div>';        	        	
        }
        if ( isset($_REQUEST['RemVer']) )
        {
          $_SESSION['Woche'] = getID_Woche($_SESSION['Datum']);
          $Plan1 = liesPlanEin($db, $_SESSION['Art'], $_SESSION['Wofuer'], 
            $_SESSION["Woche"]);                    
          KlasseBehandeln($Plan1,$db, true);
          session_unregister('ListeVerhinderung');
          flush();          
        }
        elseif ( $_SESSION['Grund'] == KLASSESONSTIGES )
        {
          $_SESSION['Woche'] = getID_Woche($_SESSION['Datum']);
          $Plan1 = liesPlanEin($db, $_SESSION['Art'], $_SESSION['Wofuer'], 
            $_SESSION["Woche"]);
          $Plan1['Tag'] = date('w', $_SESSION['Datum']);
          $Plan1['Stunde'] = $_SESSION['Stunde'];
          KlasseBehandeln($Plan1,$db);          
          flush();
        }  
        else
        {          
          $_SESSION['Woche'] = -1;
          for ( $Tag = $_SESSION['Von']; $Tag <= $_SESSION['Bis']; $Tag = strtotime('+1 day',$Tag))
          {
            if ( getID_Woche($Tag) != $_SESSION['Woche'] )
            {
              $_SESSION['Woche'] = getID_Woche($Tag);
              $Plan1 = liesPlanEin($db, $_SESSION['Art'], $_SESSION['Wofuer'], $_SESSION['Woche']);
            }
            $_SESSION['Datum'] = $Tag;
            $Plan1['Tag'] = date('w', $_SESSION['Datum']);
            KlasseBehandeln($Plan1,$db);
            flush();
          }
        }
        break;
      case 'Lehrer':
        // Raumwechsel mit Verlegung???
        if ( isset($_REQUEST['Raumwechsel']) && isset($_REQUEST['VonStunde']) && 
             isset($_REQUEST['VonDatum']))
        {
        	// Raumwechsel und Verlegung
        	$_SESSION['RaumwechselDatumOrg'] = $_SESSION['Datum'];
        	$_SESSION['RaumwechselStundeOrg'] = $_SESSION['Stunde'];
        	$_SESSION['Datum'] = $_REQUEST['VonDatum'];
        	$_SESSION['Stunde'] = $_REQUEST['VonStunde'];
        	$_SESSION['Woche'] = getID_Woche($_SESSION['Datum']);
        	echo '<div class="Hinweis">Unterrichtsverlegung vom '.
        	   date('d.m.Y',$_SESSION['RaumwechselDatumOrg']).' im '.
        	   $_SESSION['RaumwechselStundeOrg'].' Block</div>';        	        	
        }
        // Normalfall: Plan für die betroffene Stunde
        $Plan1 = liesPlanEin($db, $_SESSION['Art'], $_SESSION['Wofuer'], $_SESSION['Woche']);
        $Plan1['Tag'] = date('w', $_SESSION['Datum']);
        $Plan1['Stunde'] = $_SESSION['Stunde'];          
        echo '<a id="LehrerTabelle" name="LehrerTabelle"></a>';
        LehrerBehandeln($Plan1, $db);
        // Anzeigeoptionen
        echo '<form action="'.$_SERVER["PHP_SELF"].'#LehrerTabelle" method="post">';
        echo '<input type="Checkbox" name="Anzeige[Fach]" value="v" ';
        if ( $_SESSION["AnzeigeFach"] )
          echo 'checked="checked"';
        echo '/>';
        echo "Nur fachgleiche anzeigen<br />\n";
        echo '<input type="Checkbox" name="Anzeige[Klasse]" value="v" ';
        if ( $_SESSION['AnzeigeKlasse'] )
          echo 'checked="checked"';
        echo '/>';
        echo "Nur Lehrer aus der Klasse anzeigen<br />\n";
        echo '<input type="Checkbox" name="Anzeige[Unterricht]" value="v" ';
        if ( $_SESSION['AnzeigeUnterricht'] )
          echo 'checked="checked"';
        echo ' onClick="javascript:document.getElementsByName(\'Anzeige[Anschliessend]\')[0].checked=true;"/>';
        echo "auch bei freiem Tag anzeigen<br />\n";
        echo '<input type="Checkbox" name="Anzeige[Anschliessend]" value="v" ';
        if ( $_SESSION['AnzeigeAnschliessend'] )
          echo 'checked="checked"';
        echo '/>';
        echo 'auch Anzeigen wenn Springblock entsteht<br />';
        echo '<input type="Checkbox" name="Anzeige[Lehrer]" value="v" ';
        if ( $_SESSION['AnzeigeLehrer'] )
          echo 'checked="checked"';
        echo '/>';
        echo 'nur Kollegen anzeigen die parallel Unterricht haben<br />';
        echo '<input type="Submit" name="Save" value="Anzeige ändern" />';
        echo "</form>\n";
        break;
      case 'Raum':
        $Plan1 = liesPlanEin($db, $_SESSION['Art'], $_SESSION['Wofuer'], $_SESSION['Woche']);
        $Plan1['Tag'] = date('w', $_SESSION['Datum']);
        $Plan1['Stunde'] = $_SESSION['Stunde'];  
        RaumBehandeln($Plan1, $db);
        break;
  } // switch
  mysql_query('DELETE FROM T_Vertretung_Liste WHERE F_Verhinderung_id='.$_SESSION['Verhinderung_id']);
  echo '<a href="'.$_SERVER['PHP_SELF'].'?Liste=1">Liste der Verhinderungen anzeigen</a></p>';
} // session_registered(Datum)
    
echo '</td></tr>';
include('include/footer.inc.php');
?>
