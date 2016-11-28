<?php
/**
 * Listet alle Termine auf
 * (c) 2006 Christoph Griep
 */
if ( isset($_REQUEST['Jahr']) && is_numeric($_REQUEST['Jahr']) )
{
$jahr = $_REQUEST['Jahr'];
}
elseif ( date('m') < 8 )
{
$jahr = date('Y')-1;
}
else
{
$jahr = date('Y');
}
$Schuljahr = sprintf('%02d',($jahr-2000)).'/'.sprintf('%02d',$jahr-1999);

$von = time();
if ( isset($_REQUEST['von']) ) 
{
$von = $_REQUEST['von'];
}
$bis = mktime(0,0,0,8,1,$jahr+1);
if ( isset($_REQUEST['bis']) ) 
{
$bis = $_REQUEST['bis'];
}

if ( ! isset($_REQUEST['Export']))
{
	$Ueberschrift = 'Terminplan '.$Schuljahr;
	include('include/header.inc.php');
	include('include/Termine.class.php');
	$Termine = new Termine($db, true);
	$Termine->CheckScriptEinfuegen();
	?>
<style type="text/css">
<!--
.untenRahmen { border-bottom-width:1pt; border-bottom-style:solid; border-bottom-color: black;}
.Termintable { border-spacing: 0pt; border-collapse: collapse;}
-->
</style>
	<?php
}
else
{
	include('include/config.php');
	include('include/Termine.class.php');
	$Termine = new Termine($db, true);
	require_once 'Spreadsheet/Excel/Writer.php';
	$workbook = new Spreadsheet_Excel_Writer();
}
if ( !isset($_REQUEST['Vorlaeufig']) )
{
	$MitVorlaeufig = true;
	$Vorlaeufig = '';
}
else
{
	$MitVorlaeufig = false;
	$Vorlaeufig = 'NOT Vorlaeufig AND ';
}
if ( ! isset($_REQUEST['Export']))
{
	echo '<tr><td align="center">';
	echo '<strong>Stand: ';
	echo $Termine->holeStand($MitVorlaeufig).'</strong>';
	echo '</td></tr>';

	if ( ! isset($_REQUEST['Print']) )
	{
		echo '<tr><td align="center">';
		if ( $jahr > date('Y')) echo '<a href="?Jahr='.($jahr-1).'">Vorangehendes Jahr</a> /';
		echo' <a href="?Jahr='.($jahr+1).'">nächstes Jahr</a></td></tr>';
		echo '<tr><td align="center"><a href="Termine.php">Zur Terminübersicht</a></td></tr>';
		echo '<tr><td align="center"><a href="/">Zurück zum internen Lehrerbereich</a></td></tr>';
	}
	echo '<tr><td>';
	echo '<table class="Termintable">';
	if ( $Termine->istGefiltert() )
	{
		echo '<tr><td align="center" class="content-small-bold" colspan="3">';
		echo 'Angezeigt werden Termine für ';
		$Termine->holeFilterNamen();
		echo '</td></tr>';
	}
}
else
{
	$workbook->send('Termine.xls');
	$worksheet =& $workbook->addWorksheet('Termine');
	$worksheet->writeString(0,0,'Datum');
	$worksheet->writeString(0,1,'Bezeichnung');
	$worksheet->writeString(0,2,'Beschreibung');
	$worksheet->writeString(0,3,'Klassifikation');
	$worksheet->writeString(0,4,'Betroffen');
	$worksheet->setColumn(1,2,50);
}
$sql = "SELECT * FROM T_FreiTage WHERE ersterTag <= $bis AND letzterTag >= $von ORDER BY ersterTag";
$freiquery = mysql_query($sql, $db);
if ( ! $frei = mysql_fetch_array($freiquery)) unset($frei);
$query = mysql_query("SELECT * FROM T_Termin_Termine WHERE $Vorlaeufig Datum BETWEEN ".$von.' AND '.
$bis.' ORDER BY Datum', $db);
$dieTermine = array();
while ($termin = mysql_fetch_array($query) )
{
	// Ferien einbauen
	if ( $Termine->istBetroffen($termin['Betroffene']) )
	{
		while ( isset($frei) && $frei['ersterTag'] < $termin['Datum'] )
		{
			$fTermin['Datum'] = '';
			$fTermin['Bezeichnung'] = $frei['Kommentar'].' ('.date('d.m.',$frei['ersterTag']).
			' - '.date('d.m.',$frei['letzterTag']).')';
			$fTermin['Betroffene'] = '';
			$fTermin['Vorlaeufig'] = false;
			$fTermin['Beschreibung'] = '';
			$fTermin['F_Klassifikation'] = -1;

			$dieTermine[] = $fTermin;
			if ( ! $frei = mysql_fetch_array($freiquery)) unset($frei);
		}
		$dieTermine[] = $termin;
	}
}
mysql_free_result($query);
mysql_free_result($freiquery);
$zeile =1;
foreach ($dieTermine as $nr => $termin )
{
	if ( ! isset($_REQUEST['Export']))
	{
		echo '<tr ';
		if ( $termin['Datum'] == '' ) 
		{
		echo 'class="unterlegt"';
		}
		echo '><td class="untenRahmen">';
		if ( $termin['Datum'] != '' )
		{
			if ( $termin['Vorlaeufig'] )
			{
			echo '<span class="content-bold">';
			}
			echo date('d.m.Y',$termin['Datum']);
			$Uhrzeit = date('H:i',$termin['Datum']);
			if ( $Uhrzeit != '00:00' ) echo ' <span class="home-content-small">'.$Uhrzeit.'</span>';
			if ( $termin['Vorlaeufig'] )
			{
			echo '</span>';
			}
		}
		echo '&nbsp;</td><td class="home-content untenRahmen">';
		echo $Termine->BetroffeneAnzeigen($termin['Betroffene'], '<br />', false);
		echo '&nbsp;</td><td class="untenRahmen">';
		if ( $termin['Vorlaeufig'] )
		{
			echo '<span class="content-bold">VORLÄUFIG:</span> ';
		}
		echo '<span class="home-content-titel">'.		stripslashes($termin['Bezeichnung']);
		echo '</span> ';
		if ( $Termine->getKlassifikation($termin['F_Klassifikation']) != '' )
		{
			echo '<span class="home-content-small">(';
			echo $Termine->getKlassifikation($termin['F_Klassifikation']);
			echo ')</span>';
		}
		echo '<br />';
		if ( trim($termin['Beschreibung']) != '' )
		{
		echo nl2br(stripslashes($termin['Beschreibung']));
		}
		echo '</td></tr>';
	}
	else
	{
		// Termine in Excel-Datei schreiben 
		$s = '';
		if ( $termin['Datum'] != '' )
		{
			$s .= date('d.m.Y',$termin['Datum']);
			$Uhrzeit = date('H:i',$termin['Datum']);
			if ( $Uhrzeit != '00:00' ) $s .= ' '.$Uhrzeit;
		}
		$worksheet->writeString($zeile,0,$s);
		$worksheet->writeString($zeile,1,str_replace("\r",'',stripslashes($termin['Bezeichnung'])));
		$worksheet->writeString($zeile,2,str_replace("\r",'',stripslashes($termin['Beschreibung'])));
		if ( $Termine->getKlassifikation($termin['F_Klassifikation']) != '' )
		{
			$worksheet->writeString($zeile,3,$Termine->getKlassifikation($termin['F_Klassifikation']));
		}
		$b = explode("\n", $Termine->BetroffeneAnzeigen($termin['Betroffene'], "\n", false));
		foreach ($b as $key=>$betroffen)
		{
			$worksheet->writeString($zeile,4+$key,trim($betroffen));			
		}
		$zeile++;	
	}
}
if ( ! isset($_REQUEST['Export']))
{
	echo '</table>';
	echo '</td></tr>';
	if ( ! isset($_REQUEST['Print'] ) )
	{
		echo '<tr><td>';
		$Termine->zeigeTerminfilter();
		echo '</td></tr>';
		echo '<tr><td align="center"><a href="'.$_SERVER['PHP_SELF'].'?Export=1">Als Exceldatei exportieren</a></td></tr>';
		echo '<tr><td align="center"><a href="Termine.php">Zur Terminübersicht</a></td></tr>';
		if ( $Termine->vorlaeufigPerson() )
		{
			echo '<tr><td align="center"><a href="Terminliste.php?Vorlaeufig=1">Terminliste mit vorläufigen Terminen</a></td></tr>';
			echo '<tr><td align="center"><a href="Terminliste.php">Terminliste ohne vorläufige Termine</a></td></tr>';
		}
		echo '<tr><td align="center"><a href="/">Zurück zum internen Lehrerbereich</a></td></tr>';

	}
	include('include/footer.inc.php');
}
else
{
	$workbook->close();
}
?>