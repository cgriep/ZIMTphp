<?php
/**
 * Vertretungsplan.php
 * Zeigt den Vertretungsplan für einen oder mehrere Klassen, Lehrer und Räume an
 * Wenn keine Parameter angegeben sind, wird der Lehrervertretungsplan des
 * eingeloggten Kollegen angezeigt.
 *
 * Parameter:
 * Mail - wenn gesetzt, werden die Pläne an die Betroffenen gemailt.
 * PDF - wenn gesetzt, werden die Pläne im PDF-Format ausgegeben.
 *
 * Letzte Änderung: 25.02.06 C. Griep
 * 10.03.06 C. Griep - an neuen Server angepasst
 * 13.03.06 PDF nur für veränderte Datensätze
 *
 * Copyright 2007 Christoph Griep
 */
DEFINE('USE_KALENDER', 1);

DEFINE('PDF_KEIN', 0);
DEFINE('PDF_MAIL', 1);
DEFINE('PDF_PDFALLE', 2);
DEFINE('PDF_PDFVERAENDERT', 3);

$Seitenzahl = 0;

function findeKlassenlehrer($Klasse)
{
	$sql = 'SELECT COUNT(*), Tutor FROM T_Schueler '.'WHERE Tutor <> "" AND Klasse="'.$Klasse.'" GROUP BY Tutor';
	$rs = @ mysql_query($sql);
	$Treffer = 0;
	while ($row = @ mysql_fetch_row($rs)) //Wegen Fehleintraegen Lehrer mit den meisten Einträgen
	if ($row[0] > $Treffer)
	{
		$Treffer = $row[0];
		$KLehrer = $row[1];
	}
	if (mysql_num_rows($rs) > 5)
	{
		// in der OG gibt es Tutoren, dann keinen Klassenlehrer angeben
		$KLehrer = '';
	}
	@ mysql_free_result($rs);
	if ($KLehrer != '')
	{
		$Lehrer = new Lehrer($KLehrer, LEHRERID_KUERZEL);
		$KLehrer = $Lehrer->Name;
	}
	return $KLehrer;
}

function VerschickeMail($Lehrer, $KW, $p)
{
	global $db;
	global $LEHRER_ANREDE;
	require_once ('phpmail.php');
	// $Plan ist ein PDF-Dokument
	// Dieses muss ausgelesen, beendet und für weitere Pläne neu gestartet werden
	PDF_close($p);
	$Plan = PDF_get_buffer($p);
	$mail = new mime_mail();
	$derLehrer = new Lehrer($Lehrer, LEHRERID_KUERZEL);
	$User = $derLehrer->Username;
	$mail->to = $User.'@oszimt.de';
	//$mail->to = 'griep@oszimt.de';
	$mail->from = $_SERVER['REMOTE_USER'].'@oszimt.de';
	$mail->headers = 'Errors-To: '.$_SERVER['REMOTE_USER'].'@oszimt.de'."\n".
 'Cc: '.$_SERVER['REMOTE_USER'].'@oszimt.de';
	$mail->subject = '[OSZIMT Vertretung] '.$KW.'. Kalenderwoche';
	$body = $derLehrer->Anrede($LEHRER_ANREDE,false);
 $body .= ",\n".'anbei finden Sie aktuelle Änderungen an ' .
 'Ihrem Stundenplan für die '.$KW.'. Kalenderwoche (Stand: '.
 date('d.m.Y H:i').").\n";
	$body .= "\n"."Mit freundlichen Grüßen\n"."Ihre Abteilungsleitung am OSZ IMT" .
	"\n\n"."bearbeitet durch: ".$_SERVER['REMOTE_USER']."\n".
 "(automatisch generiert am ".date('d.m.Y H:i').
 '. Aus technischen Gründen ist es möglich, dass Sie mehrere ' .
 'E-Mails an einem Tag erhalten. Beachten Sie in diesem '.
 'Fall nur den aktuellsten Stand der Planänderung. Wir bitten um Verständnis.)';
	$mail->add_attachment($Plan, "Planaenderung$Lehrer-$KW.KW.pdf", 'application/pdf');
	$mail->body = $body;
	if (!$mail->send())
	echo '<div class="Fehler">Fehler beim Mailen: Lehrer '.$Lehrer.'</div>';
	else
	echo '<div class="Hinweis">Mail an '.$mail->to.' gesendet.</div>';
}

$PDF = PDF_KEIN;
if (isset ($_REQUEST['PDF']) && is_numeric($_REQUEST['PDF']))
{
	$PDF = $_REQUEST['PDF'];
	include ('include/pdf.inc.php');
	$p = PDF_new();
	PDF_open_file($p, ''); // TODO: ALT: ohne ''
	PDF_set_info($p, 'Creator', 'OSZIMT');
	PDF_set_info($p, 'Author', 'Christoph Griep');
	$Fonts = LadeFonts($p);
	$Wochentagnamen[0] = 'Sonntag';
	$Wochentagnamen[1] = 'Montag';
	$Wochentagnamen[2] = 'Dienstag';
	$Wochentagnamen[3] = 'Mittwoch';
	$Wochentagnamen[4] = 'Donnerstag';
	$Wochentagnamen[5] = 'Freitag';
	$Wochentagnamen[6] = 'Samstag';
}

