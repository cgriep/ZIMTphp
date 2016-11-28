<?php
/**
  * Berechnet den nächsten Schultag von einem Datum.
  * Ist zeitberuecksichtigen true, so wird ab 16 Uhr der nächste Tag, bis 16 Uhr der 
  * aktuelle Tag als Ausgangspunkt genutzt. Es werden Sams- und Sonntage sowie
  * Einträge in der Ferientabelle berücksichtigt.
  * Benötigt die Funktion istFrei aus stupla.inc.php.
  * @param $Datum Datum von dem begonnen wird
  * @param zeitberuecksichtigen true, wenn ab 16 Uhr der nächste Tag betrachtet werden soll
  * @return Einen timestamp mit dem Datum des nächsten Schultages
  */
function bestimmeNaechstenSchultag($Datum, $zeitberuecksichtigen = true)
{
	$Datum = strtotime(date('Y-m-d',$Datum));
        // Datum des nächsten Schultages festlegen
	if ( date('H') > 15 && $zeitberuecksichtigen )
	{
		$Plus=1;
	}
	else
	{
		$Plus=0;
	}
	do {
		$Nochmal = false;
		if ( date('w',strtotime('+'.$Plus.'day',$Datum)) == 0 )
		{
			$Plus += 1;
		}
		if ( date('w',strtotime('+'.$Plus.'day',$Datum)) == 6 )
		{
			$Plus += 2;
		}
		while ( istFrei(date('w',strtotime('+'.$Plus.'day',$Datum))) )
		{
			$Plus += 1;
			$Nochmal = true;
		}
	}
	while ($Nochmal);
	$Datum = strtotime('+'.$Plus.'day',$Datum);
	return $Datum;
}

/**
 * Bestimmt ob für eine Klasse eine Vertretung vorhanden ist. Betrachtet wird
 * das übergebene Datum sowie das darauf folgende Datum.
 * @param $Klasse die Klasse
 * @param $Datum das betrachtete Datum
 * @return true, wenn eine Vertretung vorliegt, false sonst 
 */
function vertretungVorhanden($Klasse, $Datum=0)
{
    if ( $Datum == 0) $Datum = bestimmeNaechstenSchultag(time());
    $Datum1 = bestimmeNaechstenSchultag(strtotime('+1day',$Datum), false);
    $Klasse = mysql_real_escape_string($Klasse);
	$sql = 'SELECT Datum FROM T_Vertretungen WHERE (Datum='.$Datum.
		' OR Datum='.$Datum1.') AND (Klasse="'.$Klasse.'" OR Klasse_Neu="'.$Klasse.
		'") ORDER BY Datum,Stunde';
    $query = mysql_query($sql);
    $anz = mysql_num_rows($query);
    mysql_free_result($query);
   return $anz > 0;    
}
?>
