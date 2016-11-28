<?php
/**
 * Letzte Änderung:
 * 25.02.06 C. Griep
 * 06.03.06 C. Griep - Tauschen von Lehrern eingebaut
 * 22.02.07 C. Griep - Korrektur im Planaufbau 
 * 
 * (c) Christoph Griep
 *
 */
include_once('Vertretung.inc.php');
include_once('stupla.inc.php');
include_once('include/raeume.inc.php');

// Arten von Vertretung
DEFINE('VERTRETUNG_AUSFALL', 1);
DEFINE('VERTRETUNG_TEILUNGAUFHEBEN', 2);
DEFINE('VERTRETUNG_VERLEGUNG', 3);
DEFINE('VERTRETUNG_VERTRETEN', 4);
DEFINE('VERTRETUNG_ENTFERNEN', 5);
DEFINE('VERTRETUNG_VERLEGUNGMITRAUM', 6);
DEFINE('VERTRETUNG_ZUSATZKLASSE', 7);
DEFINE('VERTRETUNG_RAUMZUSATZ', 8);
DEFINE('VERTRETUNG_RAUMWECHSEL', 9);
DEFINE('VERTRETUNG_KLASSENAUSFALL', 10);
DEFINE('VERTRETUNG_ANDERERUNTERRICHT', 11);
DEFINE('VERTRETUNG_VERLEGUNGOHNEAUSFALL', 12);
DEFINE('VERTRETUNG_NURBEMERKUNG', 13);
DEFINE('VERTRETUNG_AUSFALLOHNEBERECHNUNG', 14);
DEFINE('VERTRETUNG_TAUSCH', 15); // Lehrertausch
DEFINE('VERTRETUNG_SONDER', 16);
DEFINE('VERTRETUNG_PRUEFUNG', 17); // Klasse in anderen Raum verlegen
DEFINE('VERTRETUNG_TEILUNGAUSFALL', 18); // Teilung fällt aus
DEFINE('VERTRETUNG_VERLEGUNGOHNEAUSFALLMITRAUMWECHSEL',19);

// Die Vertretungsarten, die nicht in der Statistik mitgezählt werden
DEFINE('VERTRETUNG_OHNESTATISTIK', '('.VERTRETUNG_NURBEMERKUNG.','.
VERTRETUNG_RAUMZUSATZ.','.
VERTRETUNG_RAUMWECHSEL.','.
VERTRETUNG_VERLEGUNG.','.
VERTRETUNG_AUSFALLOHNEBERECHNUNG.','.
VERTRETUNG_VERLEGUNGOHNEAUSFALL.','.
VERTRETUNG_TAUSCH.','.
VERTRETUNG_SONDER.','.
VERTRETUNG_PRUEFUNG.','.
VERTRETUNG_VERLEGUNGOHNEAUSFALLMITRAUMWECHSEL.')');
// Alles was weniger Arbeit macht
DEFINE('VERTRETUNG_WENIGERARBEIT', '('.VERTRETUNG_AUSFALL.','.
VERTRETUNG_KLASSENAUSFALL.','.
VERTRETUNG_AUSFALLOHNEBERECHNUNG.','.
VERTRETUNG_TEILUNGAUSFALL.')');
// Alles was Mehrarbeit macht
DEFINE('VERTRETUNG_MEHRARBEIT', '('.VERTRETUNG_VERTRETEN.')');
// Alles was Reservierungen (keine Vertretung) sind
DEFINE('VERTRETUNG_RAUMRESERVIERUNGEN', '('.VERTRETUNG_RAUMWECHSEL.','.
VERTRETUNG_RAUMZUSATZ.')');

// Alles was Mehrarbeit macht, aber statistisch keine ist
DEFINE('VERTRETUNG_MEHRARBEITOHNESTATISTIK', '('.VERTRETUNG_ZUSATZKLASSE.','.
VERTRETUNG_TEILUNGAUFHEBEN.','.
VERTRETUNG_SONDER.','.
VERTRETUNG_ANDERERUNTERRICHT.')');

// Alles was eine Verlegung ist (Block wird an neue Stelle gerückt)
DEFINE('VERTRETUNG_VERLEGUNGEN', VERTRETUNG_VERLEGUNG.','.
VERTRETUNG_VERLEGUNGMITRAUM.','.
VERTRETUNG_VERLEGUNGOHNEAUSFALLMITRAUMWECHSEL.','.
VERTRETUNG_VERLEGUNGOHNEAUSFALL);


// Gründe für Vertretung (hinten immer Pflichtangaben im Vertretung-Eintrag)
//GrundRaum:
DEFINE('RAUMZUSATZ', 1); // Raumreservierung zusätzlich (immer Raum)
DEFINE('RAUMWECHSEL', 2);// Raumwechsel (immer Raum)
//GrundLehrer
DEFINE('LEHRERKRANK', 3); // Krankheit (immer Lehrer)
DEFINE('LEHRERSONDER',4); // Sonderurlaub (immer Lehrer)
DEFINE('LEHRERDIENST',5); // Dienstl. Beurlaubung (immer Lehrer)
DEFINE('LEHRERFORTBILDUNG',7); // Fortbildung (immer Lehrer)
//GrundKlasse
DEFINE('KLASSEWEG', 6);   // Klasse fehlt (immer Klasse)
DEFINE('KLASSESONSTIGES', 8);   // alles was nicht in der Statistik auftaucht z.B. Klausur
DEFINE('KLASSEPRUEFUNG', 9);   // Prüfung o.ä., wobei die Klasse einen Tag in einen neuen Raum umzieht

$Gruende[RAUMZUSATZ] = 'Raumreservierung';
$Gruende[RAUMWECHSEL] = 'Raumwechsel';
$Gruende[LEHRERKRANK] = 'Krank';
$Gruende[LEHRERSONDER] = 'Sonderurlaub';
$Gruende[LEHRERDIENST] = 'Dienstl. Verh.';
$Gruende[LEHRERFORTBILDUNG] = 'Fortbildung';
$Gruende[KLASSEWEG] = 'Klasse weg';
$Gruende[KLASSESONSTIGES] = 'Klassenbezogenes Ereignis';
$Gruende[KLASSEPRUEFUNG] = 'Prüfung im Hause (Raumwechsel)';

function istEinzutragen($wert, $Feld)
{
	$wert = trim($wert);
	if ( ! is_array($Feld) || $wert == '') return false;
	if ( ! in_array($wert, $Feld) &&
	! in_array(ohneStern($wert), $Feld) &&
	! in_array('*'.$wert, $Feld) &&
	$wert != '*' )
	return true;
	else
	return false;
}

function getSJahrBeginn($sjahr = 0)
{
	if($sjahr == 0)
	$sjahr = getSJahr();
	$erg = -1;
	$sql = "SELECT MIN(Montag) FROM T_Woche WHERE SJahr ='$sjahr'";
	$rs = mysql_query($sql);
	if ($row = mysql_fetch_row($rs))
	$erg = $row[0];
	mysql_free_result($rs);
	return $erg;
}

function berechneAusfallstunden($Lehrer, $Beginn, $Ende = 0)
{
	// Ausfälle für den Kollegen: KlasseFehlt
	$Bereich = 'Datum ';
	if ( $Ende != 0 )
	$Bereich .= "BETWEEN $Beginn AND $Ende";
	else
	$Bereich .= "> $Beginn";
	$sql = 'SELECT Count(*) FROM T_Vertretungen '.
	"WHERE Lehrer='$Lehrer' AND Lehrer_Neu='' AND $Bereich ".
	'AND GrundLehrer=0';
	$lquery = mysql_query($sql);
	$rs = mysql_fetch_row($lquery);
	$Ausfallstunden = $rs[0];
	mysql_free_result($lquery);
	return $Ausfallstunden;
}

function berechneVertretungsstunden($Lehrer, $Beginn, $Ende = 0, $Art='')
{
	// Vorhandene Vertretungen ohne Raumreservierungen/-verlegungen
	$Bereich = 'Datum ';
	if ( is_numeric($Ende) && $Ende != 0 )
	$Bereich .= "BETWEEN $Beginn AND $Ende";
	else
	$Bereich .= "> $Beginn";
	$lquery = mysql_query('SELECT Count(*) FROM T_Vertretungen '.
	"WHERE Lehrer_Neu='$Lehrer' AND $Bereich ".
	"AND Lehrer<>Lehrer_Neu $Art");
	$rs = mysql_fetch_row($lquery);
	$Vertretungsstunden = $rs[0];
	mysql_free_result($lquery);
	return $Vertretungsstunden;
}

function berechneWochenstundenzahl($Lehrer, $Turnusliste, $Version)
{
	$xanz = 0;
	$sql ='SELECT Count(*), Wochentag, Stunde FROM T_StuPla '.
	"WHERE Version=$Version AND Lehrer='$Lehrer' AND Turnus IN ('".
	implode("','",$Turnusliste)."') GROUP BY Wochentag, Stunde";
	$lquery = mysql_query($sql);
	while ( $anz = mysql_fetch_row($lquery) )
	{
		$xanz++;
	}
	mysql_free_result($lquery);
	return $xanz;
}

/**
 * Entfernt eine Vertretung aus dem System.
 * @param $ID eine kommaseparierte Liste der zu löschenden IDs
 *
 */
function entferneVertretung($ID)
{
	$ids = explode(',',$ID);
	foreach ( $ids as $id )
	{
		if ( is_numeric($id) )
		{
			// Sonderfall prüfen:
			// wenn die Art 11 (ANDERER UNTERRICHT) ist, dann muss geprüft werden, ob
			// eine Teilung aufgehoben wurde (Lehrer=Lehrer_Neu und
			// Art=TEILUNGAUFHEBEN und GrundLehrer=0)
			// Bei Verlegung / Verlegung mit Raum gilt das gleiche, nur dass
			// Lehrer_Neu=Lehrer und Fach=Fach_Neu, das Datum aber unterschiedliche
			// sein kann
			$query= mysql_query('SELECT * FROM T_Vertretungen WHERE Vertretung_id='.$id);
			$Vertretung = mysql_fetch_array($query);
			mysql_free_result($query);
			if ( $Vertretung['Art'] == VERTRETUNG_VERLEGUNG
			|| $Vertretung['Art'] == VERTRETUNG_VERLEGUNGMITRAUM )
			{
				$sql = 'SELECT Vertretung_id FROM T_Vertretungen WHERE ' .
				"Lehrer='{$Vertretung["Lehrer_Neu"]}' AND Art=" .
				VERTRETUNG_AUSFALL." AND Fach='{$Vertretung["Fach_Neu"]}' " .
				"AND F_Verhinderung_id=".$Vertretung["F_Verhinderung_id"];
				echo $sql;
				$query = mysql_query($sql);
				if ( $vid = mysql_fetch_row($query))
				mysql_query('DELETE FROM T_Vertretungen WHERE Vertretung_id='.$vid[0]);
				mysql_free_result($query);
			}
			if ( $Vertretung['Art'] == VERTRETUNG_VERLEGUNGOHNEAUSFALL ||
			$Vertretung['Art'] == VERTRETUNG_VERLEGUNGOHNEAUSFALLMITRAUMWECHSEL )
			{
				$sql = 'SELECT Vertretung_id FROM T_Vertretungen WHERE ' .
				"Lehrer='{$Vertretung["Lehrer_Neu"]}' AND Art=" .
				$Vertretung['Art']." AND Fach='{$Vertretung["Fach_Neu"]}' " .
				'AND F_Verhinderung_id='.$Vertretung['F_Verhinderung_id'];
				if ( !$query = mysql_query($sql)) echo '<div class="Fehler">'.$sql.': '.mysql_error().'</div>';
				if ( $vid = mysql_fetch_row($query))
				mysql_query('DELETE FROM T_Vertretungen WHERE Vertretung_id='.$vid[0]);
				mysql_free_result($query);
			}
			if ( $Vertretung['Art'] == VERTRETUNG_ANDERERUNTERRICHT )
			{
				$query = mysql_query('SELECT Vertretung_id FROM T_Vertretungen WHERE ' .
				"Lehrer='{$Vertretung["Lehrer_Neu"]}' AND Art=".
				VERTRETUNG_TEILUNGAUFHEBEN.' AND GrundLehrer=0 AND Datum=' .
				$Vertretung['Datum']." AND Stunde={$Vertretung["Stunde"]} AND " .
				'F_Verhinderung_id='.$Vertretung['F_Verhinderung_id']);
				if ( $vid = mysql_fetch_row($query))
				mysql_query('DELETE FROM T_Vertretungen WHERE Vertretung_id='.$vid[0]);
				mysql_free_result($query);
			}
			mysql_query('DELETE FROM T_Vertretungen WHERE Vertretung_id='.$id);
		}
	}
}

/**
 * Zeigt Informationen zu den Lehrern an. Erstellt wird eine Tabelle mit den
 * Namen der Lehrern, ihren Stunden und Klassen sowie für die sechs Blöcke des
 * Tages eine Anzeige, ob sie Unterricht haben oder verwendbar sind.
 * @param $Datum das Datum für das die Anzeige erfolgen soll
 * @param $Stunde die ausgewählt Stunde. Sie wird in der Anzeige markiert
 * @param $WelcheLehrer ein Feld mit Kürzeln der Lehrer die anzeigt werden sollen. Ist das Feld leer, wird aufgrund der Anzeigoptionen bestimmt welche Lehrer angezeigt werden sollen.
 * @param $Klassen die Klassen für die eine Anzeige der passenden Lehrer erfolgen soll, wenn das Lehrerfeld leer ist
 * @param $Faecher die Fächer für die eine Anzeige der passenden Lehrer erfolgen soll, wenn das Lehrerfeld leer ist
 */
