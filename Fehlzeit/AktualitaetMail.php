<?php
include_once("include/classes/orga/model/Schuljahr.class.php");
/*
 * Aktualitaet.php
 * Zeigt an, wann die Fehlzeiten der einzelnen Kurse zuletzt bearbeitet worden
 * sind.
 * Es wird immer das aktuelle Schuljahr angezeigt.
 * 
 * Letzte Änderung:
 * 09.01.06 C. Griep
 * 17.03.06 C. Griep - BmA-Kurse werden nicht mehr angezeigt
 *
 * Ist der Parameter Mail gesetzt, wird bei zu lang zurückliegenden Einträgen
 * (mehr als 1 Monat und 10 Tage) eine Erinnerungsmail verschickt.
 * (c) 2007 Christoph Griep 
 */
include('include/config.php');
include('include/turnus.inc.php');
include('include/stupla.inc.php');
include('include/Lehrer.class.php');

function sendeMail($Lehrer, $Kurs, $bearbeitetBis, $Klasse, $Mahnung)
{
	global $LEHRER_ANREDE;
	global $LEHRER_LEHRER;
        // beide Varianten sind möglich!
        $l = new Lehrer($Lehrer, LEHRERID_KUERZEL);
	if ( $l->Geschlecht == '' )
          $l = new Lehrer($Lehrer, LEHRERID_EMAIL);
        $nachricht = $l->Anrede($LEHRER_ANREDE, false).",\n\n";
	$nachricht .= "es gehört zu den Aufgaben der Tutorinnen und Tutoren auf " .
			"Tutanden, die hohe Fehlzeiten oder viele Verspätungen aufweisen, " .
			"zeitnah einzuwirken.\n";
	$nachricht .= "Das regelmäßige Erfassen der Fehlzeiten soll die Tutorinnen " .
			"und Tutoren dabei unterstützen.\n";
	$nachricht .= 'Die Fehlzeitenerfassung Ihres Kurses '.$Kurs.' in der '.$Klasse.' ist ';
	if ( $bearbeitetBis == -1 )
	  $nachricht .= 'in diesem Schuljahr noch nicht gepflegt worden.';
	else
	  $nachricht .= 'seit dem '.date('d.m.Y',$bearbeitetBis).' nicht mehr gepflegt worden.';
	if ( $Mahnung > 0 )
	{
		$nachricht .= 'Dies wurde Ihnen bereits vor '.$Mahnung.' ';
		if ( $Mahnung > 1 )
		  $nachricht .= 'Wochen';
		else
		  $nachricht .= 'Woche';
		$nachricht .= ' mitgeteilt.';
	}
	$nachricht .= "\n".'Bitte unterstützen Sie die Arbeit Ihrer Kolleginnen ' .
			'und Kollegen und aktualisieren Sie die Eintragungen im internen Lehrerbereich.';
	$nachricht .= "\nDirekter Link: https://lehrer.oszimt.de/Fehlzeit";    	
	$nachricht .= "\n\nmit freundlichen Grüßen\nIhre Abteilungsleitung\n\n";
    $nachricht .= '(automatisch erstellt am '.date('d.m.Y H:i').')';
    if ( $Mahnung>0 )
    {
    	$cc = "\nCc: ansorge@oszimt.de";
    }
    else
    {
    	$cc = '';
    }
    mail($l->Username.'@oszimt.de','[OSZIMT] Fehlzeiten '.$Kurs, $nachricht,
      'From: Abteilungsleitung IV <ansorge@oszimt.de>'.$cc,
    '-f ansorge@oszimt.de');
    echo $l->Anrede($LEHRER_LEHRER,false).
    ' wurde per Mail informiert.';
    return true;
}

$arrSchuljahr = Schuljahr::getSchuljahr('OG');
$Schuljahr = $arrSchuljahr['langform'];

