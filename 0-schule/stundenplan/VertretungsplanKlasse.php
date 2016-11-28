<?php
/*
* VertretungsplanKlasse.php
* Zeigt den Vertretungsplan für alle Klassen, wobei der aktuelle Tag sowie der
* nächste Schultag angezeigt wird.
*
* Erstellt: 23.04.06
*/

include('include/config.php');
include('include/stupla.inc.php');
include_once("include/oszframe.inc.php");
include('include/Vertretungsplan.inc.php');
$Wochentagnamen[0] = 'Sonntag';
$Wochentagnamen[1] = 'Montag';
$Wochentagnamen[2] = 'Dienstag';
$Wochentagnamen[3] = 'Mittwoch';
$Wochentagnamen[4] = 'Donnerstag';
$Wochentagnamen[5] = 'Freitag';
$Wochentagnamen[6] = 'Samstag';

$Datum = strtotime(date('Y-m-d'));
$Datum = bestimmeNaechstenSchultag($Datum);
$Datum1 = bestimmeNaechstenSchultag(strtotime('+1 day',$Datum), false);

if ( isset($_REQUEST['Klasse']) && trim($_REQUEST['Klasse']) != '')
{
	$Klasse = mysql_real_escape_string($_REQUEST['Klasse']);
	$Ueberschrift = 'Stundenplanänderungen der '.$Klasse.', Stand '.date('d.m.Y H:i');
	echo ladeOszKopf_o("Stundenplanänderungen der ".$Klasse, $Ueberschrift);
	echo '<style type="text/css">
@import url(http://css.oszimt.de/oszimt.css) screen, print;
</style>';
	include('include/Lehrer.class.php');
	include('include/Vertretungen.inc.php');

// Alle Vertretungen von einem Tag anzeigen
	$sql = 'SELECT * FROM T_Vertretungen WHERE (Datum='.$Datum.
		' OR Datum='.$Datum1.') AND (Klasse="'.$Klasse.'" OR Klasse_Neu="'.$Klasse.
		'") ORDER BY Datum,Stunde';
	if (! $query = mysql_query($sql))
	{
		echo '<div class="Fehler">Datenbankfehler: '.mysql_error().'</div>';
	}
	elseif ( mysql_num_rows($query) == 0 )
	{
		echo '<div class="Hinweis">Am '.date('d.m.Y',$Datum).' liegen keine 
  Stundenplanänderungen für die Klasse '.$Klasse.' vor.</div>';
	}
	else
	{
		$Datum1 = 0;
		while ( $row = mysql_fetch_array($query))
		{
			if ( $Datum1 != $row['Datum'])
			{
				if ( $Datum1 != 0 )
				{
					echo '</table>';
				}
				echo '<h1>';
				echo $Wochentagnamen[date('w',$row['Datum'])].', den ';
				echo date('d.m.Y',$row['Datum']).'</h1>';

				echo '<table class="Liste">';
				echo '<tr><th>Block</th><th>Fach</th>' .
    		'<th>Lehrer</th><th>Lehrer neu';
				echo '</th><th>Raum</th><th>Bemerkung</th>';
				echo "</tr>\n";
				$Datum1= $row['Datum'];
			}
			echo "<tr>\n";
			echo '<td>';
			echo $row['Stunde'];
			echo "</td>\n";
			echo '<td>';
			echo $row['Fach'];
			if ( $row['Fach_Neu'] != '' && $row['Fach']!= $row['Fach_Neu'])
			echo ' &rarr;'.$row['Fach_Neu'];
			echo "</td>\n";
			echo '<td>'.$row['Lehrer']."</td>\n";
			echo '<td>';
			if ( $row['Lehrer_Neu'] != $row['Lehrer'] )
			echo $row['Lehrer_Neu'];
			echo "</td>\n";
			echo '<td>';
			if ( $row['Raum_Neu'] != $row['Raum'] && $row['Raum_Neu'] != '')
			{
				echo $row['Raum'].' &rarr;';
			}
			echo "{$row['Raum_Neu']}</td>\n";
			echo' <td>';
			$s = '';
			if ( $row['Raum_Neu'] == '' && $row['Klasse_Neu'] == ''
			&& $row['Lehrer_Neu'] == '' && $row['Fach_Neu'] == '' )
			{
				$s = 'entfällt. ';
			}
			else
			{
				if ( $row['Lehrer'] != $row['Lehrer_Neu'] )
				{
					$Lehrer = new Lehrer($row['Lehrer_Neu'], LEHRERID_KUERZEL);
					if ( $row['Art'] == VERTRETUNG_TEILUNGAUFHEBEN )
					{
						$s .= $Lehrer->Name." allein\n";
					}
					if ( $row['Art'] == VERTRETUNG_ZUSATZKLASSE )
					{
						$s .= "neben regulärem Unterricht von ".$Lehrer->Name."\n";
					}
				}
				if ( $row['Fach'] != '' && $row['Fach'] != $row['Fach_Neu'] )
				{
					$s .= 'Unterrichtsänderung: '.$row['Fach_Neu']."\n";
				}
				if ( $row['Fach'] == '' && $row['Fach'] != $row['Fach_Neu'] && $row['Art'] != VERTRETUNG_RAUMZUSATZ )
				{
					$s .= 'Zusätzlicher Block: '.$row['Fach_Neu']."\n";
				}
			}
			if ($row['Bemerkung']!= '' )
			{
				$s .= trim($row['Bemerkung']);
			}
			echo nl2br(trim($s));
			echo "</td>\n";
			echo "</tr>\n";
	} // while
	echo '</table>';
	echo '<div class="Hinweis">Alle Angaben ohne Gewähr. Beachten Sie jeweils das Gültigkeitsdatum!</div>';
} // wenn Datensätze vorhanden
mysql_free_result($query);
}
else
{
 // Keine Klasse übergeben
	echo ladeOszKopf_o("OSZ IMT Vertretungspläne der Klassen","Vertretungspläne der Klassen");
		echo '<style type="text/css">
@import url(http://css.oszimt.de/oszimt.css) screen, print;
</style>';
	
    echo '<div class="Fehler">Keine Klasse angegeben!</div>';
}

echo ladeOszKopf_u();

echo ladeLink("http://www.oszimt.de","<b>Home</b>");
if ( isset($Klasse) )
{
	echo ladeLink("KPlan1.php?Klasse=".$Klasse,"Plan ".$Klasse);
}
echo ladeOszFuss();

?>