if ($PDF <= PDF_MAIL)
{
	$Ueberschrift = 'Planänderung';
	$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
	include ('include/header.inc.php');
	echo '<tr><td>';
} else
{
	include ('include/config.php');
}
/* ----
 Vertretungen anzeigen
 Wochenübersicht für die gewählte Art
 */

include ('include/helper.inc.php');
include ('include/stupla.inc.php');
include ('include/Lehrer.class.php');
include ('include/Vertretungen.inc.php');

function neueTabelle()
{
	$Tabelle = array ();
	$Tabelle[0][0]['value'] = 'Block';
	$Tabelle[0][0]['Breite'] = 20;
	$Tabelle[0][0]['Fuellung'] = 0.8;
	$Tabelle[0][1]['value'] = 'Klasse';
	$Tabelle[0][1]['Breite'] = 35;
	$Tabelle[0][1]['Fuellung'] = 0.8;
	$Tabelle[0][2]['value'] = 'Fach';
	$Tabelle[0][2]['Breite'] = 50;
	$Tabelle[0][2]['Fuellung'] = 0.8;
	$Tabelle[0][3]['value'] = 'Lehrer';
	$Tabelle[0][3]['Breite'] = 65;
	$Tabelle[0][3]['Fuellung'] = 0.8;
	$Tabelle[0][4]['value'] = 'Lehrer neu';
	$Tabelle[0][4]['Breite'] = 65;
	$Tabelle[0][4]['Fuellung'] = 0.8;
	$Tabelle[0][5]['value'] = 'Raum';
	$Tabelle[0][5]['Breite'] = 55;
	$Tabelle[0][5]['Fuellung'] = 0.8;
	$Tabelle[0][6]['value'] = 'Bemerkung';
	$Tabelle[0][6]['Breite'] = 200;
	$Tabelle[0][6]['Fuellung'] = 0.8;
	return $Tabelle;
}

function fuelleTag($Datum, $Montag, $Tag, $Block)
{
	global $Wochentagnamen;
	global $PDF;
	global $p;
	global $Tabelle;
	global $Fonts;
	while ($Tag != date('w', $Datum))
	{
		// auf 6 Blöcke auffüllen
		if ($Block < 6 && $Block != 0)
		{
			for ($i = $Block; $i < 7; $i ++)
			// Normalen Stundenplan anzeigen?
			if ($PDF != PDF_KEIN)
			{
				$Tabelle[Count($Tabelle)][0]['value'] = $i;
				$Tabelle[Count($Tabelle) - 1][1]['value'] = '';
				$Tabelle[Count($Tabelle) - 1][2]['value'] = '';
				$Tabelle[Count($Tabelle) - 1][3]['value'] = '';
				$Tabelle[Count($Tabelle) - 1][4]['value'] = '';
				$Tabelle[Count($Tabelle) - 1][5]['value'] = '';
				$Tabelle[Count($Tabelle) - 1][6]['value'] = '';
				$Tabelle[Count($Tabelle) - 1][6]['Breite'] = 200;
			} else
			echo "<tr><td>$i</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;"."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
		}
		$Block = 1;
		$TagDatum = strtotime('+'.$Tag.' days', $Montag);
		if ($Tag != 5)
		if ($PDF == PDF_KEIN)
		echo '<tr><td class="Zwischenueberschrift" colspan="7">'.$Wochentagnamen[date('w', $TagDatum)].', '.date('d.m.Y', $TagDatum)."</td></tr>\n";
		else
		{
			if (Count($Tabelle) != 0)
			pdf_Tabelle($p, $Tabelle);
			pdf_continue_text($p, '');
			pdf_continue_text($p, '');
			pdf_setfont($p, $Fonts['Arial']['B'], 12.0);
			pdf_continue_text($p, '');
			pdf_show_xy($p, $Wochentagnamen[date('w', $TagDatum)].', '.date('d.m.Y', $TagDatum), 50, pdf_get_value($p, 'texty', 0));
			pdf_setfont($p, $Fonts['Arial']['N'], 8.0);
			pdf_continue_text($p, '');
			$Tabelle = neueTabelle();
		}
		$Tag ++;
	}
	return $Tag;
}

$Felder = array ('Klasse', 'Lehrer', 'Raum');

if (!isset ($_REQUEST['Woche']) && isset ($_REQUEST['Datum']))
{
	$Datum = explode('.', $_REQUEST['Datum']);
	if (!checkdate($Datum[1], $Datum[0], $Datum[2]))
	{
		$Datum = time();
	}
	else
	{
		$Datum = mktime(0, 0, 0, $Datum[1], $Datum[0], $Datum[2]);
	}

	if ( date('w',$Datum)==6 || date('w',$Datum)==0)
	{
		$Datum = strtotime('+2 days',$Datum);
	}
	$_REQUEST['Woche'] = getID_Woche($Datum);
}
unset ($Art);
foreach ($Felder as $dieArt)
if (isset ($_REQUEST[$dieArt]))
{
	$Art = $dieArt;
}

