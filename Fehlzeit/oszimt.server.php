<?php
/**
 * Servermodul für Fehlzeitenspeicherung
 */

// Datenbank einbinden
include_once('include/config.php');
include('include/turnus.inc.php');

function saveAusfall($Kurs, $Datumzahl, $Art, $BearbeitetBis)
{
	$Schuelernr = substr($Datumzahl, 1, strpos($Datumzahl, 'D')-1);
  $Datum = substr($Datumzahl, strpos($Datumzahl, 'B')+1);
  $Block = substr($Datumzahl, strpos($Datumzahl, 'D')+1, 1);
  return saveFehlzeit($Kurs, $Schuelernr, $Block.'B'.$Datum, $Art, $BearbeitetBis);
}

function saveFehlzeit($Kurs, $Schuelernr, $Datumzahl, $Art, $BearbeitetBis)
{
	$objResponse = new xajaxResponse();
	// Datensätze sichern
	$GroesstesDatum = 0;
	// Eintrag entfernen
	if ( $Datumzahl != '' && $Art != '' && is_numeric($Schuelernr))
	{
		$Block = substr($Datumzahl, 0, 1);
		$Datumzahl = substr($Datumzahl, strpos($Datumzahl, 'B')+1);
		if ( trim($Art) == '' )
		{
			if ( ! mysql_query('DELETE FROM T_Fehlzeiten WHERE Schueler_id = '.$Schuelernr.
			' AND Datum = "'.
			date('Y-m-d', $Datumzahl).'" AND Block='.$Block)) 
			$objResponse->addAlert('Fehler: '.mysql_error());
		}
		else
		{
			// Eintragen
      $arrSchuljahr = Schuljahr::getSchuljahr('OG', $Datumzahl);
      $Schuljahr = $arrSchuljahr['langform'];

			if ( $Datumzahl > $GroesstesDatum ) $GroesstesDatum = $Datumzahl;
			if ( ! mysql_query('INSERT INTO T_Fehlzeiten (Schueler_id, Art, Datum, Block, Kurs, Lehrer, Schuljahr) '.
			"VALUES ($Schuelernr,'".$Art."', '".date('Y-m-d', $Datumzahl)."', $Block, '$Kurs','".
			$_SERVER['REMOTE_USER']."','".$Schuljahr."')"))
			{
				$query = mysql_query('SELECT Art FROM T_Fehlzeiten WHERE Schueler_id = '.
				$Schuelernr." AND Block=$Block AND Datum='".date('Y-m-d',$Datumzahl)."'");
				$eintrag = mysql_fetch_row($query);
				mysql_free_result($query);
				if ( $eintrag[0] != $Art )
				if ( ! mysql_query("UPDATE T_Fehlzeiten SET Art = '$Art',Lehrer='".
				$_SERVER['REMOTE_USER']."' WHERE Schueler_id=$Schuelernr AND Block=$Block AND ".
				"Datum='".date('Y-m-d',$Datumzahl)."'"))
				$objResponse->addAlert('Fehler: '.mysql_error());
			}
		}
	}
	$stand = explode('.',$BearbeitetBis);
	$stand = strtotime($stand[2].'-'.$stand[1].'-'.$stand[0]);
	if ( $stand < $GroesstesDatum ) $stand = $GroesstesDatum;
	if ( ! mysql_query("INSERT INTO T_Fehlzeitenstand (Lehrer,Kurs,Bearbeitetbis) VALUES ('".
	$_SERVER['REMOTE_USER']."','".$_REQUEST['Kurs']."',".$stand.')'))
	{
		if ( ! mysql_query("UPDATE T_Fehlzeitenstand SET Lehrer='".$_SERVER['REMOTE_USER'].
		"',Bearbeitetbis=".$stand.", Mahnung=0 WHERE Kurs='".mysql_real_escape_string($Kurs)."'"))
		$objResponse->addAlert('Fehler: '.mysql_error());
	}
	$objResponse->addAssign('BearbeitetBis', 'value', date('d.m.Y', $stand));
	$objResponse->addAssign('BearbeitetBis', 'style.color', 'blue');
	$objResponse->addAssign('I'.$Schuelernr.'D'.$Block.'B'.$Datumzahl, 'style.color', 'blue');
	return $objResponse;
}


require('oszimt.ajax.php');
$xajax->processRequests();

?>