function ZeigeLehrerInfos($Datum, $Stunde, $WelcheLehrer = array(),
$Klassen = array(), $Faecher = array())
{
	$Anschliessend = false;
	$Unterricht = false;
	if ( !session_is_registered('AnzeigeLehrer') )
	$_SESSION['AnzeigeLehrer'] = false;
	if ( session_is_registered('AnzeigeAnschliessend') )
	$Anschliessend = !$_SESSION['AnzeigeAnschliessend'];
	if ( session_is_registered('AnzeigeUnterricht') )
	$Unterricht = !$_SESSION['AnzeigeUnterricht'];
	echo '<h2>Informationen zu ';
	if ( $_SESSION['AnzeigeLehrer'] )
	echo 'parallel unterrichtenden';
	else
	echo 'verfügbaren';
	echo ' KollegInnen ';
	if ( $_SESSION['AnzeigeFach'] ) echo 'mit gleichem Fach ';
	if ( $_SESSION['AnzeigeKlasse'] ) echo 'in gleicher Klasse ';
	echo '</h2>';
	echo 'Klicken Sie auf den Lehrernamen um eine Vertretung einzurichten. ';
	echo '<table class="Liste" width="100%">';
	echo '<tr><th>Lehrer</th>';
	for ($block = 1; $block<7; $block++)
	echo "<th>$block</th>";
	echo '<th><acronym lang="de" title="Wochenstunden für die aktuelle Woche">WoS' .
	'</acronym></th>'; // Wochenstunden
	echo '<th><acronym lang="de" title="Vertretungsdifferenz in Stunden gesamt, ' .
	'in Klammern Teilungsstunden">VDif</acronym></th>'; // Vertretungsstunden im Schuljahr
	echo '<th><acronym lang="de" title="+ zuviel/ - zuwenig Stunden dieses Jahr">Ü</acronym></th>';
	echo '<th>Fächer/Klassen</th>';
	echo "</tr>\n";
	$Turnusliste = array();
	$ID_Woche = getID_Woche($Datum);
	$Montag = getMontag($ID_Woche);
	getTurnusliste($ID_Woche, $Turnusliste);
	$Turnusliste[] = 'jede Woche';
	$SJahrBeginn = getSJahrBeginn();
	$Version = getAktuelleVersion($_SESSION['Datum']);
	for ( $i= 1; $i< 7;$i++)
	{
		$freieLehrer[$i] = holeBefreite('Lehrer', $Datum,$i);
	}
	if ( ! is_array($WelcheLehrer) )
	{
		// freie Kollegen raussuchen
		$Anzeigeoptionen = '';
		if ( $_SESSION['AnzeigeKlasse'] )
		{
			$Anzeigeoptionen .= " AND Klasse IN ('".implode("','",$Klassen)."')";
		}
		if ( $_SESSION['AnzeigeFach'] )
		{
			$Anzeigeoptionen .= " AND Fach IN ('".implode("','",$Faecher)."')";
		}
		$Anschliessend = true;
		$Unterricht = true;
		if ( $_SESSION['AnzeigeUnterricht'] )
		{
			$Unterricht = false;
			$Anschliessend = false;
		}
		elseif ( $_SESSION['AnzeigeAnschliessend'] )
		{
			$Anschliessend = false;
		}
		// Freie Lehrer heraussuchen
		if ( !$WelcheLehrer )
		{
			$dieLehrer = array();
			if ( $Anzeigeoptionen != '' )
			{
				// Wir suchen die Lehrer, die auf die Anzeigeoptionen passen und
				$sql = "SELECT Lehrer FROM T_StuPla WHERE Version=$Version $Anzeigeoptionen ".
				'GROUP BY Lehrer';
				$query = mysql_query($sql);
				while ($lehrer = mysql_fetch_array($query) )
				{
					$dieLehrer[] = $lehrer['Lehrer'];
				}
				mysql_free_result($query);
				$query = mysql_query('SELECT * FROM T_Verhinderung_Sperrungen '.
				'WHERE Wochentag='.date('w',$Datum)." AND StundeVon<=$Stunde AND StundeBis>=$Stunde");
				while ( $row = mysql_fetch_array($query) )
				{
					$key = array_search($row['LehrerKuerzel'], $dieLehrer);
					if ( $key !== false )
					unset($dieLehrer[$key]);
				}
				mysql_free_result($query);
			}
			if ( Count($dieLehrer) == 0)
			$LehrerAuswahl = '';
			else
			$LehrerAuswahl = "AND Lehrer IN ('".implode("','",$dieLehrer)."')";
			// alle Lehrer,
			$sql = 'SELECT Lehrer FROM T_StuPla WHERE (Lehrer NOT IN ('.
			// die an dem Tag/Block in den aktuellen Turnussen nicht Unterricht haben
			'SELECT DISTINCT Lehrer FROM T_StuPla WHERE Wochentag='.date('w',$Datum).
			" AND Stunde=$Stunde AND Turnus IN ('".implode("','",$Turnusliste).
			"') AND Version=$Version)".
			" OR Lehrer IN ('".implode("','",$freieLehrer[$Stunde]).
			"')) AND Version=$Version $LehrerAuswahl GROUP BY Lehrer";
			//AND Turnus IN ('".implode("','",$Turnusliste)."')".
			//" AND Version=$Version $Lehrerauswahl GROUP BY Lehrer";
		} // freie Lehrer suchen
		else
		{
			// Lehrer suchen, die gleichzeitig Unterricht haben
			$sql = 'SELECT DISTINCT Lehrer FROM T_StuPla WHERE Wochentag='.date('w',$Datum).
			" AND Stunde=$Stunde AND Turnus IN ('".implode("','",$Turnusliste).
			"') AND Version=$Version $Anzeigeoptionen ORDER BY Lehrer";
		}
		$query = mysql_query($sql);
		$WelcheLehrer = array();
		while ( $lehrer = mysql_fetch_array($query) )
		{
			$WelcheLehrer[] = $lehrer['Lehrer'];
		}
		mysql_free_result($query);
	} // alle Lehrer suchen
	if ( Count($WelcheLehrer) == 0 )
	echo '<tr><td colspan="10" align="center">Keine freien KollegInnen verfügbar!</td></tr>';
	else foreach ( $WelcheLehrer as $derLehrer )
	{
		// Verhinderte Lehrer entfernen
		if ( !istVerhindert($Datum, $derLehrer,$Stunde) )
		{
			$lehrer['Lehrer'] = $derLehrer;
			// Doppeleinträge im Stundenplan
			$lehrer[1]= berechneWochenstundenzahl($derLehrer, $Turnusliste, $Version);

			// Klassen und Fächer herausbekommen
			$sql ='SELECT Lehrer, Klasse, Raum, Fach, Stunde FROM T_StuPla '.
			'WHERE Version='.$Version.' AND Lehrer="'.$lehrer['Lehrer'].
			'" AND Wochentag='.
			date('w',$Datum).' AND Turnus IN ("'.implode('","',$Turnusliste).
			'") ORDER BY Stunde';
			$lquery = mysql_query($sql);
			$stunden = array();
			while ( $info = mysql_fetch_array($lquery))
			{
				$stunden[$info['Stunde']] = $info;
			}
			mysql_free_result($lquery);
			if ( Count($stunden) == 0 && $Unterricht )
			{
				// Lehrer überspringen: Kein Unterricht am Tag
			}
			elseif ( !$_SESSION['AnzeigeLehrer'] && $Anschliessend &&
			!isset($stunden[$Stunde-1]) && !isset($stunden[$Stunde+1]) )
			{
				// Lehrer überspringen: kein vorheriger/nachheriger Unterricht
			}
			else
			{
				echo '<tr><td ';
				if ( isVertretungVorhanden('Lehrer', $lehrer['Lehrer'], $Datum, $Stunde))
				echo 'class="Liste_VertretungVorhanden"';
				elseif (istGesperrt($Datum, $lehrer['Lehrer'], $Stunde))
				echo 'class="Liste_Gesperrt"';
		  echo '>';
		  echo '<a href="'.$_SERVER['PHP_SELF'].'?Neu='.$lehrer['Lehrer'];
		  echo '&Art=';
		  if  ($_SESSION['AnzeigeLehrer'] )
		  echo VERTRETUNG_ZUSATZKLASSE;
		  else
		  echo VERTRETUNG_VERTRETEN;
		  echo '&Stamp='.time().'" title="'.$lehrer['Lehrer'].' als Vertretung einsetzen">';
		  $lehrername = new Lehrer($lehrer['Lehrer'], LEHRERID_KUERZEL);
		  echo $lehrername->Name.' ('.$lehrer['Lehrer'].')</a>';
		  echo '</td>';
		  // 6 Blöcke anzeigen
		  // ggf. Vertretungen ergänzen
		  for ( $i = 1; $i < 7; $i++ )
		  {
		  	$TeilungPruefen = false;
		  	echo '<td ';
		  	if ( isVertretungVorhanden('Lehrer', $lehrer['Lehrer'], $Datum, $i) )
		  	echo 'class="Liste_VertretungVorhanden"';
		  	elseif ( in_array($lehrer['Lehrer'],$freieLehrer[$i]) )
		  	// Ausfall!
		  	echo 'class="Liste_Ausfall"';
		  	elseif ( isset($stunden[$i]) )
		  	{
		  		echo 'class="Liste_Unterricht"';
		  		$TeilungPruefen = true;
		  	}
		  	elseif ( istGesperrt($Datum, $lehrer['Lehrer'], $i))
		  	echo 'class="Liste_Gesperrt"';
		  	elseif ( $i == $Stunde )
		  	echo 'class="Liste_AktiveStunde"';
		  	if ( isset($stunden[$i]) )
		  	echo ' title="'.$stunden[$i]['Klasse'].' ('.$stunden[$i]['Fach'].') in '.
		  	$stunden[$i]['Raum'].'"';
		  	echo ">\n";
		  	if ($_SESSION['AnzeigeLehrer'] && $TeilungPruefen && ($i == $Stunde) )
		  	{
		  		$Teilung = holeTeilungsStunden($Datum, $Stunde, $stunden[$i]);
		  		if ( Count($Teilung) > 0 )
		  		{
		  			// wir nehmen nur den ersten Teilungslehrer für die Anzeige
		  			$Teilungslehrer = reset($Teilung);
		  			$Teilungslehrer = $Teilungslehrer['Lehrer'];
		  			echo '<a href="'.$_SERVER['PHP_SELF'].'?Neu='.$lehrer['Lehrer'];
		  			echo '&Art='.VERTRETUNG_ANDERERUNTERRICHT.'&Stamp='.time();
		  			echo '" title="'.$lehrer['Lehrer'].' aus der Teilung von ';
		  			echo $stunden[$i]['Klasse']."({$stunden[$i]["Fach"]}) mit $Teilungslehrer";
		  			echo ' nehmen und als Vertretung einsetzen">';
		  			echo "<img src=\"NoTeilung.png\" alt=\"Keine Teilung\"></a>\n";
		  		}
		  		elseif ( $_SESSION['Art'] == 'Lehrer' )
		  		{
		  			echo '<a href="'.$_SERVER['PHP_SELF'].'?Neu='.$lehrer['Lehrer'];
		  			echo ':'.$stunden[$i]['Klasse'];
		  			echo '&Art='.VERTRETUNG_TAUSCH.'&Stamp='.time();
		  			echo '" title="'.$_SESSION['Wofuer'].' mit '.$lehrer['Lehrer'];
		  			echo ' aus '.$stunden[$i]['Klasse'].'('.$stunden[$i]['Fach'].')';
		  			echo ' tauschen">';
		  			echo '<img src="Tausch.png" alt="Tausch"></a>'."\n";
		  		}
		  		else
		  		echo '&nbsp;';
		  	}
		  	else
		  	echo '&nbsp;';
		  	echo "</td>\n";
		  }
		  // Wochenstundenzahl anzeigen
		  echo '<td>'.($lehrer[1]*2);
		  // Vertretungsstunden addieren!!
		  $Vertretungsstunden = berechneVertretungsstunden($lehrer['Lehrer'],
		  $Montag, strtotime('+5 days',$Montag));
		  $Ausfallstunden = berechneAusfallstunden($lehrer['Lehrer'],
		  $Montag, strtotime('+5 days',$Montag));
		  $Doppelstunden = berechneVertretungsstunden($lehrer['Lehrer'],
		  $Montag, strtotime('+5 days',$Montag),
		  'AND Art IN '.VERTRETUNG_MEHRARBEITOHNESTATISTIK);
		$Nullstunden = berechneVertretungsstunden($lehrer['Lehrer'],
		  $Montag, strtotime('+5 days',$Montag),
		  'AND Art IN '.VERTRETUNG_OHNESTATISTIK);
		  $Vertretungsstunden -= $Nullstunden;  
		$Vertretungsstunden -= $Doppelstunden; // Doppelstunden zählen nicht als Vertretung
		  $VertretungWoche = $Vertretungsstunden-$Ausfallstunden;
		  if ( $VertretungWoche > 0 )
		  echo ' +'.($VertretungWoche*2);
		  elseif ( $VertretungWoche < 0 )
		  echo ' '.($VertretungWoche*2);
		  if ( $Doppelstunden > 0 )
		  echo ' ('.($Doppelstunden*2).')';
		  echo "</td>\n";
		  // Vertretungsanzahl anzeigen
		  echo '<td>';
		  $Vertretungsstunden = berechneVertretungsstunden($lehrer['Lehrer'],$SJahrBeginn);
		  $Ausfallstunden = berechneAusfallstunden($lehrer['Lehrer'],$SJahrBeginn);
		  $Doppelstunden = berechneVertretungsstunden($lehrer['Lehrer'],$SJahrBeginn,0,
		  'AND Art IN '.VERTRETUNG_MEHRARBEITOHNESTATISTIK);
		  $Nullstunden = berechneVertretungsstunden($lehrer['Lehrer'],
		  $SJahrBeginn, 0,
		  'AND Art IN '.VERTRETUNG_OHNESTATISTIK);
		  $Vertretungsstunden -= $Nullstunden;  
		$Vertretungsstunden -= $Doppelstunden; // Doppelstunden zählen nicht als Vertretung
		  echo ($Vertretungsstunden-$Ausfallstunden)*2;
		  if ( $Doppelstunden > 0 )
		  echo ' ('.($Doppelstunden*2).')';
		  echo '</td>';

		  echo '<td class="';
		  if ( $lehrername->Ueberstunden < 0 )
		  echo 'Liste_Ausfall';
		  elseif ($lehrername->Ueberstunden > 0 )
		  echo 'Liste_Gesperrt';
		  echo '">'.$lehrername->Ueberstunden.'</td>'."\n";

		  // Fächer / Klasse anzeigen
		  echo '<td>';
		  $lquery = mysql_query('SELECT DISTINCT Fach, Klasse FROM T_StuPla WHERE Lehrer="'.
		  $lehrer['Lehrer'].'" AND Version='.$Version);
		  $lfach = array();
		  $lklasse = array();
		  while ($fach = mysql_fetch_array($lquery) )
		  {
		  	if ( in_array($fach['Fach'], $Faecher) )
		  	$fach['Fach'] = '<strong>'.$fach['Fach'].'</strong>';

		  	if ( ! in_array($fach['Fach'],$lfach) ) $lfach[] = $fach['Fach'];
		  	if ( in_array($fach['Klasse'], $Klassen))
		  	$fach['Klasse'] = '<strong>'.$fach['Klasse'].'</strong>';
		  	if ( ! in_array($fach['Klasse'],$lklasse) ) $lklasse[] = $fach['Klasse'];
		  }
		  mysql_free_result($lquery);
		  // Anzeigen
		  if ( !$_SESSION['AnzeigeFach'] )
		  {
		  	echo implode(',',$lfach);
		  	if (Count($lklasse) != 0 && !$_SESSION['AnzeigeKlasse']) echo ' / ';
		  }
		  if ( !$_SESSION['AnzeigeKlasse'] )
		  echo implode(',',$lklasse);
		  echo '</td>';
		  echo '<td><a href="../StuPla/PlanAnzeigen.php?Lehrer='.$lehrer['Lehrer'].
		  '&ID_Woche='.$ID_Woche.'" target="_blank" title="Lehrerstundenplan '.
		  $lehrer['Lehrer'].' anzeigen">';
		  echo 'Plan</a></td>';
		  echo "</tr>\n";
			}
		}
	} // foreach
	echo "</table>\n";
}

/**
 * Schreibt einen Vertretungseintrag in die Datenbank.
 * @param $Original Feld mit den "alten" Einträgen (aus StuPla)
 * @param $Neu Feld mit den "neuen" Einträgen
 * @param $Datum Datum der Vertretung
 * @param $Stunde Stunde der Vertretung
 * @param $Grund Grund der Vertretung (siehe Konstanten)
 * @param $Bemerkung Bemerkung für diese Vertretung
 * @param $Verhinderung_id die ID der Verhinderung zu der diese Vertretung gehört
 * @param $VArt bisher nicht benutzt ...
 */
