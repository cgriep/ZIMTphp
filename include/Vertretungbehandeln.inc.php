<?php

function bestimmeBlock(& $Plan1)
{
  $BlockDa = false;
  while ( !$BlockDa && $Plan1['Stunde'] < 6)
  {
    $BlockDa = false;
    foreach ( $Plan1[$Plan1['Tag']][$Plan1['Stunde']] as $key => $Turnus )
    {
      if ( !isset($Turnus['Bemerkung']) || Count($Turnus['Lehrer']) > 0 ) // Vertretung vorhanden!
        $BlockDa = true;
    }
    if ( !$BlockDa )
    {
      $Plan1['Stunde']++;
      $_SESSION['Stunde']++;
    }
  }
}

// Zeigt den Lehrerplan und zugehörige Klassenpläne an
// Plan1: Plan des Lehrers
// $_SESSION[Datum], $_SESSION['KlasseWoche'] und $_SESSION[Stunde] 
// müssen gesetzt sein

function LehrerBehandeln($Plan1,$db)
{
  // bei Raumwechsel darf die Stunde nicht mehr geändert werden
  if ( ! isset($_REQUEST['Raumwechsel'])) 
    bestimmeBlock($Plan1);  
  if ( $_SESSION['Stunde'] == 6 )
  {
    echo '<div class="Hinweis">Kein Unterricht am '.
      date('d.m.Y',$_SESSION['Datum']).'</div>';
    $Plan1['Stunde'] = 1;
    $_SESSION['Stunde'] = 1;
    // es wird nur der Plan des Kollegen angezeigt
    schreibePlan($Plan1, $db, false, true, $Plan1);
  }
  elseif ( ! isset($_REQUEST['Raumwechsel']))
  {
    // Feststellen, für wen nun eine Vertretung organisiert werden muss    
    if ( Count($Plan1[$Plan1['Tag']][$Plan1['Stunde']]) != 0 )
    {  
      $Klasse = '';    
      foreach ( $Plan1[$Plan1['Tag']][$Plan1['Stunde']] as $Wer )
      {
        if ( isset($Wer) && is_array($Wer) )
        {
          // betroffene Klasse feststellen. Notwendig falls ein verlegter Block
          // nun doch anderweitig vertreten werden muss.                 
          foreach ( $Wer['Klasse'] as $dieKlasse )
          {
            if ( $dieKlasse != '' ) $Klasse = ohneStern($dieKlasse);
          }           
        }
      }
      if ( $Klasse != '' )
      {
        // Prüfen, ob der aktuelle Block von irgendwo übertragen werden kann
        $Fach = reset($Wer['Fach']);
        $sql = 'SELECT * FROM T_Vertretungen WHERE Klasse="'.$Klasse.
           '" AND Fach="'.$Fach.'" AND Lehrer="'.$Plan1['Anzeige'].'" AND ' .
           'Art='.VERTRETUNG_VERTRETEN.' AND Lehrer_Neu<>"" ORDER BY Datum';
        $query = mysql_query($sql);
        while ( $vertretung = mysql_fetch_array($query))
        {
        	// Prüfen, ob der Kollege immer noch Zeit hat.
        	$eintraege = liesStundenplanEintragMitVertretung('Lehrer', $vertretung['Lehrer_Neu'],
        	  $_SESSION['Datum'], $_SESSION['Stunde']);
        	// nur wenn wirklich nichts anliegt
        	$anz = 0;
        	foreach ( $eintraege as $eintrag )
        	{
        	  if ( ohneStern($eintrag['Lehrer']) != '') $anz++;
        	}
        	if ( istVerhindert($_SESSION['Datum'], $vertretung['Lehrer_Neu'], $_SESSION['Stunde']) 
        	    || istGesperrt($_SESSION['Datum'], $vertretung['Lehrer_Neu'], $_SESSION['Stunde']))
        	  $anz++;     	
        	echo '<div class="Hinweis">';
        	echo 'Am '.date('d.m.Y',$vertretung['Datum']).' wurde dieser Block von ';
        	$NeuLehrer = new Lehrer($vertretung['Lehrer_Neu'],LEHRERID_KUERZEL);
        	echo $NeuLehrer->Name.' vertreten. ';
            if ( $anz == 0 ) 
              echo '<a href="'.$_SERVER['PHP_SELF'].
        	    '?Neu='.$vertretung['Lehrer_Neu'].'&Art='.VERTRETUNG_VERTRETEN.'&Stamp='.time().
        	  		  '">Übernehmen</a>';
        	else
        	  echo $NeuLehrer->Name.' kann in diesem Block aber nicht.';
        	echo '</div>';
        }
        mysql_free_result($query);
        // um Verlegungen zu ermöglichen, wird das Datum von dem Datum der 
        // angezeigten Stunde entkoppelt 
        $Plan2 = liesPlanEin($db, 'Klasse', $Klasse, $_SESSION['KlasseWoche']);
        $Plan2['OrgLehrer'] = $Plan1['Anzeige'];
        $Plan2['OrgRaum'] = '';
        if ( Count($Wer['Raum']) == 1 ) $Plan2['OrgRaum'] = reset($Wer['Raum']);
        // TODO: Überlegen ob AnzeigeKlasse sinnvoll ist. Eigentlich reicht 
        // die Optimierung, wenn nicht parallele angezeigt werden
        if ( $_SESSION['AnzeigeKlasse'] && ! $_SESSION['AnzeigeLehrer'] )
        {
          // Plan untersuchen: Wo ist eine Verlegung möglich?
          $Plan2['LehrerVerschiebbar'] = array();
          $Plan2['RaumVerschiebbar'] = array();
          for ( $Wochentag = 1; $Wochentag < 5; $Wochentag++)
            for ( $Block = 1; $Block < 7; $Block++ )
              if ( is_array($Plan2[$Wochentag][$Block]))
              {
                foreach ( $Plan2[$Wochentag][$Block] as $Turnus => $Eintraege )
                {
                  // Lehrer prüfen
                  foreach ( array('Lehrer', 'Raum') as $Feld )
                    foreach ( $Eintraege[$Feld] as $Eintrag )
                    {
                      if ( strpos($Eintrag, '*') !== false )
                        $Eintrag = substr($Eintrag,1); // * wegschneiden
                      if ( !in_array($Eintrag,$Plan2["{$Feld}Verschiebbar"]) )
                      {
                        $seintrag = liesStundenplanEintragMitVertretung($Feld,
                          $Eintrag, $_SESSION['Datum'], $_SESSION['Stunde']);
                        if ( ! hatUnterricht($seintrag, $Feld,$Eintrag) )
                        {
                          // Lehrer hat frei!!
                          $Plan2["{$Feld}Verschiebbar"][] = $Eintrag;
                        }
                      }
                    }
                }
              } // if
        }
        elseif ( $_SESSION['AnzeigeLehrer'] )
          // Alle Lehrer die an diesem Block Unterricht haben
          $Plan2['LehrerVerschiebbar'] = true;
        else
          $Plan2['LehrerVerschiebbar'] = false;
        flush();
        schreibePlan($Plan2, $db, false, true, $Plan1);
        flush();
        // Zusatzinformationen: Vertretungsinfos zu den verschiebbaren Kollegen
        ZeigeLehrerInfos($_SESSION['Datum'], $_SESSION['Stunde'],
          $Plan2['LehrerVerschiebbar'], $Wer['Klasse'], $Wer['Fach']);
        flush();
      } // if
      else
      {
        echo '<div class="Fehler">Keine Klasse gefunden die am '.
                date('d.m.Y',$_SESSION['Datum']).' im '.$_SESSION['Stunde'].
                '. Block vertreten werden müsste.</div>';
        schreibePlan($Plan1, $db, false, true, $Plan1);
      }
    } // if Wer
    else
      schreibePlan($Plan1, $db, false, true, $Plan1);
  }
  else
  {
  	 // Raumwechsel ist angesagt
  	 schreibeRaumWahlPlan($Plan1, $db);
  }
} // LehrerBehandeln

