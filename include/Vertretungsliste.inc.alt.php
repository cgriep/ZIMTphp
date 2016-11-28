<?php
/*
 * Created on 16.09.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 /**
  * Berechnet die Stunden, die von einer Verhinderung betroffen sind.
  * @param $Verhinderung ein Verhinderungsfeld
  * @returns ein datumindiziertes Feld mit den Betroffenen
  */
function berechneStunden($db,$Verhinderung) // Alt: $Art,$Wer,$Von,$Bis)
{
  $ID_Woche = getID_Woche($Verhinderung['Von']);
  $erg = array();
  $Was = 'Klasse';
  $Von = $Verhinderung['Von'];
  if ( $Verhinderung['Art'] == 'Klasse' )
  { 
    if ( isset($Verhinderung['Grund']) && 
         $Verhinderung['Grund'] == KLASSEPRUEFUNG ) 
      $Was = 'Raum';
    else
      $Was = 'Lehrer';
  }
  do {
  	  	
    // Plan des Tages holen
    //$Plan = liesPlanEin($db, $Art, $Wer, $ID_Woche);       
    // Plan durchzählen
    $Anfangtag = date('w', $Von);
    for ( $Wochentag = $Anfangtag; $Wochentag <= 5; $Wochentag++)
    {
      $Tag = strtotime('+'.($Wochentag-$Anfangtag).' days',$Von);
      if ( $Tag <= $Verhinderung['Bis'] && ! istFrei($Tag) ) // is_array($Plan[$Wochentag]) 
      {
        for ( $i = 1; $i < 7; $i++)
          if ( $Verhinderung['UE'.$i] )
          {
            $eintraege = liesStundenplanEintragMitVertretung($Verhinderung['Art'],
              $Verhinderung['Wer'], $Tag, $i);
            foreach ( $eintraege as $Eintrag )
            {
              $Klasse = $Eintrag[$Was]; 
              if ( $Klasse != '*' )
                if ( !isset($erg[$Tag]) || !is_array($erg[$Tag]) || 
                   ! in_array($Klasse, $erg[$Tag]) )
                  $erg[$Tag][]= $Klasse;
            }
          } // wenn betroffener Block
      } // wenn betroffener Tag
    }
    settype($ID_Woche, 'int');
    $ID_Woche = getID_Anschluss_KW((int)$ID_Woche, '+');
    if ( $ID_Woche < 0 )
      $Von = $Verhinderung['Bis']+1000;
    else
      $Von = getMontag($ID_Woche);
  }
  while ($Von <= $Verhinderung['Bis']);
  return $erg;
}
 
/**
 *  Erstellt für die Anzeige in der Verhinderungs-Liste eine Anzeige in HTML
 *  Dabei werden Namen für die Differenzierung nach Tag und Abteilung eingefügt.
 *  @param $betroffene das Ergebnis eines berechneStunden-Aufrufes
 *  @param $UnterrichtDa true, wenn Unterricht betroffen ist, false sonst
 *  @param $eintrag ein Verhinderungs-Eintrag mit allen Daten als Feld
 *  @param $Abteilungen das Abteilungen-Objekt 
 *  @return eine Zeichenkette mit den gekennzeichneten Betroffenen
 */
function bestimmeBetroffeneAlsHTML($betroffene, $UnterrichtDa, $eintrag, $Abteilungen)
{ 
  if ( !isset($eintrag['Verhinderung_id']) || !is_numeric($eintrag['Verhinderung_id']))
  {
  	exit;
  }
  $betroffen = '';
  foreach ( $betroffene as $Tag => $Klassen )
  {
    $betroffen .= '<span class="Tag" name="AC'.date('dm',$Tag).'">';
    $betroffen .= '<a href="';
    $betroffen .= '/Vertretung/?Verhinderung_id='.$eintrag['Verhinderung_id'].'&Datum='.$Tag;
    $betroffen .= '" title="Vertretungen für '.$eintrag['Wer'].' am '.
          date('d.m.Y',$Tag).' planen">'.
          date('d.m.Y',$Tag).'</a>: ';
    $Komma = false;
    foreach ( $Klassen as $Klasse ) 
    {
      if ($Komma) $betroffen .= ', ';
      $nr = $Abteilungen->getKlassenAbteilung(ohneStern($Klasse));
      // Falls es keine Klasse ist, den Verursacher als Abteilungsgeber nehmen
      if ( $nr <= 0 )
      {
      	$nr = $Abteilungen->getKlassenAbteilung($eintrag['Wer']);
      }
      if ( $nr > 0 )
      {
          	$betroffen .= '<span name="Abt'.$nr.'">';
          }
          $betroffen .= $Klasse;
          if ( $nr > 0 )
            $betroffen .= '</span>';
          $Komma = true;
      }
      // Achtung: Bei Änderung auch Anpassung im index.php (unbearbeitete Vertretungen)
      $betroffen .= '</span><br />'."\n";
    }
    if ( Count($betroffene) == 0 )
    {
      	$betroffen .= '<span class="Tag" name="AC">';
      	if ( $UnterrichtDa )
        {            
          // Damit man Änderungen wieder entfernen kann
    	  $betroffen .= '<a href="?Verhinderung_id=' .
    			  $eintrag['Verhinderung_id'].'&Datum='.$eintrag['Von'];
          //   Bei Klassen
    	  if ( $eintrag['Art'] == 'Klasse' && ($eintrag['Grund'] == KLASSEWEG || 
    	       $eintrag['Grund']==KLASSEPRUEFUNG))
    	  {
     	      $betroffen .= '&RemVer=1" title="Alle vorhandenen Vertretungen ' .
     	      		'entfernen">Alles entfernen';
    	  }
    	  else
            $betroffen .= '" title="Vorhandene Vertretungen bearbeiten">Bearbeiten';
          $betroffen .= "</a>\n";
        }
        else
          $betroffen .= 'niemand betroffen';
        $betroffen .= '</span>'."\n";
    }
  return $betroffen;
}
?>