function VertretungEintragen($Original, $Neu, $Datum, $Stunde, $Grund, $Bemerkung,
$Verhinderung_id, $VArt)
{
	$Arten = array('Lehrer','Klasse','Raum', 'Fach');
	// Suche nach einem vorhandenen Eintrag
	$sql = "SELECT * FROM T_Vertretungen WHERE Datum=$Datum AND Stunde=$Stunde ";
	foreach ($Arten as $Art )
	$sql .= ' AND '.$Art.'_Neu="'.$Original[$Art].'"';
	$query = mysql_query($sql);
	if ( $row = mysql_fetch_array($query) )
	{
		$gleich = true;
		foreach ($Arten as $Art )
		if ($row[$Art] != $Neu[$Art]) $gleich = false;
		// falls $Neu = $Alt dann löschen
		if ( $gleich )
		mysql_query('DELETE FROM T_Vertretungen WHERE Vertretungen_id='.$row['Verhinderung_id']);
		else
		{
			// Update von _Neu mit $Neu
			$sql = 'UPDATE T_Vertretungen SET ';
			foreach ($Arten as $Art)
			$sql .= $Art.'_Neu="'.$Neu[$Art].'",';
			$sql .= 'Bearbeiter="'.$_SERVER['REMOTE_USER'].'",Grund='.$Grund.',Bemerkung="'.$Bemerkung.'"';
			mysql_query($sql);
		}
	}
	else
	{

	}
	mysql_free_result($query);
}

/**
 * Trägt eine neue Vertretung ein. Ggf. wird eine vorhandene Vertretung
 * korrigiert. Zum Entfernen einer Vertretung die entsprechende Konstante
 * VERTRETUNG_ENTFERNEN verwenden.
 *
 * @param Wofuer Das Feld welches geändert wird (Lehrer, Raum, Klasse, Fach)
 * @param Anzeige Der Wert des Feldes
 * @param Datum für welchen Tag
 * @param Stunde  für welchen Block
 * @param Neu Entweder ein einzelner Wert durch den Anzeige ersetzt wird oder ein
 * Feld mit den Indizes (Lehrer,Raum,Klasse,Fach) wenn mehrere Dinge ersetzt werden
 * sollen
 * @param Grund Der Grund für die Änderung (siehe Konstanten)
 * @param Bemerkung ein Hinweistext
 * @param VArt Art der Vertretung (auch LÖSCHEN) für Statistik (siehe Konstanten)
 *
 * @return Ein Feld mit Vertretung-IDs, die neu erstellt wurden
 */
function trageVertretungEin($Wofuer, $Anzeige, $Datum, $Stunde,
$Neu, $Grund=0, $Bemerkung='', $Verhinderung_id=0,
$VArt = 0, $iKey = '')
{
	$InsertIds = array();
	$eingetragen = false;
	$Arten = array('Lehrer','Klasse','Raum', 'Fach');
	$Gruende = array();
	$Wofuer = mysql_real_escape_string($Wofuer);
	$Anzeige = mysql_real_escape_string($Anzeige);
	$Bemerkung = mysql_real_escape_string($Bemerkung);
	foreach ( $Arten as $Art )
	$Gruende[$Art] = 0;
	// $Gruende zusammenbauen
	switch ( $Grund )
	{
		case RAUMZUSATZ:
		case RAUMWECHSEL:
			$Gruende['Raum'] = $Grund;
			break;
		case LEHRERKRANK:
		case LEHRERSONDER:
		case LEHRERDIENST:
		case LEHRERFORTBILDUNG:
			$Gruende['Lehrer'] = $Grund;
			break;
		case KLASSEWEG:
			$Gruende['Klasse']= KLASSEWEG;
			break;
	}
	$sql = 'SELECT * FROM T_Vertretungen WHERE Datum='.$Datum.' AND Stunde='.$Stunde;
	$sql .= ' AND ('.$Wofuer.'_Neu="'.$Anzeige.'"';
	//echo 'DEBUG '.date('d.m.Y',$Datum).':'.$Stunde.'<br >';
	if ( is_array($Neu) && Count($Neu) == 4 && $Neu['Klasse'] == '' &&
	$Neu['Lehrer'] == '' ) $sql .= ' OR '.$Wofuer.'="'.$Anzeige.'")';
	else
	$sql .= ')';
	// Bei Verlegungen muss immer ein neuer Eintrag erstellt werden!
	if ( ! in_array($VArt, explode(',',VERTRETUNG_VERLEGUNGEN)) )
	{
		$query = mysql_query($sql);
		// Finde alle Vertretungen zu diesem Zeitpunkt für die Änderung
		while ( $vertretung = mysql_fetch_array($query) )
		{
			// wir haben was gefunden:
			// bei Ausfall: Alles löschen und neu mit leeren Feldern
			$sql = 'UPDATE T_Vertretungen SET ';
			if ( is_array($Neu) )
			{
				foreach ( $Arten as $Art )
				{
					if ( isset($Neu[$Art]) )
					$sql .= $Art.'_Neu="'.ohneStern($Neu[$Art]).'",';
				}
			}
			else
			$sql .= $Wofuer.'_Neu="'.ohneStern($Neu).'",';
			if ( $vertretung['iKey'] != '' && ($vertretung['Art'] == VERTRETUNG_RAUMWECHSEL ||
			$vertretung['Art'] == VERTRETUNG_RAUMZUSATZ ))
			{
				// Eintrag stammt von einem Raumwechsel
				// iKey löschen und neuen Grund +Bemerkung schreiben
				$sql .= "iKey='',Art=$VArt,Bemerkung='".mysql_real_escape_string($Bemerkung);
				$sql .= "',GrundLehrer=".$Gruende['Lehrer'].',';
			}
			elseif ( $iKey != '' )
			{
				// jetzt kommt eine Raumreservierung hinzu
				// Gründe und Bemerkung sowie Verhinderung_id müssen erhalten bleiben
				$sql .= "iKey='$iKey'";
				$sql .= ",Bemerkung=CONCAT(Bemerkung,'\n".mysql_real_escape_string($Bemerkung)."'),";
			}
			if ( $Verhinderung_id != 0 )
			{
				// für den Fall dass zu einer Raumreservierung eine Verhinderung kommt
				$sql .= 'F_Verhinderung_id='.$Verhinderung_id.',';
			}
			// Ausfälle bleiben erhalten
			// bei allen anderen kann _Neu ersetzt werden
			$sql = substr($sql, 0, strlen($sql)-1);
			// echo 'DEBUG UP:'.$sql.'/'.$vertretung['Lehrer'].' '.$vertretung['Klasse'].'/'.date('d.m.Y',$vertretung['Datum']).'/'.$vertretung['Vertretung_id'].'<br>';
			if (! mysql_query($sql.' WHERE Vertretung_id='.$vertretung['Vertretung_id']) )
			echo '<div class="Fehler">UP:'.mysql_error().'/'.$sql.'</div>';
			$eingetragen = true;
		} // while Vertretungseintrag vorhanden
		mysql_free_result($query);
	} // wenn nicht Verlegung
	if ( ! $eingetragen )
	{
		$Ausfall = is_array($Neu) && $Neu['Lehrer'] == '' && $Neu['Klasse'] == '' &&
		$Neu['Raum'] == '' && $Neu['Fach'] == '';
		$sql = 'INSERT INTO T_Vertretungen (Datum,Stunde,Bearbeiter,';
		foreach ($Arten as $Art)
		$sql .= $Art.','.$Art.'_Neu,';
		foreach ($Arten as $Art )
		if ( $Art != 'Fach' )
		$sql .= "Grund$Art,";
		$sql .= 'Bemerkung,F_Verhinderung_id, Art, iKey) VALUES '.
		"($Datum,$Stunde,'{$_SERVER["REMOTE_USER"]}',";
		// Wenn es eine Verlegung ist, muss am angegebenen Zeitpunkt frei sein
		// da dort evtl. ein anderer Block ausfällt, darf der Stundenplan in diesem
		// Fall nicht berücksichtigt werden.
		// Das gilt aber nicht wenn es der "ausfallende" Block der Verlegung ist
		if ( ! in_array($VArt, explode(',',VERTRETUNG_VERLEGUNGEN)) || $Ausfall )
		$vertretungseintraege = liesStundenplanEintrag($Wofuer, $Anzeige,
		$Datum, $Stunde);
		else
		$vertretungseintraege = array();
		$vertretung = true;
		// Prüfen, ob alle gefundenen Einträge sinnvoll sind
		// hier werden v.a. Räume aussortiert, die frei geworden sind und eigentlich
		// von anderen belegt sind
		if ( $VArt == VERTRETUNG_RAUMZUSATZ || $VArt == VERTRETUNG_RAUMWECHSEL )
		{
			$neueintraege = array();
			foreach ( $vertretungseintraege as $eintrag )
			{
				foreach ( array('Lehrer','Klasse','Fach') as $Feld)
				if ( isset($Neu[$Feld]) && $eintrag[$Feld] != $Neu[$Feld] )
				unset($eintrag);
				if ( isset($eintrag)) $neueintraege[] = $eintrag;
			}
			$vertretungseintraege = $neueintraege;
		}
		if ( Count($vertretungseintraege) == 0 && ! $Ausfall )
		{
			// zusätzlicher Bedarf! Die betroffenen Felder vorbereiten
			foreach ( $Arten as $Art )
			$vertretungseintraege[0][$Art] = '';
			/*
			 $vertretungseintraege[0][$Wofuer] = $Anzeige;
			 if ( is_array($Neu) )
			 foreach ($Neu as $Art => $neuWert)
			 $vertretungseintraege[0][$Art] = ''; // $neuWert;
			 */
			$vertretung = false;

		}
		foreach ( $vertretungseintraege as $eintrag )
		{
			// Gleichheit muss vermieden werden. Ausnahme: Wenn nur eine Bemerkung eingegeben wird.
			$gleich = $vertretung && $Bemerkung == '' && $VArt != VERTRETUNG_NURBEMERKUNG;
			if ( !is_array($Neu))
			{
				if ( $Neu != $eintrag[$Wofuer])
				$gleich = false;
			}
			// Ursprungseintragung
			$sql2 = $sql;
			foreach ( $Arten as $Art )
			{
				// Sicherstellen, dass nur bei Veränderungen gespeichert wird
				if ( is_array($Neu))
				{
					if ( isset($Neu[$Art]) && $eintrag[$Art] != $Neu[$Art])
					$gleich = false;
				}
				// bei Zusatzraum: Originalraum bleibt leer!
				if ( $VArt != VERTRETUNG_RAUMZUSATZ || $Art != 'Raum')
				{
					// Original-Stundenplaneintrag einsetzen
					$sql2 .= "'".$eintrag[$Art]."',";
				}
				else
				$sql2 .= "'',";
				// Jetzt die Vertretung
				if ( is_array($Neu) )
				{
					if ( !isset($Neu[$Art]) )
					{
						// Originaleintrag übernehmen
						$sql2 .= "'".$eintrag[$Art]."',";
					}
					else
					$sql2 .= "'".$Neu[$Art]."',"; // um mehrere Einträge auf einmal zu ändern
				}
				elseif ( $Art != $Wofuer )
				{
					// Originaleintrag übernehmen
					$sql2 .= "'".$eintrag[$Art]."',";
				}
				else
				$sql2 .= "'".$Neu."',";
			}
			// Nun noch Gründe hinterher
			foreach ($Arten as $Art )
			if ( $Art != 'Fach' )
			$sql2 .= $Gruende[$Art].',';
			$sql2 .= "'$Bemerkung',$Verhinderung_id, $VArt,'$iKey')";
			if ( ! $gleich )
			{
				//echo 'DEBUG IN:'.$sql2.'/'.$eintrag['Lehrer'].'<br>';
				if ( ! mysql_query($sql2))
				echo '<div class="Fehler">IN:'.mysql_error()."</div>\n";
				else
				$InsertIds[] = mysql_insert_id();
			}
		}
	}
	return $InsertIds;
}

/**
 * Liest einen Stundenplaneintrag für einen Lehrer, Raum oder Klasse ein.
 * Vertretungen werden nicht berücksichtigt.
 * @param $Wofuer Zeichenkette Lehrer, Klasse oder Raum
 * @param $Anzeige Zeichenkette mit dem Kürzel des Lehrer, der Klasse oder des Raums
 * @param $Datum das Datum um das es geht
 * @param $Stunde die Stunde um die es geht
 * @return ein Feld von Stundenplaneinträgen oder ein leeres Feld
 */
function liesStundenplanEintrag($Wofuer, $Anzeige, $Datum, $Stunde)
{
	$Turnusliste = array();
	getTurnusliste(getID_Woche($Datum), $Turnusliste);
	$Version = getAktuelleVersion($Datum);
	$Turnusliste[] = 'jede Woche';
	$Anzeige = mysql_real_escape_string(RaumNummerOhnePunkt($Anzeige));
	$Was = mysql_real_escape_string($Was);
	$sql = "SELECT * FROM T_StuPla WHERE $Wofuer='$Anzeige' AND Turnus IN ('".
	implode("','",$Turnusliste)."') AND Wochentag=".date('w',$Datum).
	" AND Stunde=$Stunde AND Version=$Version";
	$eintraege = array();
	if ( ! $query = mysql_query($sql) )
	echo '<div class="Fehler">Fehler '.$sql.': '.mysql_error().'</div>';
	while ($eintrag = mysql_fetch_array($query))
	{
		$eintraege[] = $eintrag;
	}
	mysql_free_result($query);
	return $eintraege;
}

function hatUnterricht($eintraege, $Wofuer, $Anzeige)
{
	$hatUnterricht = false;
	foreach ($eintraege as $eintrag)
	{
		if ( ! is_array($eintrag[$Wofuer]) )
		{
			if ( $eintrag[$Wofuer] == $Anzeige ) $hatUnterricht = true;
		}
		elseif ( in_array($Anzeige, $eintrag[$Wofuer]) ) $hatUnterricht = true;
	}
	return $hatUnterricht;
}

/**
 * Lies für einen bestimmten Block alle Eintraege aus dem Stundenplan.
 * Berücksichtigt evtl. vorliegenden Vertretungen
 *
 * @param $Wofuer Art der Anzeige (Lehrer, Klasse, Raum, Fach)
 * @param $Anzeige für wen oder was soll der Plan geholt werden
 * @param $Datum das Datum
 * @param $Stunde die Stunde
 * @return ein Feld von Stundenplaneinträgen. Indizes _Org und _Ver sind gesetzt
 */
function liesStundenplanEintragMitVertretung($Wofuer, $Anzeige, $Datum, $Stunde)
{
	$eintraege = liesStundenplanEintrag($Wofuer, $Anzeige, $Datum, $Stunde);
	// Vertretungen prüfen
	$sql = "SELECT * FROM T_Vertretungen WHERE Datum=$Datum AND Stunde=$Stunde AND ".
	"($Wofuer='$Anzeige' OR {$Wofuer}_Neu='$Anzeige')";
	$query = mysql_query($sql);
	while ( $vertretung = mysql_fetch_array($query) )
	{
		// Vertretung einbauen
		$eingetragen = false;
		foreach ( $eintraege as $key => $eintrag)
		// schaue nach ob eine passende Vertretung vorhanden ist und der Eintrag
		// seinerseits keine Vertretung ist (Vertretung für Vertretung gibt es nicht)
		if ( isVertretung($eintrag, $vertretung) && ! isset($eintrag['Vertretung_id']) )
		{
			$eintraege[$key] = ersetzeStundenplanEintrag($eintrag, $vertretung,
			$Wofuer, $Anzeige);
			$eingetragen = true;
		}
		if ( ! $eingetragen )
		{
			$eintraege[] = ersetzeStundenplanEintrag($vertretung, $vertretung,
			$Wofuer, $Anzeige);
		}
	}
	mysql_free_result($query);
	return $eintraege;
} // liesStundenplanEintragMitVertretung