echo 'Schuljahr '.$Schuljahr."\n";
$sql = 'SELECT DISTINCT T_Kurse.Kurs AS Kurs, Bearbeitetbis, Mahnung, '.
  'T_Fehlzeitenstand.Lehrer AS Lehrer, Schuljahr '.
  'FROM T_Kurse LEFT JOIN T_Fehlzeitenstand ON T_Fehlzeitenstand.Kurs= ' .
  "T_Kurse.Kurs WHERE Schuljahr='$Schuljahr' ORDER BY T_Kurse.Kurs";
$version = getAktuelleVersion();
if ( ! $query = mysql_query($sql,$db)) 
{
	echo '<div class="Fehler">Fehler '.mysql_error().'</div>';
}

$Abijahrgang = '';
$Jahrgang = explode(' ',$Schuljahr);
$Jahr = substr($Jahrgang[1], 3, 1); // Jahr ausfiltern
if ( $Jahrgang[0] == 'II')
{
	// K4 - Meldungen in längerem Abstand absetzen! (Abiturjahrgang)
    $Abijahrgang = 'OG '.(($Jahr+7)%10);    
} 
while ( $was = mysql_fetch_array($query) )
{
  $sql = 'SELECT Klasse FROM T_StuPla WHERE Fach="'.$was['Kurs'].'" ' .
      		'AND Klasse LIKE "OG _" AND Version='.$version.' LIMIT 1';
  $q = mysql_query($sql);
  
  if ( $klasse = mysql_fetch_array($q)) 
  {
        echo $was['Kurs'].' (';
        if ( $Abijahrgang == $klasse['Klasse'])
        {
            // Abiturjahrgang: Ab April keine Meldungen mehr
        	echo 'Abi,';
            if ( date('m')>=4 )
               echo 'fertig!,';
        }    
        $arrSchuljahr = Schuljahr::getSchuljahr('OG', $was['Bearbeitetbis']);
        if ( $was['Lehrer'] != '' && $arrSchuljahr['langform'] == $Schuljahr )
        {
          echo $was['Lehrer'].') bis '.date('d.m.Y',$was['Bearbeitetbis']);
          // wenn mehr als ein Monat vergangen ist 
          // und nicht Abijahrgang oder Abijahrgang noch vorhanden (< Mai)
          if ( $was['Bearbeitetbis'] < strtotime('-1 month -10 days') && 
               ($Abijahrgang != $klasse['Klasse'] || 
                date('m') < 5))
          {          	
          	if ( sendeMail($was['Lehrer'], $was['Kurs'], $was['Bearbeitetbis'], $klasse[0],$was['Mahnung']))        	
        	  mysql_query('UPDATE T_Fehlzeitenstand SET Mahnung=Mahnung+1 WHERE Kurs="'.$was['Kurs'].'"');          	
          }
          else
            mysql_query('UPDATE T_Fehlzeitenstand SET Mahnung=0 WHERE Kurs="'.$was['Kurs'].'"');
        }
        else
        {
          echo 'bisher nicht bearbeitet)';          
          $qu = mysql_query('SELECT Lehrer FROM T_StuPla ' .
        		  "WHERE Fach='".$was['Kurs']."' AND Version=".$version);
          if ( $lehrer = mysql_fetch_row($qu) )
          {
          	$l = new Lehrer($lehrer[0], LEHRERID_KUERZEL);
            echo ' -- '.$l->Vorname.', '.$l->Name;
            // Sonderfall: gerade neues Schuljahr?
            if ( date('m') == 2 || (date('m') >=7 && date('m') <= 8) )
            {
            	// Keine Mail senden, da Halbjahr gerade erst angefangen hat
            	echo ' Keine Benachrichtigung - Schuljahr hat gerade erst begonnen.';
            }
            elseif ( sendeMail($lehrer[0], $was['Kurs'], -1, $klasse[0], $was['Mahnung']))
              mysql_query('UPDATE T_Fehlzeitenstand SET Mahnung=Mahnung+1'.
                 ' WHERE Kurs="'.$was['Kurs'].'"');
          }
          else
          {
            echo ' -- Lehrer konnte nicht ermittelt werden!';
            // TODO Mail an Ansorge
          }
          mysql_free_result($qu);
        }        
        echo "\n";
  }
  mysql_free_result($q);      
}
mysql_free_result($query);

?>
