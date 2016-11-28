<?php
/*
 * Erzeugt über eine OpenOffice-Vorlage einen Arbeitsplan, in dem automatisch
 * Datum, Feiertage/Ferien und Turnus eingetragen wird
 * (c) 2006 Christoph Griep
 */
if ( ! isset($_REQUEST['Los']) )
{
  $Ueberschrift = 'Arbeitsplanvorlage erstellen';
  $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
  include('include/header.inc.php');
  include('include/stupla.inc.php');
?>
<tr><td>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
Klasse <select name="Klasse" multisel="multisel">
<?php
$Version = getAktuelleVersion();
echo $Version.'!!!';
$query = mysql_query('SELECT DISTINCT Klasse FROM T_StuPla WHERE Version='.
  $Version.' ORDER BY Klasse');
while ( $klasse = mysql_fetch_array($query))
{
	echo '<option>'.$klasse['Klasse'].'</option>'."\n";
}
mysql_free_result($query);
?>
</select>
<br />
Turnus <select name="Gruppe">
<?php
$query = mysql_query('SELECT * FROM T_TurnusGruppe ORDER BY Name');
while ($Turnus = mysql_fetch_array($query))
{
	echo '<option value="'.$Turnus['ID_Gruppe'].'">'.$Turnus['Name'].'</option>'."\n";
}
mysql_free_result($query);
?>
</select><br />
Zusammenfassen (z.B. A/B/C ergibt eine Zeile) 
<input type="checkbox" name="Zusammenfassen" value="v" /><br />
Titel des Plans (optional) <input type="Text" name="Was" size="30" /> 

<input type="Submit" name="Los" value="Liste erstellen">
</form>
<em>Hinweis:</em> Der Arbeitsplan wird im StarOffice/OpenOffice-Format angeboten. Er 
kann nicht mit Word geöffnet werden!
<div align="center"><a href="/">Zurück zum internen Lehrerbereich</a></div>
</td></tr>
<?php
  include('include/footer.inc.php');
}
else
{
  include('include/config.php');
  include('include/turnus.inc.php');
set_magic_quotes_runtime(0);

define('PCLZIP_INCLUDE_PATH','');
define('ZIPLIB_INCLUDE_PATH','');
define('POO_TMP_PATH',dirname($_SERVER['SCRIPT_FILENAME'])); // Pfad /home/httpd/bscw-oszimt.de/html/Lizenz/tmp
//$_ENV['UPLOAD_TMP_DIR']);
require('phpOpenOffice.php');
$doc = new phpOpenOffice();
$anfang = time();

$Schuljahr = Schuljahr(true, $anfang);

$Gruppe = $_REQUEST['Gruppe'];
if ( !is_numeric($Gruppe)) $Gruppe = 1;
if ( isset($_REQUEST['Zusammenfassen']))
  $Zusammenfassen = true;
else
  $Zusammenfassen = false;
$daten = array();
$query = mysql_query('SELECT * FROM T_WocheBemerkungen WHERE Schuljahr="'.$Schuljahr.'"');
if ( $bemerkung = mysql_fetch_array($query))
{
	$daten['BEMERKUNG'] = str_replace("\t","\n",$bemerkung['Halbjahr2']);
}
else
  $daten['BEMERKUNG'] = '';
mysql_free_result($query);

$sql = 'SELECT * FROM T_Woche WHERE T_Woche.SJahr="'.$Schuljahr.'" ORDER BY Montag';
$query = mysql_query($sql);
$ErstTurnus = '';
$Wochenzeile = '';
$Hinweise = '';
$naechsterTag = 0;
$Zusatzzeile = '';

while ( $woche = mysql_fetch_array($query))
{  
  if  ($woche['Montag'] >= $naechsterTag)
  {
	if ( isset($bemerkung) && date('m', $woche['Montag']) == 2 )
	{
		$daten['WOCHE'][] = '';
		$daten['HINWEIS'][] = '';		
		$daten['WOCHE'][] = '';
		$daten['HINWEIS'][] = str_replace("\t","\n",$bemerkung['Halbjahr1']);
		$daten['WOCHE'][] = '';
		$daten['HINWEIS'][] = '';
		unset($bemerkung);
	}
	$sql = 'SELECT Turnus FROM T_WocheTurnus INNER JOIN T_Turnus ON F_ID_Turnus=ID_Turnus ' .
			'WHERE F_ID_Woche='.$woche['ID_Woche'].' AND F_ID_Gruppe='.$Gruppe;
    $fquery = mysql_query($sql);
    if ( $turnus = mysql_fetch_array($fquery))
      $Turnus = $turnus['Turnus'];
    else
      $Turnus = '';
    mysql_free_result($fquery);
	$sql = 'SELECT * FROM T_FreiTage WHERE ersterTag<='.
      strtotime('+5 days',$woche['Montag']).' AND letzterTag>='.$woche['Montag'].
      ' ORDER BY ersterTag';
    //echo $sql;
    $fquery = mysql_query($sql);
    $Hinweis = '';
    $Bezeichnung = '';
    while ( $feiertag = mysql_fetch_array($fquery))
    {
    	if ( $feiertag['ersterTag']>=$woche['Montag'] || 
    	     $feiertag['letzterTag']<=strtotime('+6 days',$woche['Montag']))
    	{
    		// einzelne freie Tage
    		$Anfang = 0;
    		if ( $woche['Montag'] < $feiertag['ersterTag'])
    		  $Anfang = $feiertag['ersterTag'];
    		else
    		  $Anfang = $woche['Montag'];
    		if ( strtotime('+5 days',$woche['Montag']) > $feiertag['letzterTag'])
    		  $Ende = $feiertag['letzterTag'];
    		else 
    		  $Ende = strtotime('+5 days', $woche['Montag']);
    		if ( $Ende-$Anfang > 0 )
    		  $H = 'Frei vom '.date('d.',$Anfang).'-'.
    		    date('d.m.',$Ende).': '.$feiertag['Kommentar'];
    		else
    		  $H = 'Frei am '.date('d.m.',$Anfang).': '.$feiertag['Kommentar'];
    		if ( $Hinweis != '' ) $Hinweis .= "\n";
    		$Hinweis .= $H;    		    	
    	}
    	if ( $Bezeichnung == '') 
    	{
    		$Bezeichnung = $feiertag['Kommentar'];
    		$Von = $feiertag['ersterTag'];
    		$Bis = $feiertag['letzterTag'];
    	}
    	if ( $feiertag['letzterTag'] > strtotime('+5day',$woche['Montag']))
    	  $naechsterTag = $feiertag['letzterTag'];
     }        
    mysql_free_result($fquery);
    if ( $Turnus == '' && $Bezeichnung != '' )
    {
      // freie Woche
      if ( $Zusammenfassen && $Wochenzeile != '' )
      {
      	$Zusatzzeile = $Bezeichnung.' vom '.
	      date('d.m.',$Von).' bis '.
	      date('d.m.y',$Bis);
      }
      else
      {
        $daten['WOCHE'][] = '';
        $daten['HINWEIS'][] = $Bezeichnung.' vom '.
	      date('d.m.',$Von).' bis '.
	      date('d.m.y',$Bis); // Ferienbezeichnung!
      } 
    }
    else
    {    	
	  if ( $ErstTurnus == $Turnus && $Zusammenfassen && $Wochenzeile != '')
	  {
	  	// Wochen Zusammenfassen
	  	$daten['WOCHE'][] = $Wochenzeile; 
  	    $daten['HINWEIS'][] = $Hinweise;
  	    $Wochenzeile = '';
  	    $Hinweise = '';
  	    if ( $Zusatzzeile != '')
  	    {
  	      $daten['WOCHE'][] = '';
  	      $daten['HINWEIS'][] = $Zusatzzeile;
  	      $Zusatzzeile = '';
	    }
	  }	  
	  if ( $Wochenzeile != '') 
	  {	  	
	  	$Wochenzeile .= "\n";
	  	if ( $Zusatzzeile != '')
	  	{
	  	  if ( $Hinweise != '') $Hinweise .= "\n";
	  	  $Hinweise .= $Zusatzzeile;
	  	  $Zusatzzeile = '';
	  	}
	  }
	  $Wochenzeile .= $Turnus.') '.
	    date('d.m.',$woche['Montag']).'-'.date('d.m.y',
  	    strtotime('+4 day', $woche['Montag']));
  	  if ($Hinweis != '')
  	  {
  	    if ( $Hinweise != '') $Hinweise .= "\n";
	    $Hinweise .= $Hinweis;
  	  }	  
	  if ( ! $Zusammenfassen )
	  {
	    $daten['WOCHE'][] = $Wochenzeile; 
  	    $daten['HINWEIS'][] = $Hinweise;
  	    $Wochenzeile = '';
  	    $Hinweise = '';
	  }	  	    	  	
    }
  
    if ( $ErstTurnus == '' && $Zusammenfassen ) 
	  $ErstTurnus = $Turnus;
  }	
}
mysql_free_result($query);

$daten['KLASSE'] = '';
$daten['HJ'] = 1;
$daten['SJ'] = $Schuljahr;
if ( isset($_REQUEST['Klasse'])) 
  $daten['KLASSE'] = $_REQUEST['Klasse'];
$daten['LEHRER'] = $_SERVER['REMOTE_USER'];  
$daten['TITEL'] = '';
if ( isset($_REQUEST['Was']) ) $daten['TITEL'] = $_REQUEST['Was'];
$doc->loadDocument('Vorlagen/Arbeitsplan.sxw');
$doc->insertStyles();
$doc->parse($daten);
$doc->download('Arbeitsplan');
$doc->clean();
}

?>