/**
 * Ermittelt alle Teilungslehrer eines Stundenplaneintrages
 * Vertretungen werden berücksichtigt
 * @param $Datum das Datum
 * @param $Stunde die Stunde
 * @param $eintrag der Stundenplaneintrag des Lehrers, dessen Teilungslehrer
 *                 gesucht werden
 *
 * @return ein Feld von Stundenplaneinträgen der Teilungslehrer
 */
function holeTeilungsStunden($Datum, $Stunde, $eintrag)
{
	// Finde Einträge der gleichen Klasse und gleichen Faches aber anderen
	// Lehrers
	$Eintraege = liesStundenplanEintragMitVertretung('Klasse',
	$eintrag['Klasse'], $Datum, $Stunde);
	$dieEintraege = array();
	foreach ( $Eintraege as $derEintrag )
	{
		if ( $derEintrag['Klasse'] == $eintrag['Klasse'] &&
		$derEintrag['Fach'] == $eintrag['Fach'] &&
		$derEintrag['Lehrer'] != $eintrag['Lehrer'])
		$dieEintraege[] = $derEintrag;
	}
	return $dieEintraege;
}

/**
 * Liest den Studenplan ein
 * @param mitAusfaellen - Ausfälle werden angezeigt
 * @param faecher - nur die im Feld enthaltenen Fächer werden angezeigt
 */
function liesPlanEin($db, $Wofuer, $Anzeige, $DatumTurnus='', $mitAusfaellen=true, $faecher=array())
{
	// Wofuer: Lehrer, Raum
	// Anzeige: der Wert (bisher Raum, Lehrer, Klasse
	// DatumTurnus: SQL-Turnusse wenn allgemeine Anzeige, sonst ein TimeStamp für das
	//   das Datum des Montags der Woche

	// es fehlt eine Prüfung ob Anzeige überhaupt existiert!
	// Problematisch bei der Raumreservierung!

	// Sicherheitsabfrage: Wenn nicht im Lehrerbereich, dann nur Klassenplan darstellen
	/* TODO: Sicherheitsabfragen entfernen
  if ( $_SERVER['HTTP_HOST'] != 'lehrer.bscw-oszimt.de' && $Wofuer != 'Klasse' )
  return 'Ungültige Auswahl!';
  */
	$Plan = array();
	$Plan['Anzeige'] = trim($Anzeige);
	$Plan['Wofuer'] = trim($Wofuer);
	if ( pruefeZeichen($Anzeige) || pruefeZeichen($Wofuer) || pruefeZeichen($DatumTurnus) )
	return 'Falsche Parameter!';
	if ( ! in_array($Wofuer, array('Lehrer', 'Klasse', 'Raum') ))
	return 'Ungültige Planauswahl!';
	if ( is_numeric($DatumTurnus) )
	{
		$sql = 'SELECT Woche, Montag FROM T_Woche WHERE ID_Woche ='.$DatumTurnus;
		$ID_Woche = $DatumTurnus;
		if ( ! $rs = mysql_query($sql,$db) )
		return 'Falsche Wocheninformation übergeben!';
		$row = mysql_fetch_row($rs);
		mysql_free_result($rs);
		$Woche  = $row[0];
		$Datum = $row[1];
		$sqlTurnus = '';
		$Plan['ID_Woche'] = $DatumTurnus;
		$Plan['Datum'] = $Datum;
		$Plan['Woche'] = $Woche;
		//Initialisierungen
		for($i = 1; $i <= 5; $i++)
		$Plan['freierTag'][$i] = false;
		for($Wochentag = 0; $Wochentag <= 4; $Wochentag++)
		{
			$dieserTag = strtotime("+$Wochentag day",$Datum);//Berechnung des Unix-Timestamp
			$sql = "SELECT Kommentar FROM T_FreiTage WHERE ersterTag <= $dieserTag AND letzterTag >= $dieserTag;";
			$rs = mysql_query($sql, $db);
			if(mysql_num_rows($rs) != 0)//Freier Tag
			{
				$row = mysql_fetch_row($rs);
				$Plan['freierTag'][$Wochentag+1] = true;
				$Plan['Kommentar'][$Wochentag+1] = $row[0];
			}
			mysql_free_result($rs);
		}
	}
	else
	{
		$Datum = -1;
		$sqlTurnus = 1;
		$Plan['Turnusse'] = array();
		if (is_array($DatumTurnus) && Count($DatumTurnus) != 0 )
		{
			$sqlTurnus = "Turnus='".implode("' OR Turnus='",$DatumTurnus)."'";
			$Plan['Turnusse'] = $DatumTurnus;
		}
		else
		$Plan['Turnusse'] = array('alle');
	}
	$OnlineVersion = getAktuelleVersion($Datum);
	if($OnlineVersion == -1)//Wenn keine Version Fehler
	return 'Zur Zeit sind keine Daten abrufbar!';
	if ( $Datum != -1 )
	{
		// falls Wochenende
		$WEVersion = getAktuelleVersion(strtotime('+2 day', $Datum));
		// wir haben Samstag oder Sonntag und am Montag wechselt der Plan
		if ( $WEVersion != $OnlineVersion )
		$OnlineVersion = $WEVersion;
	}

	$Plan['Version'] = $OnlineVersion;
	//Gueltigkeits-Timestamp der aktuellsten Version aus DB holen
	$Plan['GueltigAb'] = getGueltigAbVersion($OnlineVersion);
	// Wenn kein Datum Fehler
	if ( $Plan['GueltigAb'] == -1 )
	return 'Zur Zeit sind keine Daten abrufbar!';
	$aktuelleVersion = getAktuelleVersionWE();
	if($OnlineVersion == $aktuelleVersion)
	{
	$Plan['linkVersion'] = getNaechsteVersion($OnlineVersion);
	}
	else
	{
	$Plan['linkVersion'] = $aktuelleVersion;
	}
	//Gueltigkeits-Timestamp der anderen Version aus DB holen
	if($Plan['linkVersion'] != -1)
	{
	$Plan['naechsteGueltigAb'] = getGueltigAbVersion($Plan['linkVersion']);
	}
	if  ($Wofuer == 'Raum')
	{
		// Beziehung zwischen Winschool-Anzeige und interner Anzeige sicherstellen
		$Anzeige = RaumnummerOhnePunkt($Anzeige);
	}
	if ( $Wofuer == 'Lehrer' )
	{
		//Lehrername einlesen
		$sql = 'SELECT DISTINCT Name, Vorname FROM T_StuPla '.
		"WHERE Lehrer = \"$Anzeige\" AND Version = $OnlineVersion;";
		$rs = mysql_query($sql,$db);
		if ( ! $row = mysql_fetch_row($rs) )
		return 'Ungültiger Lehrername!';
		mysql_free_result($rs);
		$Plan['Name'] = $row[0];
		$Plan['Vorname'] = $row[1];
	}
	if ( $Datum != -1 )
	{
		getTurnusliste($ID_Woche, $Plan['Turnusse']);
		$Plan['Turnusse'][] = 'jede Woche';
		$sqlTurnus = "Turnus='".implode("' OR Turnus='",$Plan['Turnusse'])."'";
		//ID der naechsten KW ermitteln
		$Plan['ID_naechsteWoche'] = getID_Anschluss_KW($ID_Woche, '+');
		//ID der letzten KW ermitteln
		$Plan['ID_letzteWoche'] = getID_Anschluss_KW($ID_Woche, '-');
		if ( $Wofuer != 'Klasse' )
		{
			require_once('include/Termine.class.php');
			$Termine = new Termine($db);
			$Filter = array_keys($Termine->Betroffen);
		}
	}
	//StuPla abfragen
	$Vertretungen = array();
	for ( $Wochentag = 1; $Wochentag <= 5; $Wochentag++)
	{
		if ( $Datum != -1 )
		{
			$Tag = strtotime('+'.($Wochentag-1).' day',$Datum);
			$Plan[$Wochentag][0] = $Tag;
			if ( $Wofuer != 'Klasse' )
			{
				// Termine suchen
				$sql = "SELECT *,DATE_FORMAT(Stand,'%d.%m.%Y %H:%i') AS St ".
				'FROM T_Termin_Termine INNER JOIN T_Termin_Klassifikationen ON '.
				'Klassifikation_id=F_Klassifikation WHERE Datum BETWEEN '.$Tag.
				' AND '.strtotime('+22 hours',$Tag).' AND NOT Vorlaeufig ORDER BY Datum';
				if (! $query = mysql_query($sql, $db)) echo mysql_error();
				while ( $termin = mysql_fetch_array($query) )
				{
					$art = explode(',',$termin['Betroffene']);
					if ( Count(array_intersect($art, $Filter)) > 0 )
					$Plan['Termine'][$Wochentag][] = $termin;
				}
				mysql_free_result($query);
			}
		} // wenn Datum angegeben
		for ( $Block = 1; $Block <= 6; $Block++)
		{
			$Plan[$Wochentag][$Block] = array();
			$sql = 'SELECT * FROM T_StuPla';
			$sql .= " WHERE $Wofuer = \"$Anzeige\"";
			$sql .= " AND Stunde=$Block";
			$sql .= " AND Wochentag=$Wochentag";
			$sql .= " AND Version = $OnlineVersion";
			$sql .= " AND ($sqlTurnus) ORDER BY Turnus;";
			$rs = mysql_query($sql, $db);
			while($row = mysql_fetch_array($rs))//Fuer den Fall das Mehrfacheintraege vorhanden sind
			{
				if ( Count($faecher)== 0 || in_array($row['Fach'],$faecher)){
					// Auf Vertretung prüfen
					if ( $Datum != -1 )
					{

						$row = pruefeVertretung($row, $Tag, $Plan['Wofuer'], $Plan['Anzeige']);
						 
						if ( ! isset($Plan[$Wochentag][$Block][$row['Turnus']]['Vertretungen']) )
						$Plan[$Wochentag][$Block][$row['Turnus']]['Vertretungen'] = array();
						if ( isset($row['Vertretungen']) )
						{
							$Plan[$Wochentag][$Block][$row['Turnus']]['Vertretungen'] =
							array_merge($Plan[$Wochentag][$Block][$row['Turnus']]['Vertretungen'],
							$row['Vertretungen']);
							$Vertretungen = array_merge($Vertretungen, $row['Vertretungen']);
							$Plan[$Wochentag][$Block][$row['Turnus']]['Bemerkung'] = $row['Bemerkung'];
						}
					}
					else
					{
						if ( ! isset($Plan[$Wochentag][$Block][$row['Turnus']]['Vertretungen']) )
						$Plan[$Wochentag][$Block][$row['Turnus']]['Vertretungen'] = array();
					}
					foreach ( array('Fach', 'Raum', 'Klasse', 'Lehrer') as $Feld )
					{
						// Nach Turnus trennen, damit nach Turnus geteilt werden kann
						if ( ! isset($Plan[$Wochentag][$Block][$row['Turnus']][$Feld]) ||
						! is_array($Plan[$Wochentag][$Block][$row['Turnus']][$Feld]) )
						{
							$Plan[$Wochentag][$Block][$row['Turnus']][$Feld] = array();
							$Plan[$Wochentag][$Block][$row['Turnus']][$Feld.'_Org'] = array();
						}
						if ( istEinzutragen($row[$Feld], $Plan[$Wochentag][$Block][$row['Turnus']][$Feld]))
						$Plan[$Wochentag][$Block][$row['Turnus']][$Feld][] = $row[$Feld];
						/* Originaleinträge und Vertretung gibt es an dieser Stelle gar nicht !*/
						 if ( isset($row[$Feld.'_Org']) && istEinzutragen($row[$Feld.'_Org'],
						 $Plan[$Wochentag][$Block][$row['Turnus']][$Feld.'_Org']))
						 $Plan[$Wochentag][$Block][$row['Turnus']][$Feld.'_Org'][] = $row[$Feld.'_Org'];
						 if ( isset($row[$Feld.'_Ver']))
						 $Plan[$Wochentag][$Block][$row['Turnus']][$Feld.'_Ver'] = $row[$Feld.'_Ver'];
						
					}
					// Sonderfall: Teilungslehrer hinzufügen
					if ( $Wofuer == 'Lehrer' )
					{
						$sql = 'SELECT * FROM T_StuPla';
						$sql .= " WHERE Stunde = $Block";
						$sql .= " AND Wochentag = $Wochentag";
						$sql .= " AND Fach = '".$row["Fach"]."'";
						$sql .= " AND Klasse = '".$row["Klasse"]."'";
						$sql .= " AND Lehrer != '$Anzeige'";
						$sql .= " AND Version = $OnlineVersion";
						$sql .= " AND ($sqlTurnus) ORDER BY Lehrer;";
						$rsLehrer = mysql_query($sql, $db);
						while ( $lehrer = mysql_fetch_array($rsLehrer) )
						{
							// Auf Vertretung prüfen
							if ( $Datum != -1 )
							{
								$lehrer = pruefeVertretung($lehrer,$Tag, 'Klasse', $lehrer['Klasse']);
								if ( isset($lehrer['Vertretungen']) )
								{
									$Plan[$Wochentag][$Block][$lehrer['Turnus']]['Vertretungen'] =
									array_merge($Plan[$Wochentag][$Block][$lehrer['Turnus']]['Vertretungen'],
									$lehrer['Vertretungen']);
									$Vertretungen = array_merge($Vertretungen, $lehrer['Vertretungen']);
									$Plan[$Wochentag][$Block][$lehrer['Turnus']]['Bemerkung'] = $lehrer['Bemerkung'];
								}
							}
							foreach ( array('Fach', 'Lehrer', 'Klasse', 'Raum') as $Feld )
							{
								if ( isset($lehrer[$Feld.'_Org']) &&
								istEinzutragen($lehrer[$Feld.'_Org'],
								$Plan[$Wochentag][$Block][$lehrer['Turnus']][$Feld.'_Org']))
								$Plan[$Wochentag][$Block][$lehrer['Turnus']][$Feld.'_Org'][] = $lehrer[$Feld.'_Org'];
								if ( isset($lehrer[$Feld.'_Ver']))
								$Plan[$Wochentag][$Block][$lehrer['Turnus']][$Feld.'_Ver'] = $lehrer[$Feld.'_Ver'];
							}
							if ( is_array($Plan[$Wochentag][$Block][$lehrer['Turnus']]['Klasse']) &&
							(in_array($lehrer['Klasse'],
							$Plan[$Wochentag][$Block][$lehrer['Turnus']]['Klasse']) ||
							in_array('*'.$lehrer['Klasse'],
							$Plan[$Wochentag][$Block][$lehrer['Turnus']]['Klasse']) ||
							in_array(ohneStern($lehrer['Klasse']),
							$Plan[$Wochentag][$Block][$lehrer['Turnus']]['Klasse'])))
							{
								if ( istEinzutragen($lehrer['Lehrer'], $Plan[$Wochentag][$Block][$lehrer['Turnus']]['Lehrer']))
								$Plan[$Wochentag][$Block][$lehrer['Turnus']]['Lehrer'][] =$lehrer['Lehrer'];
								if ( istEinzutragen($lehrer['Raum'], $Plan[$Wochentag][$Block][$lehrer['Turnus']]['Raum']))
								$Plan[$Wochentag][$Block][$lehrer['Turnus']]['Raum'][]=$lehrer['Raum'];
							}
						}
						mysql_free_result($rsLehrer);
					}
				} // wenn Fach berücksichtigt werden soll
			} // while
			mysql_free_result($rs);
			// Auf zusätzliche Einträge prüfen
			// aber nur, wenn eine Datumsbezogene Anzeige erfolgt
			if ( $Datum != -1 )
			{
				$zusatzeintraege = pruefeZusatzBlock($Tag, $Block, $Wofuer, $Anzeige, $Vertretungen);
				foreach ($zusatzeintraege as $eintrag )
				{
					// ergänzeEinträge
					if ( Count($faecher) == 0 || in_array($eintrag['Fach'],$faecher))
					{
						$Felder = array('Lehrer', 'Klasse', 'Raum', 'Fach');
						$eingetragen = false;
						foreach ( $Plan[$Wochentag][$Block] as $Turnus => $Eintraege )
						{
							$gleich = 0;
							// Anzahl der Übereinstimmungen berechnen
							foreach ( $Felder as $Feld )
							{
								if ( in_array($eintrag[$Feld.'_Neu'], $Eintraege[$Feld]) ||
								in_array('*'.$eintrag[$Feld.'_Neu'], $Eintraege[$Feld]) ||
								in_array(ohneStern($eintrag[$Feld.'_Neu']), $Eintraege[$Feld]))
								$gleich++;
							}
							// wenn mehr als ein Feld gleich ist, dann einen zusätzlichen Eintrag zu
							// vorhandenem Block machen
							if ( $gleich >= 2 )
							{
								foreach ( $Felder as $Feld )
								{
									if ( istEinzutragen($eintrag[$Feld],$Plan[$Wochentag][$Block][$Turnus][$Feld]))
									$Plan[$Wochentag][$Block][$Turnus][$Feld][] = $eintrag[$Feld];
									if ( istEinzutragen($eintrag[$Feld.'_Org'],
									$Plan[$Wochentag][$Block][$Turnus][$Feld.'_Org']))
									$Plan[$Wochentag][$Block][$Turnus][$Feld.'_Org'][] = $eintrag[$Feld.'_Org'];
									if ( isset($eintrag[$Feld.'_Ver']))
									$Plan[$Wochentag][$Block][$Turnus][$Feld.'_Ver'] = $eintrag[$Feld.'_Ver'];
								}
								$Plan[$Wochentag][$Block][$Turnus]['Bemerkung'] = $eintrag['Bemerkung'].'';
								$Plan[$Wochentag][$Block][$Turnus]['Vertretungen'][] = $eintrag['Vertretung_id'];
								$Plan[$Wochentag][$Block][$Turnus]['F_Vertretungen'][] = $eintrag['F_Vertretung_id'];
								$eingetragen = true;
							}
						}
						// Nicht eingetragen - offenbar handelt es sich um einen Block, der
						// extra auszuweisen ist
						if ( ! $eingetragen )
						{
							if ( ohneStern($eintrag[$Wofuer]) != $Anzeige ) $Anhaengsel = '_Neu';
							else $Anhaengsel = '';
							foreach ( $Felder as $Feld )
							{
								if ( ! is_array($Plan[$Wochentag][$Block]['Zusatz'][$Feld]))
								{
									$Plan[$Wochentag][$Block]['Zusatz'][$Feld] = array();
									$Plan[$Wochentag][$Block]['Zusatz'][$Feld.'_Org'] = array();
								}
								if ( istEinzutragen($eintrag[$Feld.$Anhaengsel],$Plan[$Wochentag][$Block]['Zusatz'][$Feld]))
								$Plan[$Wochentag][$Block]['Zusatz'][$Feld][] = $eintrag[$Feld.$Anhaengsel];
								if ( istEinzutragen($eintrag[$Feld.'_Org'],
								$Plan[$Wochentag][$Block]['Zusatz'][$Feld.'_Org']))
								$Plan[$Wochentag][$Block]['Zusatz'][$Feld.'_Org'][] = $eintrag[$Feld.'_Org'];
								if ( isset($eintrag[$Feld.'_Ver']))
								$Plan[$Wochentag][$Block]['Zusatz'][$Feld.'_Ver'] = $eintrag[$Feld.'_Ver'];
								//$Plan[$Wochentag][$Block]['Zusatz'][$Feld][] = $eintrag[$Feld.$Anhaengsel];
							} // foreach
							// Leere Zeichenkette damit Bemerkung auf jeden Fall definiert ist
							$Plan[$Wochentag][$Block]['Zusatz']['Bemerkung'] = $eintrag['Bemerkung'].'';
							$Plan[$Wochentag][$Block]['Zusatz']['Vertretungen'][] = $eintrag['Vertretung_id'];
							$Plan[$Wochentag][$Block]['Zusatz']['F_Vertretungen'][] = $eintrag['F_Vertretung_id'];
						}
					}
				} // für alle Zusatzeinträge
			} // wenn Datum nicht -1
			// Aufälle anzeigen oder nicht
			if ( ! $mitAusfaellen )
			{
				$ausfall = false;
				foreach ($Plan[$Wochentag][$Block] as $Turnus => $Turnusse )
				if ( isAusfall($Turnusse) )
				unset($Plan[$Wochentag][$Block][$Turnus]);
			}
		} // for Block
	} // for Wochentag
	return $Plan;
}