if (!isset ($Art) && isset ($_REQUEST['Art']))
{
	$Art = $_REQUEST['Art'];
	$_REQUEST[$Art] = $_REQUEST['Wofuer'];
}

// Standard: Wenn nichts ausgewählt dann den eingeloggten Benutzer anzeigen
if (!isset ($Art))
{
	$Art = 'Lehrer';
	$User = new Lehrer($_SERVER['REMOTE_USER'], LEHRERID_EMAIL);
	$_REQUEST['Lehrer'] = $User->Kuerzel;
	$_REQUEST['Datum'] = date('d.m.Y');
	if ( date('w')==6 || date('w')==0)
	{
		$_REQUEST['Woche'] = getID_Woche(strtotime('+2 days'));
	}
	else
	{
		$_REQUEST['Woche'] = getID_Woche();
	}
}
if ( isset($_REQUEST['Woche']) && $_REQUEST['Woche'] < 0 )
{
	unset($_REQUEST['Woche']);
}
if (!isset ($_REQUEST['Print']) && $PDF == PDF_KEIN)
{
	foreach ($Felder as $dieArt)
	{
		echo '<form action="'.$_SERVER["PHP_SELF"].'" name="Eingabe'.$dieArt.'" method="post" class="Verhinderung">';
		echo "Vertretungplan von ".$dieArt.' <select name="'.$dieArt.'" size="1">';
		if ($dieArt == "Lehrer")
		{
			$FeldArt = "Name, Vorname, Lehrer";
		}
		else
		{
			$FeldArt = $dieArt;
		}
		$query = mysql_query("SELECT DISTINCT $FeldArt FROM T_StuPla ORDER BY $FeldArt");
		while ($inhalt = mysql_fetch_array($query))
		{
			echo '<option ';
			if ($dieArt == "Lehrer")
			{
				echo 'value="'.$inhalt[2].'" ';
				if ($dieArt == $Art && $_REQUEST[$Art] == $inhalt[2])
				{
					echo 'selected="selected"';
				}
				echo '>';
				echo trim($inhalt[0].", ".$inhalt[1]);
			} else
			{
				if ($dieArt == $Art && $_REQUEST[$Art] == $inhalt[0])
				{
					echo 'selected="selected"';
				}
				echo ">".$inhalt[0];
			}
			echo "</option>\n";
		} // while
		mysql_free_result($query);
		echo "</select>\n";
		echo 'für Datum <input type="Text" name="Datum" value="';
		if (isset ($_REQUEST['Datum']))
		{
			echo $_REQUEST['Datum'];
		}
		else
		{
			echo date('d.m.Y');
		}
		echo '" size="10" maxlength="10"';
		echo ' onClick="popUpCalendar(this,Eingabe'.$dieArt."['Datum'],'dd.mm.yyyy')".'" ';
		echo "onBlur=\"autoCorrectDate('Eingabe$dieArt','Datum' , false )\"";
		echo '/>
				 <input type="Submit" value="Anzeigen"/>
				 </form>';
	} // foreach
} // if
//
if (isset ($_REQUEST['Verhinderung_id']) && is_numeric($_REQUEST['Verhinderung_id']))
{
	$query = mysql_query('SELECT * FROM T_Verhinderungen WHERE Verhinderung_id='.
	$_REQUEST['Verhinderung_id']);
	$Verhinderung = mysql_fetch_array($query);
	mysql_free_result($query);

	$Wochen = array ();
	$Plaene = array ();
	$sql = 'SELECT * FROM T_Vertretungen WHERE F_Verhinderung_id='.$_REQUEST['Verhinderung_id'];
	if ($PDF == PDF_PDFVERAENDERT) // nur veränderte anzeigen
	{
		// Betroffene feststellen
		$sql2 = 'SELECT Lehrer, Lehrer_Neu FROM T_Vertretungen WHERE F_Verhinderung_id='.
		$_REQUEST['Verhinderung_id'].' AND Bearbeiter="'.$_SERVER['REMOTE_USER'].
		'" AND Stand >"'.date('Y-m-d H:i', strtotime('-1hour', time())).'"';
		$query = mysql_query($sql2);
		$Betroffene = array ();
		while ($lehrer = mysql_fetch_array($query))
		{
			if (!in_array($lehrer['Lehrer'], $Betroffene))
			{
				$Betroffene[] = $lehrer['Lehrer'];
			}
			if (!in_array($lehrer['Lehrer_Neu'], $Betroffene))
			{
				$Betroffene[] = $lehrer['Lehrer_Neu'];
			}
		}
		mysql_free_result($query);
		$Betroffene = '"'.implode('","', $Betroffene).'"';
		$sql .= ' AND (Lehrer IN ('.$Betroffene.') OR Lehrer_Neu IN ('.$Betroffene.'))';
	}
	$sql .= ' ORDER BY Datum';
	$query = mysql_query($sql);
	if (isset ($_REQUEST['Tag']) && is_numeric($_REQUEST['Tag']))
	{
		$wid = getID_Woche($_REQUEST['Tag']);
	}
	else
	{
		$wid = -1;
	}
	$Nachricht = array ();
	while ($v = mysql_fetch_array($query))
	{
		$w = getID_Woche($v['Datum']);
		if ($w == $wid && $w != -1)
		{
			$f = array('Klasse', 'Lehrer');
			foreach ($f as $Feld)
			{
				$a = array ($Feld, $v[$Feld]);
				if (!in_array($a, $Plaene) && $v[$Feld] != '' &&
				!($Feld == 'Lehrer' && $v['Art'] == VERTRETUNG_NURBEMERKUNG) )
				// den Betroffenen keine Mitteilung machen
				// nun doch wieder eine Meldung für den Betroffenen
				//	 !($Feld == 'Lehrer' && $Verhinderung['Wer'] == $v[$Feld]))
				{
					$Plaene[] = $a;
				}
				$a = array ($Feld, $v[$Feld.'_Neu']);
				if (!in_array($a, $Plaene) && $v[$Feld.'_Neu'] != '' &&
				!($Feld == 'Lehrer' && $v['Art'] == VERTRETUNG_NURBEMERKUNG))
				{
					$Plaene[] = $a;
				}
				if ($v['Art'] == VERTRETUNG_NURBEMERKUNG && $Feld == 'Lehrer')
				{
					$Lehrer = new Lehrer($v['Lehrer'], LEHRERID_KUERZEL);
					if (isset ($v['Klasse']) && !isset ($Nachricht[$v['Klasse']]) ||
					!in_array($Lehrer->Vorname.' '.$Lehrer->Name, $Nachricht[$v['Klasse']]))
					{
						$Nachricht[$v['Klasse']][] = $Lehrer->Vorname.' '.$Lehrer->Name;
					}
				}
				if (!isset ($Wochen[$Feld][$v[$Feld]]) ||!is_array($Wochen[$Feld][$v[$Feld]]) ||
				!in_array($w, $Wochen[$Feld][$v[$Feld]]))
				{
					$Wochen[$Feld][$v[$Feld]][] = $w;
				}
				if (!isset ($Wochen[$Feld][$v[$Feld.'_Neu']]) ||!is_array($Wochen[$Feld][$v[$Feld.'_Neu']]) ||
				!in_array($w, $Wochen[$Feld][$v[$Feld.'_Neu']]))
				{
					$Wochen[$Feld][$v[$Feld.'_Neu']][] = $w;
				}								
			}
			// Für Raumänderungen: Der spezielle Raumplan soll erscheinen
			if ($Verhinderung['Art'] == 'Raum' && 
			($v['Raum'] == $Verhinderung['Wer'] || $v['Raum_Neu']==$Verhinderung['Wer'])) 
			{
				if ( ! isset($Wochen['Raum'][$Verhinderung['Wer']]) || 
				! in_array($w, $Wochen['Raum'][$Verhinderung['Wer']]))
				{
				$Wochen['Raum'][$Verhinderung['Wer']][] = $w;
				}
			}
		}
	}
	// Benachrichtung der Klasse bei nicht betroffenem Lehrer
	foreach ($Nachricht as $Klasse => $Lehrer)
	{
		$key = array_search(array ('Klasse', $Klasse), $Plaene);
		if ($key !== false)
		{
			$Plaene[$key][1] .= '/'.implode(',', $Lehrer);
		}
	}
	mysql_free_result($query);
	// Mitteilung für Sekretariat und Abteilungsleitung
	if ($PDF != PDF_MAIL && $PDF != PDF_KEIN )
	{
		$KlassenPlaene = array();
		foreach ( $Plaene as $Klasse)
		{
		 if ( $Klasse[0] == 'Klasse')
		 {
		 	$KlassenPlaene[] = array ('Klasse', $Klasse[1].'/Aushang');
		 }
		}
		// Kopie für Sekretariat entfernt 12.09.06
		//$Plaene[] = array ($Verhinderung['Art'], $Verhinderung['Wer'].'/Sekretariat');
		$Plaene[] = array ($Verhinderung['Art'], $Verhinderung['Wer'].'/Abteilungsleitung');
		//$Plaene[] = array ($Verhinderung['Art'], $Verhinderung['Wer'].'/Aushang');
		$Plaene = array_merge($Plaene, $KlassenPlaene);
	}
}
elseif (isset ($_REQUEST['Datum']) && !isset ($Art))
{
	$Wochen = array ();
	$Plaene = array ();
	// alle Vertretung an einem bestimmten Tag
	$Datum = explode('.', $_REQUEST['Datum']);
	$Datum = mktime(0, 0, 0, $Datum[1], $Datum[0], $Datum[2]);
	$w = getID_Woche($Datum);
	if ( $w > 0 )
	{
	 $query = mysql_query("SELECT * FROM T_Vertretungen WHERE Datum=$Datum");
	 while ($v = mysql_fetch_array($query))
	 {
	 	foreach (array ('Klasse', 'Lehrer') as $Feld)
	 	{
	 		$a = array ($Feld, $v[$Feld]);
	 		if (!in_array($a, $Plaene) && $v[$Feld] != '')
	 		{
	 			$Plaene[] = $a;
	 		}
	 		$a = array ($Feld, $v[$Feld.'_Neu']);
	 		if (!in_array($a, $Plaene) && $v[$Feld.'_Neu'] != '')
	 		{
	 			$Plaene[] = $a;
	 		}
	 		if (!is_array($Wochen[$Feld][$v[$Feld]]) || !in_array($w, $Wochen[$Feld][$v[$Feld]]))
	 		{
	 			$Wochen[$Feld][$v[$Feld]][] = $w;
	 		}
	 		if (!is_array($Wochen[$Feld][$v[$Feld.'_Neu']]) || !in_array($w, $Wochen[$Feld][$v[$Feld.'_Neu']]))
	 		{
	 			$Wochen[$Feld][$v[$Feld.'_Neu']][] = $w;
	 		}
	 	}
	 }
	}
	mysql_free_result($query);
}
elseif (isset ($Art) && isset ($_REQUEST[$Art]) && isset($_REQUEST['Woche']))
{
	$Plaene[] = array ($Art, $_REQUEST[$Art]);
	foreach (explode(',', $_REQUEST['Woche']) as $Woche)
	$Wochen[$Art][$_REQUEST[$Art]][] = $Woche;
}
$LehrerDabei = false;
include_once ('include/Abteilungen.class.php');
$Abteilung = new Abteilungen($db);

