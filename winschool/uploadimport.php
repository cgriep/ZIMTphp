<?php
/*
 * Created on 08.12.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
if ( ! isset($_FILES))
{
	die('Es wurde keine Datei zum Upload angegeben!');
}
else
{
	// Datenbank öffnen
	include_once('include/config.php');
 foreach($_FILES as $datei)
 {
 	$anz = 0;
 	$fehler = 0;
 	$meldungen = '';
 	$content = file_get_contents($datei['tmp_name']);
 	$dateiname = $datei['name'].'-'.date('YmdHi');
 	$dat = fopen($dateiname,'w');
 	fwrite($dat, $content);
 	fclose($dat);
 	$messages .= 'Datei '.$datei['name'].' wurde empfangen, Dateiname '.$dateiname;
 	try {
 		$xml = simplexml_load_file($dateiname);
 		if (strpos( $dateiname, '.kurse.') > 0 )
 		{
 			// Kursdatei
 			$schuljahr = $xml->xpath('/winschool/Schuljahr');
 			$schuljahr = $schuljahr[0];
 			$key = $xml->xpath("/winschool/Schueler");
 			// Alle Kurse löschen
 			mysql_query("DELETE FROM T_Kurse WHERE Schuljahr='".$schuljahr."'");
 			$messages .= "\n".mysql_affected_rows().' Kurse aus '.$schuljahr.' gelöscht'."\n";
 			$insertsql = "INSERT INTO T_Kurse (Schueler_id, Kurs, Schuljahr, Art, Fach) VALUES (";
 			for ( $i=0; $i< count($key); $i++)
 			{
 				$schuelerNr = $key[$i]->SchuelerNr;
 				$kurse =  $key[$i]->xpath("Kurs");
 				for ( $j = 0; $j< count($kurse); $j++)
 				{
 					$sql = $insertsql . $schuelerNr.',';
 					$kurs = $kurse[$j]->Kurs;
 					$sql .= '\''.mysql_real_escape_string(utf8_decode($kurs)).'\',';
 					$sql .= '\''.mysql_real_escape_string($schuljahr).'\',';
 					$sql .= '\''.mysql_real_escape_string($kurse[$j]->Art).'\',';
 					$sql .= '\''.mysql_real_escape_string(utf8_decode($kurse[$j]->Fach)).'\')';
 					if ( ! mysql_query($sql) )
 					{
 						$messages .= $sql.'/'.$j.'/'.mysql_error()."\n";
 						$fehler++;
 					}
 					else
 						$anz++;
 				}
 			}
 		}
 		/*
 		 * winschool/Schuljahr/LdfSchuljahr
 		 /Halbjahr
 		 */
 		if (strpos( $dateiname, '.schueler.') > 0 )
 		{
 			$key = $xml->xpath("/winschool/schueler");
 			// Alle Schueler löschen
 			mysql_query("DELETE FROM T_Schueler");
 			$messages .= mysql_affected_rows().' Schueler gelöscht.'."\n";
 			$insertsql = "INSERT INTO T_Schueler (Nr, Name, Vorname, Klasse, Tutor, Geburtsdatum, Bemerkung, Abteilung) VALUES (";
 			for ( $i=0; $i< count($key); $i++)
 			{
 				$sql = $insertsql . $key[$i]->NR .',\''.mysql_real_escape_string(utf8_decode($key[$i]->Name)).'\',\''.
 				mysql_real_escape_string(utf8_decode($key[$i]->Vorname));
 				$sql .= '\',\''.mysql_real_escape_string(utf8_decode($key[$i]->Klasse)).'\',\''.
 				mysql_real_escape_string(utf8_decode($key[$i]->Tutor)).'\',';
 				// Geburtsdatum
 				list ($tag, $monat, $jahr) = split('[/.-]', $key[$i]->Geburtsdatum);
 				list ($jahr, $sonst) = split(' ',$jahr);
 				$sql .= mktime(0,0,0,$monat,$tag,$jahr).",";
 				$sql .= '\''.mysql_real_escape_string(trim(utf8_decode($key[$i]->Betrieb))).'\',\''.
 				mysql_real_escape_string(utf8_decode($key[$i]->Abteilung)).'\')';
 				if ( ! mysql_query($sql))
 				{
 					$messages .= $sql.'//'.mysql_error()."\n";
 					$fehler++;
 				}
 				else
 					$anz++;
 			}
 			mysql_query("UPDATE T_Stand SET Stand = ".time().", Bearbeiter='systemupload'");
		}
 	}
 	catch ( Exception $e)
 	{
 		$messages .= 'Fehler: '.$e->getMessage()."\n".$e->getTraceAsString();
 	}
 	$messages = "\n".$anz.' Datensätze ('.$fehler.' Fehler) aus '.$datei['name'].'-'.date('YmdHi')."\n".$messages;
 	
 	mail('griep@oszimt.de', 'Datenupload '.$datei['name'], $messages);
 }
}
?>