function LinkEinfuegen($Feld, $Eintrag, $params, $link_table, $Vertretung=false)
{
	echo "<a class = \"$link_table\" href=\"";
	if ( $Vertretung )
	{
		echo '../StuPla/PlanAnzeigen.php';
	}
	else
	echo $_SERVER['PHP_SELF'];
	echo "?$Feld=".urlencode($Eintrag);
	echo $params.'" ';
	echo 'target="_blank"';
	if ( $Feld == 'Raum' )
	{
		// Zeige Raumkapazität an
		$Raum2 = Raumbezeichnung($Eintrag);
		$sql = 'SELECT Raumbezeichnung, Beschreibung, Kapazitaet FROM T_Raeume '.
		"WHERE Raumnummer='".mysql_real_escape_string($Raum2)."'";
		if (!$query = mysql_query($sql)) echo mysql_error();
		if ( !$r = mysql_fetch_array($query) )
		{
			$r['Raumbezeichnung'] = '';
			$r['Kapazitaet'] = '';
			$r['Beschreibung'] = '';
		}
		mysql_free_result($query);
		if ( is_numeric($r['Kapazitaet']) && $r['Raumbezeichnung'] != '' )
		{
			echo 'title="'.stripslashes($r['Raumbezeichnung']).' ('.
			$r['Kapazitaet'].' Schülerplätze';
			$canz = ComputerAnzahl($Raum2);
			if ( $canz != 0 ) echo " / $canz Computer";
			echo ')"';
		}
	}
	echo ">\n";
	echo $Eintrag;
	echo '</a>';
}

/**
 *  @param $Plan ein Feld mit dem gesamten Plan
 *  @param $Felder ein Zeichenketten-Feld mit den Feldern, die angezeigt werden sollen
 *  @param $Wochentag der Wochentag um den es geht
 *  @param $Block der Block um den es geht
 *  @param $Vertretung 0-wenn nur Anzeige, 1-Anzeige linke Spalte, 2-Anzeige großer Plan
 *  @return Gibt die Anzahl der belegten Einträge zurück
 */
function SchreibePlanEintraege($Plan, $Felder, $Wochentag, $Block, $Vertretung = 0)
{
	$anz = 0;
	foreach ( $Plan[$Wochentag][$Block] as $Turnus => $Eintraege )
	{
		if ( $anz > 0 ) echo '<br />';
		if ( isset($Eintraege['Bemerkung']) )
		{
			if ( $Vertretung != 0 )
			{
				echo '<div class="BemerkungsIcon"><a href="VertretungBemerkung.php';
				echo "?Vertretung_id=".implode(",",$Eintraege["Vertretungen"]);
				echo '" target="_blank" title="Beschreibung ändern">';
				echo '<img src="edit_small.gif" alt="Beschreibung ändern" '.
				'/></a>'."\n";
				if ( $Vertretung == 1 )
				{
					echo '<br /><a href="VertretungHaendisch.php';
					echo "?Vertretung_id=".implode(",",$Eintraege["Vertretungen"]);
					echo "&VonStunde=$Block";
					echo '" target="_blank" title="Vertretung per Hand eingeben">';
					echo '<img src="hand.gif" alt="Hand" /></a>'."\n";
				}
				echo "</div>\n";
			}
			echo '<div class="Stundenplan_Aenderung" ';
			// Originaleinträge und ggf. Vertretung anzeigen
			$s = '';
			foreach($Felder as $Feld)
			{
				if (is_array($Eintraege[$Feld.'_Org']))
				$s .= $Feld.': '.implode(',',$Eintraege[$Feld.'_Org']);
				if ( isset($Eintraege[$Feld.'_Ver']))
				{
					$s .= ' &rarr; '.$Eintraege[$Feld.'_Ver'];
					if ( $Eintraege[$Feld.'_Ver'] == '' )
					$s .= '(entfällt)';
				}
				$s .= '<br />';
			}
			//if ( $Vertretung != 0 )
			//{
			echo 'onMouseOver="return overlib('."'";
			if ( trim($Eintraege['Bemerkung']) != '' )
			echo htmlentities(str_replace("\r",'',str_replace("\n",'',
			stripslashes(nl2br($Eintraege['Bemerkung']))))).'<br />';
			echo $s;
			echo "',CAPTION,'Originaleintrag');";
			echo '" onMouseOut="return nd();"';
			//}
			//else
			//  echo 'title="'.$Eintraege["Bemerkung"].'" ';
			echo ">\n";
			if ( trim($Eintraege['Bemerkung']) != '' )
			echo nl2br($Eintraege['Bemerkung'])."<br />\n";
		}
		else
		{
			if ( $Vertretung != 0 )
			{
				$Tag = strtotime('+'.($Wochentag-1).' days',$Plan['Datum']);
				$VertretungInfo = $Tag;
				$VertretungInfo .= ",$Block,";
				$VertretungInfo .= reset($Eintraege['Lehrer']);
				echo '<div class="BemerkungsIcon">';
				echo '<a href="VertretungBemerkung.php';
				echo '?Vertretung_id=-1&VertretungInfo='.$VertretungInfo;
				echo '" target="_blank" title="Beschreibung hinzufügen">';
				echo '<img src="edit_small.gif" alt="Stift" /></a><br />';
				if ( $Vertretung == 2 )
				{
					echo '<a href="'.$_SERVER['PHP_SELF'].'?Art='.
					VERTRETUNG_AUSFALLOHNEBERECHNUNG.
					"&VonStunde=$Block&VonDatum=$Tag&Neu=Klasse:".reset($Eintraege["Klasse"]).
					'&Stamp='.time().
					'" title="Block ausfallen lassen (ohne Statistik)">' .
					'<img src="delete.gif" alt="rotes Kreuz" /></a><br />';
				}
				elseif ( $Vertretung == 1 )
				{
					echo '<a href="VertretungHaendisch.php';
					echo '?Vertretung_id=-1&VonStunde='.$Block;
					echo '" target="_blank" title="Vertretung per Hand eingeben">';
					echo '<img src="hand.gif" alt="Hand" /></a><br />'."\n";
				}
				echo "</div>\n";
			}
			echo '<div class="Stundenplan_Normal">';
		}
		$Ausfall = true;
		foreach ( $Felder as $Feld )
		{
			$eanz = 0;
			echo '<div class="Stundenplan_'.$Feld.'">';
			foreach ( $Eintraege[$Feld] as $Eintrag )
			{
				if ( $eanz > 0 ) echo ' / ';
				if ( strpos($Eintrag, '*') !== false )
				{
					// Änderung vom normalen Stundenplan
					echo '&rarr;';
					$Eintrag = ohneStern($Eintrag); // * wegschneiden
				}
				// Links einfügen
				if (  isset($Plan[$Feld.'Verschiebbar']) &&
				is_array($Plan[$Feld.'Verschiebbar']) &&
				in_array($Eintrag,$Plan[$Feld.'Verschiebbar']) )
				echo '<span class="Eintrag_verschiebbar">';
				if ( $Plan['Wofuer'] != 'Klasse' && $Feld != 'Fach' )
				{
					if ( isset($Plan['Datum']) )
					$params = '&ID_Woche='.$Plan['ID_Woche'];
					else
					$params = '&Version='.$Plan['Version'];
					if ( $Vertretung != 0 )
					$params = '&ID_Woche='.$Plan['ID_Woche'];
					$link_table = 'linktable'; // Für Druckversion ändern
					LinkEinfuegen($Feld, $Eintrag, $params, $link_table, $Vertretung!=0);
				}
				else
				{
					echo $Eintrag;
					// Links für Teilung aufheben bei 'Unbetroffenen' einfügen
					if ( $Feld == 'Lehrer' && Count($Eintraege[$Feld]) > 1 &&
					$Vertretung != 0)
					{
						echo '<a href="'.$_SERVER['PHP_SELF'].'?Art=';
						if ( Count($Eintraege['Fach']) == 1 )
						echo VERTRETUNG_TEILUNGAUFHEBEN;
						else
						echo VERTRETUNG_AUSFALLOHNEBERECHNUNG;
						echo "&VonStunde=$Block&VonDatum=$Tag&Neu=Lehrer:$Eintrag&Stamp=".
						time().'" title="Lehrer bekommt frei">' .
						'<img src="noperson.gif" alt="Durchgestrichene Person" /></a>';
					}
				}
				if ( $Eintrag != '' ) $Ausfall = false;
				if ( isset($Plan[$Feld.'Verschiebbar']) &&
				is_array($Plan[$Feld.'Verschiebbar']) &&
				in_array($Eintrag,$Plan[$Feld.'Verschiebbar']) )
				echo "</span>\n";
				$eanz++;
			} // foreach Einträge
			if ( Count($Eintraege[$Feld]) == 0 ) echo '&nbsp;'; // Zeile füllen
			if ( $Turnus != 'jede Woche' && $Feld == 'Klasse' && $eanz > 0 )
			echo "($Turnus)";   // Turnus angeben
			echo "</div>\n";
		} // foreach Felder
		if ( ! $Ausfall ) $anz++;
		echo "</div>\n";
		if ( $Vertretung != 0 )
		{
			echo '<a href="'.$_SERVER['PHP_SELF'].'?Art=';
			$eintragDa = false;
			if ( ! isset($Eintraege['Bemerkung']) ||
			($Vertretung==1 && Count($Eintraege['Lehrer'])>=1 &&
			$Plan['Wofuer']=='Lehrer') )
			{
				if ( $Vertretung == 1 )
				{
					$eintragDa = true;
					if ( Count($Eintraege['Lehrer']) == 1 )
					echo VERTRETUNG_AUSFALL.'&Stunde='.$Block.'&Stamp='.time().
					'" title="Block ausfallen lassen">Ausfall';
					elseif ( $Plan['Wofuer'] == 'Lehrer' )  // Nur wenn Lehrer bekannt ist,
					{
						echo VERTRETUNG_TEILUNGAUFHEBEN.'&Stunde='.$Block.'&Neu=';
						$Lehrer = array();
						foreach ( $Eintraege['Lehrer'] as $derLehrer )
						if ( $derLehrer != $Plan['Anzeige'] && ! in_array($derLehrer, $Lehrer) )
						$Lehrer[] = $derLehrer;
						// kann Teilung aufgehoben werden
						echo implode(',',$Lehrer).'&Stamp='.time().
						'" title="Teilung aufheben ('.implode(",",$Lehrer).
						' allein)">Teilung aufheben';
						echo '</a><br />'."\n";
						echo '<a href="'.$_SERVER['PHP_SELF'].'?Art=';
						echo VERTRETUNG_TEILUNGAUSFALL.'&Stunde='.$Block;
						echo '&Stamp='.time().
						'" title="Teilungsgruppe '.$_SESSION['Wofuer'].' ausfallen lassen (Gruppe '.
						implode(",",$Lehrer).' bleibt)">Teilung fällt aus';
					}
					else
					{  echo '">'; $eintragDa = false; }
				}
				elseif ( is_array($Plan['LehrerVerschiebbar']) )
				{
					$verlegen = true;
					foreach ($Eintraege['Lehrer'] as $lehrer )
					if ( !in_array($lehrer,$Plan['LehrerVerschiebbar']))
					$verlegen = false;
					if ( $verlegen )
					{
						$eintragDa = true;
						echo VERTRETUNG_VERLEGUNG.'&VonStunde='.$Block.'&VonDatum='.
						strtotime('+'.($Wochentag-1).' days',$Plan['Datum']).'&Neu='.
						$Eintraege['Lehrer'][0].'&Stamp='.time().
						'" title="Unterricht verlegen (Raum bleibt gleich)">Verlegen</a>';
						$verlegen = true;
						foreach ($Eintraege['Raum'] as $lehrer )
						if ( !in_array($lehrer,$Plan['RaumVerschiebbar']))
						$verlegen = false;
						if ( $verlegen )
						echo '<br /><a href="'.$_SERVER['PHP_SELF'].'?Art='.
						VERTRETUNG_VERLEGUNGMITRAUM.'&VonStunde='.$Block.'&VonDatum='.
						strtotime('+'.($Wochentag-1).' days',$Plan['Datum']).'&Neu='.
						$Eintraege['Lehrer'][0].'&Stamp='.time().
						'" title="Lehrer und Raum verlegen">L+R Verlegen';
					}
					else
					echo '">';
				}
				else
				echo '">';
			}
			else
			echo '">';
			echo '</a>';
			if ( isset($Eintraege['Bemerkung']) && is_array($Eintraege['Vertretungen']) )
			{
				if ( $eintragDa ) echo "<br />\n";
				echo '<a href="'.$_SERVER['PHP_SELF'].'?Art='.VERTRETUNG_ENTFERNEN.'&ID='.
				implode(',',$Eintraege['Vertretungen']).
				'" title="Vertretung entfernen">Vertr. entfernen</a>';
			}
			if ( isset($Eintraege['Raum'][0]) && $Eintraege['Raum'][0] != '' && $Vertretung == 1
			&& $Plan['Wofuer'] != 'Raum')
			{
				echo '<br /><a href="'.$_SERVER['PHP_SELF'].'?Raumwechsel='.VERTRETUNG_RAUMWECHSEL;
				echo "&Stunde=$Block&Stamp=".time()."\" title=\"Anderen Raum zuweisen\">\n";
				echo "Raumwechsel</a>\n";
			}
		} // Vertretung
	} // foreach
	if ( $anz == 0 && $Vertretung != 0)
	{
		if ( $Plan['Wofuer'] == 'Lehrer' && $Vertretung == 1 &&
		!isset($Eintraege['Bemerkung']))
		{
			echo '<div class="BemerkungsIcon"><a href="VertretungHaendisch.php';
			echo "?Vertretung_id=-1&VonStunde=$Block";
			echo '" target="_blank" title="Vertretung per Hand eingeben">';
			echo '<img src="hand.gif" alt="Hand" /></a>'."\n";
			echo "</div>\n";
		}
		elseif ( $Plan['Wofuer'] == 'Klasse' &&
		$Plan['OrgLehrer'] != '' && $Plan['OrgRaum'] != '')
		{
			// Verlegung möglich?
			$Tag = strtotime('+'.($Wochentag-1).' days',$Plan['Datum']);
			$seintrag = liesStundenplanEintragMitVertretung('Lehrer', $Plan['OrgLehrer'],
			$Tag, $Block);
			if ( ! hatUnterricht($seintrag, 'Lehrer',$Plan['OrgLehrer']) )
			{
				$seintrag = liesStundenplanEintragMitVertretung('Raum', $Plan['OrgRaum'],
				$Tag, $Block);
				if ( ! hatUnterricht($seintrag, 'Raum', $Plan['OrgRaum']) )
				{
					// Raum, Lehrer und Klasse haben frei
					echo '<br /><a href="'.$_SERVER['PHP_SELF'].'?Art='.
					VERTRETUNG_VERLEGUNGOHNEAUSFALL.
					"&VonDatum=$Tag&VonStunde=$Block&Stamp=".time().
					'" title="markierten Unterricht hierher verlegen">hierher<br />' .
					'verlegen</a><br />'."\n";
				}
				else
				{
					// nur Lehrer und Klasse haben frei - Verlegung mit Raumwechsel anbieten
					echo '<br /><a href="'.$_SERVER['PHP_SELF'].'?Raumwechsel='.
					VERTRETUNG_VERLEGUNGOHNEAUSFALLMITRAUMWECHSEL.
					"&VonDatum=$Tag&VonStunde=$Block&Stamp=".time().
					'" title="Raumwechsel durchführen und markierten Unterricht hierher ' .
					'verlegen">Raum wechseln<br />und hierher<br />verlegen</a><br />'."\n";
				}
			}
		}
	}
	return $anz;
} // SchreibePlanEintraege