if (isset ($Plaene) && is_array($Plaene))
{
	foreach ($Plaene as $planeintrag)
	{
		$Art = $planeintrag[0];
		$Wer = $planeintrag[1];
		$feld = explode('/', $Wer);
		$Wer = $feld[0];
		if (isset ($feld[1]))
		{
			$Zusatz = $feld[1];
		}
		else
		{
			$Zusatz = '';
		}
		if ($Art == 'Lehrer')
		{
			$LehrerDabei = true;
			$Lehrer = new Lehrer($Wer, LEHRERID_KUERZEL);
			$LehrerName = $Lehrer->Anrede($LEHRER_LEHRER, true);
		} else
		{
			$LehrerName = $Art.' '.$Wer;
		}
		if ($PDF > PDF_KEIN)
		{
			PDF_set_info($p, 'Title', 'Planänderung '.$LehrerName);
		}
		if (isset ($Wochen[$Art][$Wer]))
		{
			$AlleWochen = $Wochen[$Art][$Wer];
		}
		else
		{
			$AlleWochen = array (getID_Woche());
		}
		foreach ($AlleWochen as $Woche)
		{
			$Montag = getMontag($Woche);
			$Version = getAktuelleVersion($Montag);
			$Turnusliste = array ();
			getTurnusliste($Woche, $Turnusliste);
			$sql = 'SELECT * FROM T_Vertretungen WHERE ('.$Art."='".mysql_real_escape_string($Wer)."' OR ".$Art."_Neu='".mysql_real_escape_string($Wer)."') ";
			$sql .= " AND Datum BETWEEN $Montag AND ".strtotime('+5 days', $Montag).' ORDER BY Datum,Stunde';
			$query = mysql_query($sql);
			if ($PDF == PDF_KEIN)
			{
				echo "<hr />\n";
				echo '<table class="Liste">';
				echo '<tr><th colspan="7"><span class="ueberschrift">Planänderung für '.
				$LehrerName;
				echo '</span> <span class="smallmessage_gr">';
				echo getKW($Woche).". KW (Turnus ";
				echo implode(",", $Turnusliste).")";
				echo '<br />';
				echo 'Stand '.date('d.m.Y H:i');
				$sql = 'SELECT * FROM T_Verhinderungen WHERE Von <= '.strtotime('+5 days', $Montag).' AND Bis >= '.$Montag." AND Art='$Art' AND Wer='$Wer' ORDER BY Von";
				$q = mysql_query($sql);
				while ($v = mysql_fetch_array($q))
				{
					echo '<br />'.$Gruende[$v['Grund']];
					if ($v['Von'] != $v['Bis'])
					{
						echo ' vom ';
					}
					else
					{
						echo ' am ';
					}
					echo date('d.m.Y', $v['Von']);
					if ($v['Von'] != $v['Bis'])
					{
						echo ' bis '.date('d.m.Y', $v['Bis']);
					}
				}
				mysql_free_result($q);
				echo "</span>\n";
				echo "</th></tr>\n";
				echo '<tr><th>Block</th><th>Klasse</th><th>Fach</th><th>Lehrer</th><th>Lehrer neu';
				echo '</th><th>Raum</th><th>Bemerkung</th>';
				echo "</tr>\n";
			} else
			{
				// PDF-Kopf
				PDF_begin_page($p, 595, 842);
				$bb = 'oszimtlogo300.jpg';
				if (file_exists($bb))
				{
					// Alter Server : $pim = pdf_open_image_file($p, 'jpeg', $bb);
					$pim = pdf_load_image($p, 'jpeg', $bb, '');
					pdf_place_image($p, $pim, 475, 790, 0.2);
					pdf_close_image($p, $pim);
				}
				pdf_setfont($p, $Fonts['Arial']['N'], 6.0);
				pdf_show_xy($p, 'Berufliches Gymnasium, Berufsoberschule,', 450, 785);
				pdf_continue_text($p, 'Fachoberschule, Berufsfachschule,');
				pdf_continue_text($p, 'Fachschule und Berufsschule');
				pdf_continue_text($p, 'Haarlemer Straße 23-27, 12359 Berlin-Neukölln');
				pdf_continue_text($p, 'Tel.: 030-606-4097 Fax: 030-606-2808');
				pdf_continue_text($p, 'http://www.oszimt.de');
				pdf_setfont($p, $Fonts['Arial']['B'], 16.0);
				pdf_show_xy($p, 'Planänderung für', 50, 810);
				pdf_continue_text($p, $LehrerName);
				if ($Art == 'Klasse')
				{
					pdf_setfont($p, $Fonts['Arial']['O'], 10.0);
					$L = findeKlassenlehrer($Wer);
					if ($L != '')
					{
						pdf_show($p, ' (Klassenlehrer: '.$L.')');
					}
					pdf_setfont($p, $Fonts['Arial']['B'], 16.0);
				}
				pdf_continue_text($p, '');
				pdf_setfont($p, $Fonts['Arial']['N'], 10.0);
				pdf_show($p, getKW($Woche).'. KW (Turnus '.implode(',', $Turnusliste).')');
				pdf_continue_text($p, 'Stand: '.date('d.m.Y H:i'));
				pdf_setfont($p, $Fonts['Arial']['N'], 10.0);
				$Tabelle = array ();
			}
			$Datum = 0;
			$Block = 0;
			$Tag = 0;
			$LBlock = 0;
			$Bearbeiter = array ();
			while ($row = mysql_fetch_array($query))
			{
				if (!isset ($Bearbeiter[$row['Bearbeiter']]) || $Bearbeiter[$row['Bearbeiter']] < $row['Stand'])
				{
					$Bearbeiter[$row['Bearbeiter']] = $row['Stand'];
				}
				if ($Datum != $row['Datum'])
				{
					$Tag = fuelleTag($row['Datum'], $Montag, $Tag, $Block);
					$Block = 1;
					$LBlock = 0;
					$Datum = $row['Datum'];
				}
				for ($i = $Block; $i < $row['Stunde']; $i ++)
				{
					// Normalen Stundenplan anzeigen?
					if ($PDF != PDF_KEIN)
					{
						$Tabelle[Count($Tabelle)][0]['value'] = $i;
						$Tabelle[Count($Tabelle) - 1][1]['value'] = '';
						$Tabelle[Count($Tabelle) - 1][2]['value'] = '';
						$Tabelle[Count($Tabelle) - 1][3]['value'] = '';
						$Tabelle[Count($Tabelle) - 1][4]['value'] = '';
						$Tabelle[Count($Tabelle) - 1][5]['value'] = '';
						$Tabelle[Count($Tabelle) - 1][6]['value'] = '';
						$Tabelle[Count($Tabelle) - 1][6]['Breite'] = 200;
					} else
					{
						echo "<tr><td>$i</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
			 	 <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
					}
				}
				if ($PDF == PDF_KEIN)
				{
					echo "<tr>\n";
					echo '<td>';
					if ($LBlock != $row['Stunde'])
					{
						echo $row['Stunde'];
					}
					$LBlock = $row['Stunde'];
					echo "</td>\n";
					echo '<td>';
					if ($row['Klasse'] == '')
					{
						echo $row['Klasse_Neu'];
					}
					else
					{
						echo $row['Klasse'];
					}
					echo "</td>\n";
					echo '<td>';
					echo $row['Fach'];
					if ($row['Fach_Neu'] != '' && $row['Fach'] != $row['Fach_Neu'])
					{
						echo '&rarr;'.$row['Fach_Neu'];
					}
					echo "</td>\n";
					$Lehrer = new Lehrer($row['Lehrer'], LEHRERID_KUERZEL);
					echo '<td>'.$Lehrer->Name."</td>\n";
					echo '<td>';
					$Lehrer = new Lehrer($row['Lehrer_Neu'], LEHRERID_KUERZEL);
					if ($row['Lehrer_Neu'] != $row['Lehrer'])
					{
						echo $Lehrer->Name;
					}
					echo "</td>\n";
					echo '<td>';
					if ($row['Raum_Neu'] != $row['Raum'] && $row['Raum_Neu'] != '')
					{
						echo $row['Raum'].'&rarr;';
					}
					echo "{$row['Raum_Neu']}</td>\n";
					echo ' <td>';
					$s = '';
					if ($row['Raum_Neu'] == '' && $row['Klasse_Neu'] == '' && $row['Lehrer_Neu'] == '' && $row['Fach_Neu'] == '')
					{
						$s = "entfällt\n";
					}
					else
					{
						if ($row['Lehrer'] != $row['Lehrer_Neu'])
						{
							$Lehrer = new Lehrer($row['Lehrer_Neu'], LEHRERID_KUERZEL);
							if ($row['Art'] == VERTRETUNG_TEILUNGAUFHEBEN)
							{
								$s .= $Lehrer->Name." allein\n";
							}
							if ($row['Art'] == VERTRETUNG_ZUSATZKLASSE)
							{
								$s .= 'neben regulärem Unterricht von '.$Lehrer->Name."\n";
							}
						}
						if ($row['Fach'] != '' && $row['Fach'] != $row['Fach_Neu'])
						{
							$s .= 'Unterrichtsänderung: '.$row['Fach_Neu']."\n";
						}
						if ($row['Fach'] == '' && $row['Fach'] != $row['Fach_Neu'] && $row['Art'] != VERTRETUNG_RAUMZUSATZ )
						{
							$s .= 'Zusätzlicher Block: '.$row['Fach_Neu']."\n";
						}
					}
					if ($row['Bemerkung'] != '')
					{
						$s .= trim($row['Bemerkung']);
					}
					echo '<div style="float:right"><a href="../Vertretung/VertretungBemerkung.php';
					echo "?Vertretung_id=".$row["Vertretung_id"];
					echo '" target="_blank">';
					echo '<img border="0" src="edit_small.gif"/></a></div>'."\n";
					echo nl2br(trim($s));
					echo "</td>\n";
					echo "</tr>\n";
				} else
				{
					if ($LBlock != $row['Stunde'])
					{
						$Tabelle[Count($Tabelle)][0]['value'] = $row['Stunde'];
					}
					else
					{
						$Tabelle[Count($Tabelle)][0]['value'] = '';
					}
					$LBlock = $row['Stunde'];
					if ($row['Klasse'] == '')
					{
						$Tabelle[Count($Tabelle) - 1][1]['value'] = $row['Klasse_Neu'];
					}
					else
					{
						$Tabelle[Count($Tabelle) - 1][1]['value'] = $row['Klasse'];
					}
					$Tabelle[Count($Tabelle) - 1][1]['Breite'] = 35;
					$Tabelle[Count($Tabelle) - 1][2]['value'] = $row['Fach'];
					if ($row['Fach_Neu'] != '' && $row['Fach'] != $row['Fach_Neu'])
					{
						if ( $row['Frach']=='')
						{
							$Tabelle[Count($Tabelle) - 1][2]['value'] = $row['Fach_Neu'];
						}
						else
						{
							$Tabelle[Count($Tabelle) - 1][2]['value'] .= ' {Symbol:174} '.$row['Fach_Neu'];
						}
					}
					$Tabelle[Count($Tabelle) - 1][2]['Breite'] = 50;
					$Lehrer = new Lehrer($row['Lehrer'], LEHRERID_KUERZEL);
					$Tabelle[Count($Tabelle) - 1][3]['value'] = $Lehrer->Name;
					$Tabelle[Count($Tabelle) - 1][3]['Breite'] = 65;
					if ($row['Lehrer_Neu'] != $row['Lehrer'])
					{
						$Lehrer = new Lehrer($row['Lehrer_Neu'], LEHRERID_KUERZEL);
						$Tabelle[Count($Tabelle) - 1][4]['value'] = $Lehrer->Name;
					}
					$Tabelle[Count($Tabelle) - 1][4]['Breite'] = 65;
					$Tabelle[Count($Tabelle) - 1][5]['value'] = $row['Raum_Neu'];
					$Tabelle[Count($Tabelle) - 1][5]['Breite'] = 55;
					if ($row['Raum_Neu'] != $row['Raum'] && $row['Raum_Neu'] != '')
					{
						if ( trim($row['Raum']) != '' )
						{
							$Tabelle[Count($Tabelle) - 1][5]['value'] = $row['Raum'].'{Symbol:174}';
						}
						else
						{
							$Tabelle[Count($Tabelle) - 1][5]['value'] = '';
						}

						$Tabelle[Count($Tabelle) - 1][5]['value'] .= $row['Raum_Neu'];
					}

					$Tabelle[Count($Tabelle) - 1][6]['value'] = '';
					$Tabelle[Count($Tabelle) - 1][6]['Breite'] = 200;
					if ($row['Raum_Neu'] == '' && $row['Klasse_Neu'] == '' && $row['Lehrer_Neu'] == '' && $row['Fach_Neu'] == '')
					{
						$Tabelle[Count($Tabelle) - 1][6]['value'] .= 'entfällt';
					}
					else
					{
						if ($row['Lehrer'] != $row['Lehrer_Neu'])
						{
							$Lehrer = new Lehrer($row['Lehrer_Neu'], LEHRERID_KUERZEL);
							if ($row['Art'] == VERTRETUNG_TEILUNGAUFHEBEN)
							{
								$Tabelle[Count($Tabelle) - 1][6]['value'] .= $Lehrer->Name.' allein';
							}
							if ($row['Art'] == VERTRETUNG_ZUSATZKLASSE)
							{
								$Tabelle[Count($Tabelle) - 1][6]['value'] .= 'neben regulärem Unterricht von '.$Lehrer->Name;
							}
						}
					}
					if (trim($row['Bemerkung']) != '')
					{
						$Tabelle[Count($Tabelle) - 1][6]['value'] .= ' ('.trim($row['Bemerkung']).')';
					}
				}
				$Block = $row['Stunde'] + 1;
			}
			fuelleTag(strtotime('+5 days', $Montag), $Montag, $Tag, $Block);
			if ($PDF == PDF_KEIN)
			{
				echo "</table>\n";
			}
			else
			{
				pdf_Tabelle($p, $Tabelle);
				if ($Zusatz != '')
				{
					pdf_setfont($p, $Fonts['Arial']['B'], 14.0);
					pdf_show_xy($p, "Kopie für $Zusatz", 50, pdf_get_value($p, 'texty', 0) - 40);
				}
				pdf_setfont($p, $Fonts['Arial']['N'], 6.0);
				$s = '';
				$z = '';
				foreach ($Bearbeiter as $name => $stand)
				{
					$s .= "$z$name ($stand)";
					$z = ', ';
				}
				pdf_show_xy($p, 'Bearbeitet durch: '.$s, 50, pdf_get_value($p, 'texty', 0) - 10);
				PDF_end_page($p);
				$Seitenzahl++;
				if ($PDF == PDF_MAIL)
				{
					if ($Art == 'Lehrer')
					{
						VerschickeMail($Wer, getKW($Woche), $p);
					}
					else
					{
						PDF_close($p);
					}
					PDF_delete($p);
					$p = PDF_new();
					PDF_open_file($p, '');
					PDF_set_info($p, 'Creator', 'OSZIMT');
					PDF_set_info($p, 'Author', 'Christoph Griep');
					$Fonts = LadeFonts($p);
					$Seitenzahl = 0;
				}
			}
		} // Wochendurchlauf
		if ($PDF == PDF_KEIN)
		{
			if (isset ($_REQUEST['Verhinderung_id']))
			{
				echo '<a href="'.$_SERVER['PHP_SELF'].'?PDF='.PDF_PDFVERAENDERT;
				foreach ($_REQUEST as $key => $value)
				echo "&$key=$value";
				if (is_array($_REQUEST['Woche']))
				{
					echo '&Woche='.implode(',', $_REQUEST['Woche']);
				}
				echo '">PDF-Version (geänderte) zum Ausdrucken</a> / ';
			}
			echo '<a href="'.$_SERVER['PHP_SELF'].'?PDF='.PDF_PDFALLE;
			foreach ($_REQUEST as $key => $value)
			{
				echo "&$key=$value";
			}
			if (is_array($_REQUEST['Woche']))
			{
				echo '&Woche='.implode(',', $_REQUEST['Woche']);
			}
			echo '">PDF-Version (alle) zum Ausdrucken</a> ';
			if ($LehrerDabei)
			{
				if ($Abteilung->isAbteilungsleitung() ||
				$_SERVER['REMOTE_USER'] == 'Seidel' ||
				$_SERVER['REMOTE_USER'] == 'Griep')
				{
					echo '/ <a href="'.$_SERVER['PHP_SELF'].'?PDF='.PDF_MAIL;
					foreach ($_REQUEST as $key => $value)
					{
						echo "&$key=$value";
					}
					if (is_array($_REQUEST['Woche']))
					{
						echo '&Woche='.implode(',', $_REQUEST['Woche']);
					}
					echo '" target="_blank">Pläne an die angezeigten Lehrer per Mail senden</a> ';
				}
			}
		} // wenn nicht PDF
	}
}
if ($PDF != PDF_KEIN)
{
	if ( $Seitenzahl > 0 )
	{
		PDF_close($p);
	}
	if ($PDF != PDF_MAIL)
	{

		$buf = PDF_get_buffer($p);
		$len = strlen($buf);
		header('Content-type: application/pdf');
		header('Content-Length: '.$len);
		header('Content-Disposition: inline; filename=Planaenderung'.$LehrerName.'.pdf');
	 //header('Pragma: no-cache');
 	//header('Expires: 0');
		print $buf;
	} else
	echo '<br />Sie können dieses Fenster nun <a href="javascript:window.close()">schließen</a>.';
	PDF_delete($p);
}
if ($PDF <= PDF_MAIL)
{
	echo '</td></tr>';
	include ('include/footer.inc.php');
}
?>