/**
 * Behandelt die Vertretungen einer Klasse
 * Wenn Grund=KLASSEWEG:
 * Setzt allen Unterricht am Tag auf Ausfall
 * Wenn Grund=KLASSENBEZOGENES EREIGNIS:
 * Zeigt den Stundenplan zur weiteren Bearbeitung an
 * 
 * @param $Plan1 Der Stundenplan der Klasse
 * @param $db Verbindungsresource zur Datenbank
 * @param $loeschen wenn true und GRUND=KLASSEWEG werden alle Vertretungen am Tag entfernt
 */
function KlasseBehandeln($Plan1, $db, $loeschen = false)
{
  if ( $_SESSION['Grund'] == KLASSEWEG )
  {
    if ( $loeschen )
    {
      mysql_query('DELETE FROM T_Vertretungen WHERE F_Verhinderung_id='.
        $_SESSION['Verhinderung_id']);
      echo "<div class=\"Hinweis\">Der Unterricht der {$Plan1['Anzeige']} ";
      echo ' wurde wieder eingesetzt.</div>';
    }
    else
    {  
      // Alle Blöcke der Klasse ausfallen lassen
      $wann = array();
      for ( $Block = 1; $Block <= 6; $Block++)
      	// Die Blockvorgaben berücksichtigen!               
        if ( $_SESSION['UE'][$Block] )
        {
          $wann[] = $Block;
          if ( isset($Plan1[$Plan1['Tag']][$Block]) && Count($Plan1[$Plan1['Tag']][$Block])>0)
          {
            trageVertretungEin('Klasse', $Plan1['Anzeige'], $_SESSION['Datum'], $Block,
               array('Fach'=>'','Lehrer'=>'','Raum'=>'','Klasse'=>''),
             $_SESSION['Grund'], $_SESSION['Hinweis'], $_SESSION['Verhinderung_id'],
             VERTRETUNG_KLASSENAUSFALL);         
          }              
        }
      if ( Count($wann) != 6 )
        $wann = 'in den Blöcken '.implode(',',$wann);
      else
        $wann = '';        
      echo "<div class=\"Hinweis\">Der Unterricht der {$Plan1['Anzeige']} am ";
      echo date('d.m.Y',$_SESSION['Datum'])." $wann fällt aus.</div>\n";
    }
    session_unregister('Datum');
  }
  elseif ($_SESSION['Grund'] == KLASSESONSTIGES )
  {
  	if ( isset($_REQUEST['Raumwechsel']))
  	{
  		schreibeRaumWahlPlan($Plan1, $db);
  	}
  	else
  	{
  	// Klassenstundenplan anzeigen  	    
    	schreibePlan($Plan1, $db, false, true, $Plan1);
  	}    
  }
  elseif ($_SESSION['Grund']== KLASSEPRUEFUNG )
  {
  	// Prüfung: Klasse zieht in einen neuen Raum. Ursprüngliche Räume werden 
  	// freigegeben
  	if ( $loeschen )
    {
      mysql_query('DELETE FROM T_Vertretungen WHERE F_Verhinderung_id='.
        $_SESSION['Verhinderung_id']);
      echo "<div class=\"Hinweis\">Der Unterricht der {$Plan1['Anzeige']}";
      echo ' wurde wieder eingesetzt.</div>';
    }
    else
    {  
      // Alle Blöcke der Klasse ausfallen lassen
      $wann = array();
      for ( $Block = 1; $Block <= 6; $Block++)
      	// Die Blockvorgaben berücksichtigen!               
        if ( $_SESSION['UE'][$Block] )
        {
          $wann[] = $Block;
          if ( isset($Plan1[$Plan1['Tag']][$Block]) && Count($Plan1[$Plan1['Tag']][$Block])>0)
          {
            trageVertretungEin('Klasse', $Plan1['Anzeige'], $_SESSION['Datum'], $Block,
               array('Fach'=>'Prüfung','Raum'=>''),
             $_SESSION['Grund'], $_SESSION['Hinweis'], $_SESSION['Verhinderung_id'],
             VERTRETUNG_PRUEFUNG);         
          }              
        }
      if ( Count($wann) != 6 )
        $wann = 'in den Blöcken '.implode(',',$wann);
      else
        $wann = '';        
      echo "<div class=\"Hinweis\">Der Unterricht der {$Plan1["Wofuer"]} am ";
      echo date('d.m.Y',$_SESSION['Datum'])." $wann fällt aus.</div>\n";
    }
    session_unregister('Datum');
  }
} // KlasseBehandeln

function RaumBehandeln($Plan1, $db)
{
  bestimmeBlock($Plan1);
  if ( $_SESSION['Stunde'] == 6 )
  {
    echo '<div class="Hinweis">Kein Unterricht am '.
      date('d.m.Y',$_SESSION['Datum']).'</div>';
    $Plan1['Stunde'] = 1;
    $_SESSION['Stunde'] = 1;
    schreibePlan($Plan1, $db, false, true, $Plan1);
  }
  else
  {
    schreibeRaumWahlPlan($Plan1, $db);
  }
} // RaumBehandeln

?>