function SchreibeTermine($db, $Plan, $zelle_beschr_bg, $zelle_inhalt_bg, $rahmen, $rahmen_farbe)
{
	// TODO Druck ist unbehandelt!
	$Druck = false;
	if ( isset($Plan['Termine']) && $Plan['Wofuer'] != 'Klasse' )
	{
		// Skript für CSS-Popup-Fenster
		echo '<script src="http://js.oszimt.de/overlib.js" type="text/javascript"></script>';
		echo '<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>';
		include_once('include/Termine.class.php');
		$Termine = new Termine($db);
		// Ende Popup-Fenster
		echo '<tr>';
		echo "\n\t\t<td class = \"$zelle_beschr_bg $rahmen $rahmen_farbe\" align = \"center\">";
		echo '<span class="smallmessage_kl">Termine</span></td>';
		if ( $Plan['Vertretung'] )
		echo "<td></td><td></td>\n";
		for ($Wochentag = 1; $Wochentag<=5; $Wochentag++ )
		{
			echo "\n\t\t<td class = \"$zelle_inhalt_bg $rahmen $rahmen_farbe\" ";
			echo " align = \"center\">";
			if ( isset($Plan['Termine'][$Wochentag]) )
			foreach ( $Plan['Termine'][$Wochentag] as $Termin )
			{
				echo '<div class="Termin" ';
				if ( ! $Druck )
				{
					echo 'onMouseOver="return overlib('."'";
					echo htmlentities(stripslashes(str_replace("\n","",
					str_replace("\r","",nl2br($Termin['Beschreibung'])))));
					echo $Termine->BetroffeneAnzeigen($Termin['Betroffene']);
					echo '<br /><span class=Termininfo>';
					echo $Termin['Bearbeiter'].' / Stand: ';
					echo $Termin['St'];
					echo '</span>';
					echo"',CAPTION,'".$Termin["Bezeichnung"];
					echo " (".$Termin["Klassifikation"].")');";
					echo '" onMouseOut="return nd();"';
				}
				echo '>';
				$uhrzeit = date('H:i',$Termin['Datum']);
				if ( $uhrzeit != '00:00' )
				echo '<span class="Uhrzeit">'.$uhrzeit.'</span> ';
				echo $Termin['Bezeichnung'];
				echo "</div>\n";
			}
			echo "&nbsp;</td>\n";
		}
		echo "</tr>\n";
	}
} // SchreibeTermine

// ZweiterPlan: Wenn nicht leer, dann wird vom zweiten Plan nur der aktuelle Tag / Stunde
// angezeigt.
// Zusätzliche Felder von ZweiterPlan:
// Tag, Stunde - Enthält betrachteten Tag + Stunde

