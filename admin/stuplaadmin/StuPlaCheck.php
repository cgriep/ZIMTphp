<?php
/*
 * Created on 15.08.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $Debug = true;
if (isset($_REQUEST['NoDebug']))
  $Debug = false;
 
 include('include/Lehrer.class.php');
 include('include/stupla.inc.php');
 
 function pruefePlanKonsistenz($Version, $GueltigAb)
 {
    global $Debug;
    global $LEHRER_ANREDE;
    if (!is_numeric($Version) || !is_numeric($GueltigAb)) exit;
    $Meldungen = array(); 	
 	$meldung = '';
 	$query = mysql_query('SELECT * FROM T_Vertretungen WHERE Datum >= '.$GueltigAb.
      ' ORDER BY Datum,Stunde');
 	while ( $eintrag = mysql_fetch_array($query))
 	{
 	  // Prüfen, ob die Grundlage des Eintrages noch vorliegt
 	  if ( $eintrag['Klasse'] != '' && $eintrag['Raum'] != '' )
 	  {
 		// Turnus prüfen
        $Turnusse = array();          
        getTurnusListe(getID_Woche($eintrag['Datum']), $Turnusse);
        $Turnusse[] = 'jede Woche';
 		$erledigt = false; 		 		
 		// Turnus berücksichtigen (aktuellen Turnus holen und einsetzen)
 		$sql = 'SELECT * FROM T_StuPla WHERE Version='.
 		  $Version.' AND Stunde='.$eintrag['Stunde'].' AND Lehrer="'.
 		  $eintrag['Lehrer'].'" AND Klasse="'.$eintrag['Klasse'].'" AND Fach="'.
 		  $eintrag['Fach'].'" AND Raum="'.$eintrag['Raum'].
          '" AND Wochentag='.date('w',$eintrag['Datum']).' AND Turnus IN ("'.
           implode('","', $Turnusse).'")'; 		
 		$stuplaquery = mysql_query($sql);
 		
 		if ( $stuplaeintrag = mysql_fetch_array($stuplaquery))
 		{
          // alles ok 		   
 		}
 		else
 		{ 		    
 		  // Prüfen, ob nur der Raum sich geändert hat, dann zunächst Vertretungseintrag anpassen
 		  $sql = 'SELECT * FROM T_StuPla WHERE Version='.
 		       $Version.' AND Stunde='.$eintrag['Stunde'].' AND Lehrer="'.
 		       $eintrag['Lehrer'].'" AND Klasse="'.$eintrag['Klasse'].'" AND Fach="'.
 		       $eintrag['Fach'].'" AND Wochentag='.date('w',$eintrag['Datum']).
                ' AND Turnus IN ("'.
                implode('","', $Turnusse).'")'; 		
 		    $neuraum = mysql_query($sql);
 		  if ( mysql_num_rows($neuraum) > 0)
 		  {
 		    	// Ja, Raumänderung! Das wird zuerst korrigiert
 		    	$neuerRaum = mysql_fetch_array($neuraum);
 		    	if ( !$Debug )
 		    	  mysql_query('UPDATE T_Vertretungen SET Raum="'.$neuerRaum['Raum'].
                   '" WHERE Vertretung_id='.$eintrag['Vertretung_id']);
 		    	
 		        $meldung .= 'Eine Raumänderung am '.date('d.m.Y',$eintrag['Datum']).
                   ' von '.$eintrag['Raum'].' nach '.
 		           $neuerRaum['Raum'].' für '.$eintrag['Lehrer'].' in Klasse '.
 		           $eintrag['Klasse'].' im Stundenplan wurde im ' .
 		        		'Vertretungseintrag korrigiert.'."\n\n";
 		         $eintrag['Raum'] = $neuerRaum['Raum'];    
 		  }
 		  else
 		  {
 			// Es hat sich was am Stundenplan geändert!!!!!
 			// Löschen,
 			$Lehrer = new Lehrer($eintrag['Bearbeiter'], LEHRERID_EMAIL); 
 			if ( !isset($Meldungen[$Lehrer->Username]))
 			{  			  
 			  $message = $Lehrer->Anrede($LEHRER_ANREDE, false).",\n\n";
 			  $message .= 'aufgrund der Stundenplanänderung, die ab dem ';
 			  $message .= date('d.m.Y',$GueltigAb).' wirksam wird, ist folgende ' .
 				  	'Vertretung/Reservierung gelöscht worden, da die zugrunde ' .
 					'liegende Stunde verlegt wurde.'."\n";
 			  $Meldungen[$Lehrer->Username] = '';
 			}
 			else
 			  $message = '';
 			$s = date('d.m.Y',$eintrag['Datum']).' '.$eintrag['Stunde'].'. Block: '."\n";
 			$s .= 'Lehrer: '.$eintrag['Lehrer'].'-'.$eintrag['Lehrer_Neu']."\n";
 			$s .= 'Klasse: '.$eintrag['Klasse'].'-'.$eintrag['Klasse_Neu']."\n";
 			$s .= 'Fach: '.$eintrag['Fach'].'-'.$eintrag['Fach_Neu']."\n";
 			$s .= 'Raum: '.$eintrag['Raum'].'-'.$eintrag['Raum_Neu']."\n";
 			$message .= $s;
 			$s = 'Änderung am Plan - lösche Vertretung!'."\n".$s;
 			$s .= 'Bearbeiter: '.$eintrag['Bearbeiter']."\n\n"; 			 			
 			$meldung .= $s; 			
 			echo nl2br($s);
 			// -> Bearbeiter informieren wenn nicht Reservierung
 			      
 			$Meldungen[$Lehrer->Username] .= $message."\n";
            if ( !$Debug )
              mysql_query('DELETE FROM T_Vertretungen WHERE Vertretung_id='.$eintrag['Vertretung_id']);
 		  } // wenn kein Raumänderung
 		  mysql_free_result($neuraum); 			 		  
 		  $erledigt = true; 			
 		}
 		mysql_free_result($stuplaquery);
 	  } // wenn Eintrag nicht leer 
 	}
 	mysql_free_result($query);
 	foreach ( $Meldungen as $Wer => $message )
 	{
 		if ( !$Debug )
 		  mail($Wer.'@oszimt.de', '[OSZIMT Stundenplan] Löschung Vertretung(en) wegen Stundenplanänderung', 
 			$message."\n\n".'Diese Nachricht ist automatisch am '.
 			date('d.m.Y H:i').' erstellt worden', 
            'From: '.$_SERVER['REMOTE_USER']."@oszimt.de\nBcc: Griep@oszimt.de",
 			'-f'.$_SERVER['REMOTE_USER'].'@oszimt.de');			
 	}
 	// Zweiter Schritt: Nachdem alle Vertretungen raus sind, die nicht mehr möglich sind,
 	// muss nun geprüft werden ob Raumwechsel noch möglich sind.	
 	$Meldungen = array();
 	$message = '';	
 	$query = mysql_query('SELECT * FROM T_Vertretungen WHERE Raum<>Raum_Neu ' .
 			'AND Datum >= '.$GueltigAb);
 	while ( $eintrag = mysql_fetch_array($query))
 	{
 		// Turnus prüfen
        $Turnusse = array();          
        getTurnusListe(getID_Woche($eintrag['Datum']), $Turnusse);
        $Turnusse[] = 'jede Woche';
 		// ist der neue Raum noch frei?
 		$stuplaquery = mysql_query('SELECT * FROM T_StuPla WHERE Version='.
 			  $Version.' AND Stunde='.$eintrag['Stunde'].' AND Raum="'.$eintrag['Raum_Neu'].
              '" AND Wochentag='.date('w',$eintrag['Datum']).' AND Klasse<>"'.
              $eintrag['Klasse_Neu'].'" AND Turnus IN ("'.
           implode('","', $Turnusse).'")');
        while ( $stundenplaneintrag = mysql_fetch_array($stuplaquery) )
        {
           	// offenbar gibt es einen Stundenplaneintrag für diesen Raum ...
              // nun bleibt nur zu hoffen, dass eine andere Vertretung den Raum
              // freigibt...
              
              $sql = 'SELECT * FROM T_Vertretungen '.
                   'WHERE Datum='.$eintrag['Datum'].' AND Stunde='.$eintrag['Stunde'].                   
                   ' AND Raum="'.$eintrag['Raum_Neu'].
                   '" AND Raum_Neu<>"'.$eintrag['Raum_Neu'].'" ';
              $vquery = mysql_query($sql);
              if ( $raumeintrag = mysql_fetch_array($vquery) )
              {
              	// es gibt eine Freigabe -> kann bleiben, aber Meldung
              	$message .= 'Raumänderung kann erhalten bleiben:'."\n";
              	$message .= date('d.m.Y',$eintrag['Datum']).' '.$eintrag['Stunde'].'. Block: '."\n";
 			    $message .= 'Lehrer: '.$eintrag['Lehrer'].'-'.$eintrag['Lehrer_Neu']."\n";
 			    $message .= 'Klasse: '.$eintrag['Klasse'].'-'.$eintrag['Klasse_Neu']."\n";
 			    $message .= 'Fach: '.$eintrag['Fach'].'-'.$eintrag['Fach_Neu']."\n";
 			    $message .= 'Raum: '.$eintrag['Raum'].'-'.$eintrag['Raum_Neu']."\n"; 			
 			    $message .= 'Bearbeiter: '.$eintrag['Bearbeiter']."\n";
 			    $message .= 'Vertretungseintrag gibt Raum frei:'."\n";
				$message .= 'Lehrer: '.$raumeintrag['Lehrer'].'-'.$raumeintrag['Lehrer_Neu']."\n";
 			    $message .= 'Klasse: '.$raumeintrag['Klasse'].'-'.$raumeintrag['Klasse_Neu']."\n";
 			    $message .= 'Fach: '.$raumeintrag['Fach'].'-'.$raumeintrag['Fach_Neu']."\n";
 			    $message .= 'Raum: '.$raumeintrag['Raum'].'-'.$raumeintrag['Raum_Neu']."\n"; 			
 			    $message .= 'Bearbeiter: '.$eintrag['Bearbeiter']."\n\n";  			     			 			 			
              }
              else
              {
              	// Raum ist nicht frei - Meldung !!!
              	$Lehrer = new Lehrer($eintrag['Bearbeiter'], LEHRERID_EMAIL);
 				if ( !isset($Meldungen[$Lehrer->Username]))
 				{
 				  $message = $message = $Lehrer->Anrede($LEHRER_ANREDE, false).",\n\n"; 			  
 			      $message .= 'aufgrund der Stundenplanänderung, die ab dem ';
 			      $message .= date('d.m.Y',$GueltigAb).' wirksam wird, ist folgende ' .
   				    'Raumänderung nicht mehr möglich, da der betroffene Raum nun belegt ist.'."\n\n";
   				  $Meldungen[$Lehrer->Username] = '';   				   			   
 				}
 				else 
 				  $message = '';			
                // Prüfen ob der alte Raum frei ist, wenn ja: in diesen verlegen
                // wenn nein: unverändert lassen 
                // Meldung machen!
                $s = date('d.m.Y',$eintrag['Datum']).' '.$eintrag['Stunde'].'. Block: '."\n";
 			    $s .= 'Lehrer: '.$eintrag['Lehrer'].'-'.$eintrag['Lehrer_Neu']."\n";
 			    $s .= 'Klasse: '.$eintrag['Klasse'].'-'.$eintrag['Klasse_Neu']."\n";
 			    $s .= 'Fach: '.$eintrag['Fach'].'-'.$eintrag['Fach_Neu']."\n";
 			    $s .= 'Raum: '.$eintrag['Raum'].'-'.$eintrag['Raum_Neu']."\n";
 			
                $message .= $s;
 			    $s = 'Änderung am Plan - Raumänderung unmöglich!'."\n".$s;
 			    $s .= 'Bearbeiter: '.$eintrag['Bearbeiter']."\n\n"; 			 			
                
                $freiquery = mysql_query('SELECT * FROM T_StuPla WHERE Wochentag='.
                  date('w',$eintrag['Datum']).' AND Raum="'.$eintrag['Raum'].
                  '" AND Version='.$Version.' AND Stunde='.$eintrag['Stunde'].
                  ' AND Klasse<>"'.$eintrag['Klasse'].'"'.
                  ' AND Turnus IN ("'.implode('","',$Turnusse).'")');                       
                if ( $eintrag['Raum'] == '' )
                {
                	$message.= 'Die Reservierung wird aufgehoben.'."\n";
                	$s .= 'Die Reservierung wird aufgehoben.'."\n";
                	if ( !$Debug ) 
                	  mysql_query('DELETE FROM T_Vertretungen WHERE Vertretung_id='.
                	    $eintrag['Vertretung_id']);
                }
                elseif (mysql_num_rows($freiquery) > 0 )
                {
                	$neuraum = mysql_fetch_array($freiquery);
                	// Raum ist nicht frei!
                	$message .= 'Der Originalraum ist belegt:'.
                	  $neuraum['Klasse'].' bei '.$neuraum['Lehrer'].' in '.$neuraum['Fach'];
                    $message .= '. Bitte händisch prüfen!'."\n";
                	$s .= 'Der Originalraum ist belegt. Bitte händisch prüfen!'."\n";
                }                
                else
                {
                	// Raum ist frei - wir setzen die Klasse zurück in den alten Raum
                	$message .= 'Der Originalraum '.$eintrag['Raum'].' ist frei. ' .
                			'Die Klasse wird in den Raum zurückgesetzt.'."\n";
                    $s .= 'Der Originalraum '.$eintrag['Raum'].' ist frei. ' .
                			'Die Klasse wird in den Raum '.$eintrag['Raum'].
                            ' zurückgesetzt.'."\n";                	
                	if ( !$Debug ) 
                	  mysql_query('UPDATE T_Vertretungen SET Raum_Neu=Raum ' .
                			'WHERE Vertretung_id='.$eintrag['Vertretung_id']); 
                }            
                $s .= "\n"; 			    
 			    echo nl2br($s);
 			    $meldung .= $s;
 			    $Meldungen[$Lehrer->Username] .= $message."\n";
 			     			              	              	
              }
              mysql_free_result($vquery);
        } // while Stundenplaneintrag 
        mysql_free_result($stuplaquery); 			
 	} // while Vertretungsplaneintrag
 	mysql_free_result($query);
 	foreach ( $Meldungen as $Wer => $message )
 	{
 		if ( !$Debug )
 		  mail($Wer.'@oszimt.de', '[OSZIMT Stundenplan] ' .
 			'Raumproblem wegen Stundenplanänderung', $message."\n\n".'Diese Nachricht ist automatisch am '.
 			date('d.m.Y H:i').' erstellt worden', 'From: '.
 			$_SERVER['REMOTE_USER']."@oszimt.de",
 			'-f'.$_SERVER['REMOTE_USER'].'@oszimt.de');	
 	}	 
 	if ( $meldung == '' )
 	{
 		$meldung = 'Es waren keine Änderungen notwendig.';
 	}
 	if ( !$Debug )
 	  mail('seidel@oszimt.de','[OSZIMT Stundenplanänderung] Änderungen Vertretungen',
 	  'Ergebnis des Stundenplankonsistenzprüfung'."\n\n".$meldung.
       "\n\n".'Diese Nachricht ist automatisch am '.
 			date('d.m.Y H:i').' erstellt worden',
            "From: noreply@oszimt.de");    
    // Cache der Vertretungsliste löschen
    if ( !$Debug )
      mysql_query('DELETE FROM T_Vertretung_Liste'); 	
}

$Ueberschrift = 'Plankonsistenz prüfen';
include('include/header.inc.php');
echo '<tr><td>';
$query = mysql_query('SELECT DISTINCT Version, GueltigAb FROM T_StuPla ORDER BY Version');
while ( $version = mysql_fetch_array($query))
{
	$Version = $version['Version'];
	echo '<a href="'.$_SERVER['PHP_SELF'].'?Version='.$Version.'">'.$Version.' ('.
      date('d.m.Y',$version['GueltigAb']).')</a><br />';
	if ( isset($_REQUEST['Version']) && $_REQUEST['Version']==$Version)
	  $GueltigAb = $version['GueltigAb'];
}
mysql_free_result($query);
echo '<hr />';
if ( $Debug)
{
  echo '<div class="Hinweis">DEBUG-Modus. Es werden keine Änderungen an der Datenbank vorgenommen.';
  if ( isset($_REQUEST['Version']) && isset($GueltigAb)) 
    echo ' <a href="'.$_SERVER['PHP_SELF'].'?Version='.$_REQUEST['Version'].
      '&NoDebug=1">Änderungen durchführen</a>';
  echo '</div>';
}
if (isset($_REQUEST['Version']) && isset($GueltigAb))
  pruefePlankonsistenz($_REQUEST['Version'],$GueltigAb);
echo '</td></tr>';
include('include/footer.inc.php');
?>