function schreibePlan($Plan, $db, $Druck = false, $Vertretung = false, $ZweiterPlan=array())
{
	if ( ! is_array($Plan) )
	dieMsg('Fehlerhafter Parameter - Kein Feld übergeben!');
	$Tag = array('Montag','Dienstag','Mittwoch','Donnerstag','Freitag');
	$BZeit = array('8:00-9:30','9:45-11:15','11:45-13:15','13:30-15:00','15:15-16:45','17:00-18:30');
	$Felder = array();
	$Felder[] = 'Fach';
	if ( $Plan['Wofuer'] != 'Klasse' || Count($ZweiterPlan) > 0)
	$Felder[] = 'Klasse';
	if ( $Plan['Wofuer'] != 'Raum' )
	$Felder[] = 'Raum';
	$Felder[] = 'Lehrer';

	//css-Einstellungen
	if($Druck)
	{
		$rahmen_farbe      = 'ra_sw';
		$zelle_beschr_bg   = 'zelle_beschr_bg_dr';
		$zelle_inhalt_bg   = 'zelle_inhalt_bg_dr';
		$zelle_frei_tag_bg = 'zelle_frei_tag_bg_dr';
		$link_table        = 'linktable_dr';
	}
	else
	{
		$rahmen_farbe      = 'ra_bl';
		$zelle_beschr_bg   = 'zelle_beschr_bg';
		$zelle_inhalt_bg   = 'zelle_inhalt_bg';
		$zelle_frei_tag_bg = 'zelle_frei_tag_bg';
		$link_table        = 'linktable';
	}
	if ( Count($ZweiterPlan) == 0 )
	{
		echo "\n\n<p align = \"center\"><br />";
		echo "<span class = \"ueberschrift\">\n";
		switch ( $Plan['Wofuer'] )
		{
			case 'Raum':
				echo 'Belegungsplan von Raum '.$Plan['Anzeige'];
				break;
			case 'Lehrer':
				echo 'Unterrichtseinsatz von '.$Plan['Vorname'].' '.$Plan['Name'];
				/* ***NEU 12.09.05: Aufsichten */
				$Aufsichten = array();
				if(isset($Plan['Datum']))
				$AufsichtAb = getGueltigAb($Plan['Datum'], 'T_Aufsichten');
				else
				$AufsichtAb = getGueltigAb(-1, 'T_Aufsichten');
				$sql = 'SELECT * FROM T_Aufsichten INNER JOIN T_Aufsichtsorte ON F_Ort_id=Ort_id '.
				"WHERE Lehrer='".$Plan['Anzeige']."' AND GueltigAb=$AufsichtAb";
				$query = mysql_query($sql, $db);
				while($row = mysql_fetch_array($query))
				$Aufsichten[$row['VorStunde']][$row['Wochentag']] = $row['Ort'];
				mysql_free_result($query);
				$AnzAufsichten = 0;
				for ( $i = 1; $i < 5; $i++)
				if ( isset($Aufsichten[$i]) ) $AnzAufsichten++;
				// Ende Aufsichten einlesen
				break;
			case 'Klasse':
				echo 'Stundenplan der Klasse '.$Plan['Anzeige'];
				break;
			default:
				echo 'Stundenplan von '.$Plan['Anzeige'];
		}
		echo "</span>\n<span class = \"smallmessage_gr\">&nbsp;&nbsp;(";
		if ( isset($Plan['Woche']) )
		echo $Plan['Woche'].'. KW / ';
		else
		echo 'Turnus: ';
		echo implode(', ',$Plan['Turnusse']);
		echo ")</span>\n";
		if ( $Plan['Wofuer'] == 'Raum' )
		{
			// Raumbezeichnung und Kapazität anzeigen
			// ggf. den Punkt nachtragen wenn nicht vorhanden
			$Raum2 = Raumbezeichnung($Plan['Anzeige']);
			$sql = 'SELECT Raumbezeichnung, Beschreibung, Kapazitaet FROM T_Raeume '.
			"WHERE Raumnummer='".mysql_real_escape_string($Raum2)."'";
			if (!$query = mysql_query($sql, $db)) echo mysql_error();
			if ( !$r = mysql_fetch_array($query) )
			{
				$r['Raumbezeichnung'] = '';
				$r['Kapazitaet'] = '';
				$r['Beschreibung'] = '';
			}
			mysql_free_result($query);
			if ( is_numeric($r['Kapazitaet']) && $r['Raumbezeichnung'] != '' )
			{
				echo '<br /><span class = "p_small smallmessage_gr">'.
				stripslashes($r['Raumbezeichnung']).' ('.$r['Kapazitaet'].
				' Schülerplätze';
				$canz = ComputerAnzahl($Raum2, $db);
				if ( $canz != 0 ) echo " / $canz Computer";
				echo ')';
				if ( $r['Beschreibung'] != '' )
				echo '<br />'.nl2br($r['Beschreibung']);
				echo "</span>\n";
			}
		} // wenn Raum
		echo '</p>';
		$Breite = 95;
	} // wenn nicht ZweiterPlan vorhanden
	else
	{
		// es gibt einen zweiten Plan links
		$Breite = 98;
	}
	echo '<div style="text-align:center">';
	echo "\n<table class=\"Stundenplantabelle\" width = \"$Breite%\" height = \"1%\"";
	echo "  cellpadding = \"0\" cellspacing = \"0\" align=\"center\">";
	if ( Count($ZweiterPlan) == 0 )
	{
		echo "\n\t<tr>";
		echo "\n\t\t<td>&nbsp;</td>";
		if(isset($Plan['ID_letzteWoche']) && $Plan['ID_letzteWoche'] != -1)
		echo "\n\t\t<td align=\"left\"><a href=\"{$_SERVER["PHP_SELF"]}?{$Plan["Wofuer"]}=".
		"{$Plan["Anzeige"]}&ID_Woche={$Plan["ID_letzteWoche"]}&KW=true".
		"\" title=\"Plan anzeigen\"><img src = \"http://img.oszimt.de/nav/pfeili_blau.gif\" ".
		"alt=\"Pfeil links\" title=\"vorherige Woche\"></a></td>";
		else
		echo "\n\t\t<td>&nbsp;</td>";
		echo "\n\t\t<td colspan = \"3\"><span class = \"smallmessage_gr\">";
		echo "<b>Gültig ab: " . date("d.m.Y",$Plan["GueltigAb"]) . "</b></span>";
		//Es gibt eine weitere Version
		if ( $Plan['Wofuer'] == 'Lehrer' && isset($Plan['naechsteGueltigAb']) )
		echo "&nbsp;&nbsp;&nbsp;&nbsp;<a class = \"smalllink\" ".
		"href = \"{$_SERVER["PHP_SELF"]}?{$Plan["Wofuer"]}={$Plan["Anzeige"]}" .
		"&Version={$Plan["naechsteGueltigAb"]}&$linkTurnus\" title=\"neue Version anzeigen\">\n".
		"[Gültig ab: " . date("d.m.Y",$Plan["naechsteGueltigAb"]) . "]</a></td>\n";
		echo '</td>';
		if(isset($Plan['ID_naechsteWoche']) && $Plan['ID_naechsteWoche'] != -1)
		echo "\n\t\t<td align = \"right\"><a href=\"{$_SERVER["PHP_SELF"]}?{$Plan["Wofuer"]}={$Plan["Anzeige"]}&".
		"ID_Woche={$Plan["ID_naechsteWoche"]}&KW=true\">" .
		"<img src = \"http://img.oszimt.de".
		"/nav/pfeire_blau.gif\" alt=\"Pfeil rechts\" title=\"nächste Woche\"></a></td>";
		else
		echo "\n\t\t<td>&nbsp;</td>";
		echo "\n\t</tr>";
		$StartTag = 1;
		$TagAnzahl = 5;
		$Spaltenbreite = 17;
		$Blockspaltenbreite = 15;
	} // ZweiterPlan nicht vorhanden
	else
	{
		$StartTag = 1;
		$TagAnzahl = 5;
		$Spaltenbreite = 13;
		$Blockspaltenbreite = 10;
	}
	echo "\n\t<tr height = \"1%\">";
	// Maximale Stundenanzahl bestimmen - bei Raumauswahl bis 6. Block
	// und Zelle oben links mit Hilfe versehen, wenn Raumauswahl möglich ist
	if ( $Plan['Wofuer'] != 'Raum' || !isset($Plan['Datum']) || $Druck )
	{
		echo "\n\t\t<td width=\"$Blockspaltenbreite%\">&nbsp;</td>";  // Zelle links oben ist leer
		$MaxStunde = 4;  // Mindestens 4 Blöcke anzeigen
		for ( $Wochentag = $StartTag; $Wochentag <= $TagAnzahl; $Wochentag++)
		for ( $Block = $MaxStunde; $Block <= 6; $Block++)
		if ( isset($Plan[$Wochentag][$Block]) && Count($Plan[$Wochentag][$Block])!= 0 )
		$MaxStunde = $Block;
		if ( isset($_REQUEST['MaxStunde']) && $MaxStunde < $_REQUEST['MaxStunde'])
		$MaxStunde = $_REQUEST['MaxStunde'];
	}
	else
	{
		echo "\n\t\t<td bgcolor = \"#F8F8F8\" width=\"$Blockspaltenbreite%\">";
		echo "<a class = \"smalllink\" href=\"resHilfe.html\" target=\"_blank\">Hilfe</a></td>";
		$MaxStunde = 6;
	}
	if ( Count($ZweiterPlan) != 0 )
	{
		// eventuell muss MaxStunde angepasst werden
		for ( $Wochentag = $StartTag; $Wochentag <= $TagAnzahl; $Wochentag++)
		for ( $Block = $MaxStunde; $Block <= 6; $Block++)
		if ( isset($ZweiterPlan[$Wochentag][$Block]) && Count($ZweiterPlan[$Wochentag][$Block])!= 0 )
		$MaxStunde = $Block;
		// Zwei Spalten: Plan+Auswahlspalte zusätzlich
		echo "\n\t\t<td class = \"$zelle_beschr_bg ";
		echo "$rahmen_farbe\" id=\"form_spalte_beschr\" width=\"$Spaltenbreite%\">\n";
		echo "<span class=\"tab_beschr\">{$ZweiterPlan["Anzeige"]}</span><br />\n";
		if ( $ZweiterPlan['Tag'] == 1 )
		$Wert = -3;
		else
		$Wert = $ZweiterPlan['Tag']-2;
		$PWert = $ZweiterPlan['Tag'];
		if ( $ZweiterPlan['Tag'] == 5 )
		$PWert += 2;
		echo '<table width="100%"><tr><td>'."\n";
		echo '<a href="'.$_SERVER['PHP_SELF'].'?Datum='.
		strtotime("+$Wert day",$ZweiterPlan["Datum"]).'" title="Tag zurück">&larr;</a>';
		echo "</td><td>\n";
		echo "<td class=\"smallmessage_kl\">";
		echo date("d.m.Y",strtotime("+".($ZweiterPlan["Tag"]-1)." days",$ZweiterPlan["Datum"]));
		echo "</td><td align=\"right\">\n";
		echo '<a href="'.$_SERVER['PHP_SELF'].'?Datum='.
		strtotime("+$PWert day",$ZweiterPlan["Datum"]).'" title="Tag vor">&rarr;</a>';
		echo "</td></tr></table>\n";
		echo "</td>\n";
		echo "\n\t\t<td width=\"2%\"></td>";
	} // ZweiterPlan vorhanden
	for ( $Wochentag = $StartTag; $Wochentag <= $TagAnzahl; $Wochentag++)
	{
		$rahmen = LayoutRahmen(0, $MaxStunde, $Wochentag, 5);
		echo "\n\t\t<td class = \"$zelle_beschr_bg $rahmen ";
		echo "$rahmen_farbe\" id=\"form_spalte_beschr\" width=\"$Spaltenbreite%\">";
		// Pfeil für vorherige und nächste Woche
		if ( Count($ZweiterPlan) != 0 && $Wochentag == $StartTag )
		{
			if(isset($Plan['ID_letzteWoche']) && $Plan['ID_letzteWoche'] != -1)
			echo "<a href=\"{$_SERVER["PHP_SELF"]}?KlasseWoche={$Plan["ID_letzteWoche"]}".
			"\" title=\"Plan anzeigen\" style=\"float:left;\">" .
			"<img src = \"http://img.oszimt.de/nav/pfeili_blau.gif\" ".
			"alt=\"Pfeil links\" title=\"vorherige Woche\"></a> ";
		}
		if ( Count($ZweiterPlan) != 0 && $Wochentag == $TagAnzahl )
		{
			if(isset($Plan['ID_naechsteWoche']) && $Plan['ID_naechsteWoche'] != -1)
			echo " <a href=\"{$_SERVER["PHP_SELF"]}?KlasseWoche=".
			"{$Plan["ID_naechsteWoche"]}\" style=\"float:right;\">" .
			"<img src = \"http://img.oszimt.de".
			"/nav/pfeire_blau.gif\" alt=\"Pfeil rechts\" title=\"nächste Woche\"></a>";
		}
		echo "<span class = \"tab_beschr\">" . $Tag[$Wochentag-1] . "</span>";
		if(isset($Plan['Datum']))//Wenn Ausgabe nach KW, dann Tagesdatum ausgeben
		{
			echo "<br><span class = \"smallmessage_kl\">";
			$tag = strtotime("+".($Wochentag - 1)." day",$Plan["Datum"]);
			if ( Count($ZweiterPlan) != 0 )
			echo '<a href="'.$_SERVER["PHP_SELF"].'?Datum='.$tag.'">';
			echo date("d.m.y",$tag);
			if ( Count($ZweiterPlan) != 0 ) echo '</a>';
			echo '</span>';
		}
		echo "</td>";
	}
	echo '</tr>';
	$FruehUndFerien = false;
	if ( $Vertretung )
	$Vertretung = 2;
	else
	$Vertretung = 0;
	for ( $Block = 1; $Block <= $MaxStunde; $Block++)
	{
		/* *** NEU 11.09.05: Aufsichten */
		if ( isset($Aufsichten[$Block]) )
		{
			$rahmen = "x1001";
			echo "\n\t<tr>";
			//      echo "\n\t\t<td>&nbsp;</td>"; // Leere Zelle links
			echo "\n\t\t<td class = \"$zelle_beschr_bg $rahmen $rahmen_farbe\" >";
			echo '<span class="smallmessage_kl">Aufsicht</span></td>';
			if ( Count($ZweiterPlan) != 0 )
			{
				// Leere Spalte bei Aufsicht
				echo '<td>';
				echo '</td>';
				echo "<td></td>\n";
			} // ZweiterPlan vorhanden
			for ( $Wochentag = $StartTag; $Wochentag <= $TagAnzahl; $Wochentag++ )
			{
				if ( !isset($Plan['freierTag']) || ! $Plan['freierTag'][$Wochentag] )
				{
					if($Wochentag == 5)
					$rahmen = 'x1101';
					echo "\n\t\t<td class = \"$zelle_inhalt_bg $rahmen $rahmen_farbe\" >";
					if ( isset($Aufsichten[$Block][$Wochentag]) )
					{
						echo '<span class="smallmessage_kl">';
						echo $Aufsichten[$Block][$Wochentag];
						echo '</span>';
					}
					else
					echo '&nbsp;';
					echo '</td>';
				}
				else if($Block == 1)//Wenn Fruehaufsicht (vor erstem Block) und Ferien dann beginnt die Ferienspalte hier!
				{
					$FruehUndFerien = true;
					$rahmenFerien = 'x1011';
					if($Wochentag == 5)
					$rahmenFerien = 'x1111';
					echo "\n\t\t<td class = \"$zelle_frei_tag_bg $rahmenFerien $rahmen_farbe\"";
					echo "rowspan = \"".($MaxStunde+$AnzAufsichten)."\" nowrap >";
					echo "<span class = \"smallmessage_gr\">" . $Plan["Kommentar"][$Wochentag] . "</span></td>";
				}
			}
			echo "\n\t</tr>\n";
		}
		// Ende Aufsichten-Zeile

		echo "\n\t<tr>";

		$rahmen = LayoutRahmen($Block, $MaxStunde, 0, 5);
		echo "\n\t\t<td nowrap class = \"$zelle_beschr_bg $rahmen ";
		echo "$rahmen_farbe\" id=\"form_zeile_beschr\">";
		echo "<span class = \"tab_beschr\">&nbsp;$Block. Block&nbsp;</span><br>";
		echo "<span class = \"smallmessage_kl\">" . $BZeit[$Block-1] . "</span>";
		echo "</td>\n";
		if ( Count($ZweiterPlan) != 0 )
		{
			$rahmen = LayoutRahmen($Block, $MaxStunde, 1, 1);
			// Planeintrag des zweiten Planes
			echo "\n\t\t<td nowrap class = \"";
			if ( $Block != $ZweiterPlan['Stunde'] )
			echo "$zelle_inhalt_bg ";
			else
			echo 'zelle_markiert_bg ';
			echo "$rahmen $rahmen_farbe Zelle\">";
			//echo "id = \"form_inhalt\">"; //"<span class = \"smallmessage_gr\">";
			$anz = SchreibePlanEintraege($ZweiterPlan, $Felder, $ZweiterPlan["Tag"], $Block, 1);
			echo "</td>\n";
			echo '<td>';
			if ( $Block != $ZweiterPlan['Stunde'] && $anz > 0 )
			echo '<a href="'.$_SERVER['PHP_SELF']."?Stunde=$Block\" " .
			"title=\"Diesen Block bearbeiten\">&larr;<br /><img src=\"".
			"http://img.oszimt.de/nav/calendar.gif\" alt=\"Kalender\" " .
			"title=\"Diesen Block bearbeiten\"/></a>\n";
			echo "</td>\n";
		}
		for ( $Wochentag = $StartTag; $Wochentag <= $TagAnzahl; $Wochentag++)
		{
			$rahmen = LayoutRahmen($Block, $MaxStunde, $Wochentag, 5);
			if ( isset($Plan['Datum']) )
			$Tag = strtotime('+'.($Wochentag-1).' day',$Plan['Datum']);
			//Ausgabe der Ferienspalte
			if(isset($Plan['freierTag']) && $Plan['freierTag'][$Wochentag]
			&& $Block == 1 && ! $FruehUndFerien)
			{
				$rahmenFerien = 'x1011';
				if($Wochentag == 5)
				$rahmenFerien = 'x1111';
				echo "\n\t\t<td class = \"$zelle_frei_tag_bg $rahmenFerien $rahmen_farbe\"";
				echo "rowspan = \"".($MaxStunde+$AnzAufsichten)."\" nowrap>";
				echo "<span class = \"smallmessage_gr\">" . $Plan["Kommentar"][$Wochentag];
				echo "</span></td>";
			}
			elseif ( !isset($Plan['freierTag'][$Wochentag]) ||
			!$Plan['freierTag'][$Wochentag] )
			{
				//Ausgabe der Unterrichtsbloecke
				echo "\n\t\t<td nowrap class = \"";
				if ( $Block != $ZweiterPlan["Stunde"] || $Wochentag != $ZweiterPlan["Tag"] )
				echo "$zelle_inhalt_bg ";
				else
				echo 'zelle_markiert_bg ';
				echo "$rahmen $rahmen_farbe Zelle\"> ";
				$anz = SchreibePlanEintraege($Plan, $Felder, $Wochentag, $Block, $Vertretung);
				// Raumreservierungen. Wenn keine Einträge vorhanden, ist der Raum frei
				if ( $anz == 0 )
				if ( $Plan['Wofuer'] == 'Raum' && isset($Plan['Datum']) && ! $Druck )
				{
					//Berechnung des Unix-Timestamp
					$dieserTag = strtotime('+'.($Wochentag-1).' day',$Plan['Datum']);
					//Anzeige nur wenn Termin nicht in der Vergangenheit
					if($dieserTag >= mktime(0,0,0,date('m,d,Y',time())) &&
					! istVerhindert($dieserTag,$Plan['Anzeige'],$Block,'Raum') &&
					! istGesperrt($dieserTag, $Plan['Anzeige'], $Block))
					{
						echo "<a href=\"/StuPla/RaumReservierung.php?Raum=".$Plan['Anzeige'].
						"&Tag=$dieserTag&Stunde=$Block\" target=\"_blank\" title=\"" .
						"Raum reservieren\">".
						"<img src=\"/StuPla/R1.gif\" ></a>\n";
					}
				}
				else//(Keine Reservierungsinfos anzeigen)
				echo '&nbsp;';
			} // wenn nicht freierTag
			echo "\n\t\t</td>";
		} // for Wochentag
		echo "\n\t</tr>";
	} // for Block
	// Termine anzeigen
	$Plan['Vertretung'] = Count($ZweiterPlan) > 0;
	SchreibeTermine($db, $Plan, $zelle_beschr_bg, $zelle_inhalt_bg, $rahmen, $rahmen_farbe);
	// Versionsangabe
	echo "\n\t<tr>";
	if ( Count($ZweiterPlan) != 0 )
	{
		echo '<td><a href="'.$_SERVER['PHP_SELF'].'?MaxStunde='.($MaxStunde+1).
		'" title="Den '.($MaxStunde+1).'. Block anzeigen">+1 Block</a></td>';
	}
	echo "\n\t\t<td align = \"right\" valign = \"top\" colspan = \"";
	if ( Count($ZweiterPlan) == 0 )
	echo '6';
	else
	echo '7';
	echo '">';
	echo '<span class ="smallmessage_kl">Version: ' .
	date('d.m.Y',$Plan['Version']) . '</span></td>';
	//  echo '\n\t\t<td>&nbsp;</td>';
	echo "\n\t</tr>";
	echo "\n</table>\n";
	echo '</div>';
}

function schreibeRaumWahlPlan($Plan, $db)
{
	global $Wochentagnamen;
	if ( ! is_array($Plan) )
	dieMsg('Fehlerhafter Parameter - Kein Feld übergeben!');
	$BZeit = array('8:00-9:30','9:45-11:15','11:45-13:15','13:30-15:00','15:15-16:45','17:00-18:30');
	$Felder = array();
	$Felder[] = 'Fach';
	$Felder[] = 'Klasse';
	$Felder[] = 'Lehrer';
	$Felder[] = 'Raum';
	$rahmen_farbe      = 'ra_bl';
	$zelle_beschr_bg   = 'zelle_beschr_bg';
	$zelle_inhalt_bg   = 'zelle_inhalt_bg';
	$zelle_frei_tag_bg = 'zelle_frei_tag_bg';
	$link_table        = 'linktable';
	echo '<div style="text-align:center">';
	include_once('include/raeume.inc.php');
	if ( $Plan['Wofuer'] == 'Raum' )
	{
		// Raumwechsel per Raum
		$Raum2 = Raumbezeichnung($Plan['Anzeige']);
	}
	else
	{
		// Lehrer oder Klasse - Raumwechsel. Man nehme den ersten Raum ...
		if ( Count($Plan[$Plan['Tag']][$Plan['Stunde']]) > 0 )
		{
			$Turnus = reset($Plan[$Plan['Tag']][$Plan['Stunde']]);
			$Raum2 = reset($Turnus['Raum']);
		}
		else
		{
			$Raum2 = '';
			$Turnus = '';
		}
	}
	$sql = 'SELECT Raumbezeichnung, Beschreibung, Kapazitaet FROM T_Raeume '.
	"WHERE Raumnummer='".mysql_real_escape_string($Raum2)."'";
	if (!$query = mysql_query($sql, $db)) echo mysql_error();
	if ( !$r = mysql_fetch_array($query) )
	{
		$r['Raumbezeichnung'] = '';
		$r['Kapazitaet'] = '';
		$r['Beschreibung'] = '';
	}
	mysql_free_result($query);
	echo "<h1>Ausweichräume für {$Plan["Wofuer"]} {$Plan["Anzeige"]}</h1>\n";
	if ( is_numeric($r['Kapazitaet']) && $r['Raumbezeichnung'] != '' )
	{
		echo '<span class = "p_small smallmessage_gr">'.stripslashes($r["Raumbezeichnung"])." (".$r["Kapazitaet"]." Schülerplätze";
		$canz = ComputerAnzahl($Raum2, $db);
		if ( $canz != 0 ) echo " / $canz Computer";
		echo ')';
		if ( $r['Beschreibung'] != '' )
		echo '<br />'.nl2br($r['Beschreibung']);
		echo "</span>\n";
	}

	echo "\n<table class=\"Stundenplantabelle\" width = \"95%\" height = \"1%\"";
	echo "  cellpadding = \"0\" cellspacing = \"0\" align=\"center\">";
	$Spaltenbreite = 13;
	$Blockspaltenbreite = 10;
	echo "\n\t<tr height = \"1%\">";
	//  echo "\n\t\t<td>&nbsp;</td>"; // Leere Spalte
	// Maximale Stundenanzahl bestimmen - bei Raumauswahl bis 6. Block
	// und Zelle oben links mit Hilfe versehen, wenn Raumauswahl möglich ist
	echo "\n\t\t<td width=\"$Blockspaltenbreite%\">&nbsp;</td>";  // Zelle links oben ist leer
	$MaxStunde = 6;
	// Zwei Spalten: Plan+Auswahlspalte zusätzlich
	echo "\n\t\t<td class = \"$zelle_beschr_bg $rahmen ";
	echo "$rahmen_farbe\" id=\"form_spalte_beschr\" width=\"$Spaltenbreite%\">\n";
	echo "<span class=\"tab_beschr\">{$Plan["Anzeige"]}</span><br />\n";
	if ( $Plan['Tag'] == 1 )
	$Wert = -3;
	else
	$Wert = $Plan['Tag']-2;
	$PWert = $Plan['Tag'];
	if ( $Plan['Tag'] == 5 )
	$PWert += 2;
	$derTag = strtotime('+'.($Plan['Tag']-1).' days', $Plan['Datum']);
	echo '<table width="100%"><tr><td>'."\n";
	echo '<a href="'.$_SERVER["PHP_SELF"].'?Datum='.
	strtotime("+$Wert day",$Plan["Datum"]).'" title="Tag zurück">&larr;</a>';
	echo "</td><td>\n";
	echo "<td class=\"smallmessage_kl\">";
	echo $Wochentagnamen[date('w',$derTag)].'<br />'.date('d.m.Y',$derTag);
	echo "</td><td align=\"right\">\n";
	echo '<a href="'.$_SERVER['PHP_SELF'].'?Datum='.
	strtotime("+$PWert day",$Plan['Datum']).'" title="Tag vor">&rarr;</a>';
	echo "</td></tr></table>\n";
	echo "</td>\n";
	echo "\n\t\t<td width=\"5%\"></td>";
	echo "<td rowspan=\"".($MaxStunde+1)."\" valign=\"top\">\n";
	fuegeRaumauswahlEin($derTag, $Plan['Stunde'], $db);
	echo '</td>';
	echo "</tr>\n";
	for ( $Block = 1; $Block <= $MaxStunde; $Block++)
	{
		echo "\n\t<tr>";
		$rahmen = LayoutRahmen($Block, $MaxStunde, 0, 5);
		echo "\n\t\t<td nowrap class = \"$zelle_beschr_bg $rahmen ";
		echo "$rahmen_farbe\" id=\"form_zeile_beschr\">";
		echo "<span class = \"tab_beschr\">&nbsp;$Block. Block&nbsp;</span><br>";
		echo "<span class = \"smallmessage_kl\">" . $BZeit[$Block-1] . "</span>";
		echo "</td>\n";
		$rahmen = LayoutRahmen($Block, $MaxStunde, 1, 1);
		// Planeintrag des zweiten Planes
		echo "\n\t\t<td nowrap class = \"";
		if ( $Block != $Plan['Stunde'] )
		echo "$zelle_inhalt_bg ";
		else
		echo 'zelle_markiert_bg ';
		echo "$rahmen $rahmen_farbe Zelle\"> ";
		$anz = SchreibePlanEintraege($Plan, $Felder, $Plan["Tag"], $Block, 1);
		echo "</td>\n";
		echo '<td>';
		if ( $Block != $Plan['Stunde'] && $anz > 0 )
		echo '<a href="'.$_SERVER['PHP_SELF']."?Stunde=$Block\" " .
		"title=\"Diesen Block bearbeiten\">&larr;<br /><img src=\"".
		"http://img.oszimt.de/nav/calendar.gif\" alt=\"Kalender\" " .
		"title=\"Diesen Block bearbeiten\"/></a>\n";
		echo "</td>\n";
		echo "\n\t</tr>";
	} // for Block
	// Versionsangabe
	echo "\n\t<tr>";
	echo "\n\t\t<td align = \"right\" valign = \"top\" colspan = \"4\">";
	echo "<span class = \"smallmessage_kl\">Version: " .
	date("d.m.Y",$Plan['Version']) . '</span></td>';
	echo "\n\t</tr>";
	echo "\n</table>\n";
	echo '</div>';
}

/**
 *
 * gibt zurück ob eine Reservierung für einen Raum vorliegt.
 * @return true, wenn eine Reservierung vorhanden ist
 * @return false, wenn keine Reservierung vorhanden ist
 */
function isReservierungVorhanden($Anzeige, $Datum, $Stunde)
{
	$sql = 'SELECT Raum FROM T_Vertretungen WHERE Raum_Neu="'.
	mysql_real_escape_string($Anzeige).'" '.
	"AND Datum=$Datum AND Stunde=$Stunde AND iKey <>'' AND iKey IS NOT NULL";
	$query = mysql_query($sql);
	$vorhanden = false;
	while ($vertretung = mysql_fetch_array($query) )
	{
		$vorhanden = true;
		// TODO Prüfen ob Rückübertragen möglich
	}
	mysql_free_result($query);
	return $vorhanden;
}

function fuegeRaumauswahlEin($Datum, $Stunde, $db)
{
	// freie Räume suchen
	$Version = getAktuelleVersion($Datum);
	$ID_Woche = getID_Woche($Datum);
	$Turnusse = array();
	getTurnusliste($ID_Woche, $Turnusse);
	$Turnusse[] = 'jede Woche';
	if ( isset($_REQUEST['Raumwechsel']) && is_numeric($_REQUEST['Raumwechsel']))
	$Raumwechselart = $_REQUEST['Raumwechsel'];
	else
	$Raumwechselart = VERTRETUNG_RAUMWECHSEL;
	// 1. Schritt: Belegte Räume finden
	$sql = 'SELECT DISTINCT Raum FROM T_StuPla';
	$sql .= " WHERE Stunde = $Stunde";
	$sql .= " AND Wochentag = ".date("w", $Datum);
	$sql .= " AND Version = $Version";
	$sql .= " AND Turnus IN ('".implode("','",$Turnusse)."') ORDER BY Raum;";
	$rs = mysql_query($sql, $db);
	$BRaum = array();
	while($row = mysql_fetch_row($rs))
	$BRaum[] = $row[0];
	mysql_free_result($rs);

	// 2. Schritt: durch Vertretung belegte Räume finden
	$sql = "SELECT Raum_Neu FROM T_Vertretungen WHERE Datum=$Datum AND " .
	"Stunde=$Stunde AND Art NOT IN ".VERTRETUNG_RAUMRESERVIERUNGEN;
	$rs = mysql_query($sql, $db);
	while($row = mysql_fetch_row($rs))
	{
		if ( $row[0] != '' )
		{
			$BRaum[] = $row[0];
		}
	}
	mysql_free_result($rs);

	// 3. Schritt: durch Vertretung freigewordene Räume finden
	for ( $i = 1; $i < 7; $i++)
	$freieRaeume[$i] = holeBefreite('Raum', $Datum, $i);
	//Freie Raeume laut Stundenplan ermitteln
	$BRaum = implode("','",$BRaum);
	$sql = 'SELECT DISTINCT Raum FROM T_StuPla';
	$sql .= " WHERE (Raum NOT IN ('$BRaum') OR Raum IN ('".
	implode("','",$freieRaeume[$Stunde])."')) ";
	$sql .= "AND Version = $Version UNION ";
	// Schritt 4: Nicht im Stundenplan befindliche Räume anzeigen
	$sql .= 'SELECT Raumnummer AS Raum FROM T_Raeume WHERE Reservierbar AND ' .
	'Raumnummer NOT IN (SELECT Raum FROM T_StuPla) AND ' .
	'REPLACE(Raumnummer,".","") NOT IN (SELECT Raum FROM T_StuPla)';
	$sql .= 'ORDER BY Raum;';
	//echo $sql;
	$rs = mysql_query($sql, $db);
	echo 'Klicken Sie auf den Raumnamen um eine Verlegung einzurichten.';
	echo "<table class=\"Liste\" style=\"margin:0;width:100%;\">";
	echo '<tr><th>Raum</th>';
	for ($i = 1; $i < 7; $i++)
	echo "<th>$i</th>\n";
	echo "<th>Beschreibung</th></tr>\n";
	include_once('include/raeume.inc.php');
	while($row = mysql_fetch_row($rs))//Fuer den Fall das Mehrfacheintraege vorhanden sind
	{
		//echo $row[0].',';
		if ( ! istVerhindert($Datum,$row[0],$Stunde,'Raum'))
		{
			echo "<tr>\n";
			echo "<td><a href=\"{$_SERVER["PHP_SELF"]}?Art=".$Raumwechselart.
			"&Neu={$row[0]}&Stamp=".time()."\" ";
			// Zeige Raumkapazität an
			$Raum2 = Raumbezeichnung($row[0]);
			$sql = 'SELECT Raumbezeichnung, Beschreibung, Kapazitaet FROM T_Raeume '.
			"WHERE Raumnummer='".mysql_real_escape_string($Raum2)."'";
			if (!$query = mysql_query($sql)) echo mysql_error();
			if ( !$r = mysql_fetch_array($query) )
			{
				$r['Raumbezeichnung'] = '';
				$r['Kapazitaet'] = '';
				$r['Beschreibung'] = '';
			}
			mysql_free_result($query);
			if ( is_numeric($r['Kapazitaet']) && $r['Raumbezeichnung'] != '' )
			{
				echo 'title="'.stripslashes($r["Raumbezeichnung"]).' ('.
				$r['Kapazitaet'].' Schülerplätze';
				$canz = ComputerAnzahl($Raum2);
				if ( $canz != 0 ) echo " / $canz Computer";
				echo ')"';
			}
			echo ">{$row[0]}</a></td>\n";
			// Raumbezeichnung und Kapazität anzeigen
			$sql ='SELECT Klasse, Lehrer, Fach, Stunde FROM T_StuPla '.
			"WHERE Version=$Version AND Raum='{$row[0]}' AND Wochentag=".
			date('w', $Datum)." AND Turnus IN ('".implode("','",$Turnusse).
			"') ORDER BY Stunde";
			$lquery = mysql_query($sql,$db);
			$stunden = array();
			while ( $info = mysql_fetch_array($lquery))
			{
				$stunden[$info['Stunde']] = $info;
			}
			mysql_free_result($lquery);
			for ( $i = 1; $i < 7; $i++)
			{
				// 6 Blöcke anzeigen
				// ggf. Vertretungen ergänzen
				$ResImg = '';
				echo '<td ';
				if ( in_array($row[0],$freieRaeume[$i]) )
				{
					// Ausfall!
					echo 'class="Liste_Ausfall"';
				}
				elseif ( isVertretungVorhanden('Raum', $row[0], $Datum, $i) )
				{
					// Sonderfall bei Raumreservierung: Wenn möglich, Aufhebungssymbol
					// anzeigen
					if ( isReservierungVorhanden($row[0], $Datum, $i))
					{
						if ( $i == $Stunde )
						{
							// TODO Prüfen, ob der alte Raum noch frei ist
							/*
							 if ( $eintrag['Raum'] != '' && $eintrag['Raum'] != $eintrag['Raum_Neu'])
							 {
        // vor einer Wiederbelegung prüfen ob der Raum noch frei ist!
        // im Stundenplan steht natürlich der Raum als belegt
        // prüfe, ob eine Raumreservierung vorliegt
        $sql = 'SELECT * FROM T_Vertretungen WHERE Raum_Neu="'.
        $eintrag['Raum'].
        '" AND Datum='.$eintrag['Datum'].' AND Stunde='.$eintrag['Stunde'];
        $query2 = mysql_query($sql);
        if ( $reservierung = mysql_fetch_array($query2))
        $freigabe = 2;
        mysql_free_result($query2);
        }
        */
							$ResImg = '<a href=""><img src="NoReservierung.png" ' .
							'title="Reservierung aufheben und diesen Raum als ' .
							'neuen Raum einsetzen"/></a>';
						}
						else
						{
							$ResImg = '<acronym lang="de" title="Hier liegt eine ' .
							'Raumreservierung vor, die aufgehoben werden könnte">R</acronym>';
						}
					}
					echo 'class="Liste_VertretungVorhanden"';
				}
				elseif ( isset($stunden[$i]) )
				echo 'class="Liste_Unterricht"';
				elseif ( $i == $Stunde )
				echo 'class="Liste_AktiveStunde"';
				if ( isset($stunden[$i]) )
				echo ' title="'.$stunden[$i]['Lehrer'].': '.$stunden[$i]['Fach'].' bei '.
				$stunden[$i]['Klasse'].'"';
				echo '>&nbsp;'.$ResImg;
				echo '</td>';
			}
			echo '<td>';
			echo '<a href="../StuPla/PlanAnzeigen.php?Raum='.$row[0];
			echo "&ID_Woche=$ID_Woche";
			echo '" class="smalllink" target="_blank" title="Plan anzeigen">' .
			'<img src="Raster.png" alt="Raster"/></a>&nbsp;';
			$Raum = Raumbezeichnung($row[0]);
			$sql = "SELECT Raumbezeichnung, Kapazitaet FROM T_Raeume WHERE Raumnummer='".$Raum."'";
			if (!$query = mysql_query($sql, $db)) echo mysql_error();
			if ( !$r = mysql_fetch_array($query) )
			{
				$r['Raumbezeichnung'] = '';
				$r['Kapazitaet'] = '';
			}
			mysql_free_result($query);
			if ( is_numeric($r['Kapazitaet']) && $r['Raumbezeichnung'] != '' )
			{
				echo stripslashes($r['Raumbezeichnung']).' ('.$r['Kapazitaet'].' Schülerplätze';
				$canz = ComputerAnzahl($Raum, $db);
				if ( $canz != 0 ) echo " / $canz Computer";
				echo ")\n";
			}
			echo '<a href="../StuPla/PlanAnzeigen.php?Raum='.$row[0];
			echo "&ID_Woche=$ID_Woche";
			echo '" class="smalllink" target="_blank" title="Plan anzeigen">&rarr;&nbsp;Plan</a>';
			echo "</td>\n";
			echo '</tr>';
		} // wenn nicht verhindert
	} // while
	mysql_free_result($rs);
	echo "</table>\n";
	// Ende freie Räume suchen
}

?>
