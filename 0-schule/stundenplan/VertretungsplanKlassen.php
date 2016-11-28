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
 
if ( date('H') > 6 && date('H') < 8 ) 
  $Zeit = 5*60;
else
  $Zeit = 15*60;
  
$Datum = strtotime(date('Y-m-d')); // heutiges Datum
$Param = '';
do {
  
  if ( date('w',$Datum) == 0 )
    $Datum = strtotime('+1 day', $Datum);
  if ( date('w', $Datum) == 6 )
    $Datum = strtotime('+2 day', $Datum);
 
  while ( istFrei($Datum) )
  {
    $Datum = strtotime('+1 day', $Datum);
    if ( date('w',$Datum) == 0 )
      $Datum = strtotime('+1 day', $Datum);
    if ( date('w',$Datum) == 6 )
      $Datum = strtotime('+2 day', $Datum);  
  }
  if ( isset($_REQUEST['Tag']) && is_numeric($_REQUEST['Tag']))
  {
    $Datum = strtotime('+'.$_REQUEST['Tag'].' day', $Datum);
    $Param = '?Tag='.$_REQUEST['Tag'];
    unset($_REQUEST['Tag']);
    $Nochmal = true;
  }
  else
    $Nochmal = false;
}
while ($Nochmal);  
  $Wochentagnamen[0] = 'Sonntag';
  $Wochentagnamen[1] = 'Montag';
  $Wochentagnamen[2] = 'Dienstag';
  $Wochentagnamen[3] = 'Mittwoch';
  $Wochentagnamen[4] = 'Donnerstag';
  $Wochentagnamen[5] = 'Freitag';
  $Wochentagnamen[6] = 'Samstag';
  
$Ueberschrift = 'Planänderungen für '.$Wochentagnamen[date('w',$Datum)].', den '.
  date('d.m.Y',$Datum).', Stand '.date('d.m.Y H:i');
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">' .
		'<meta http-equiv="refresh" content="'.$Zeit.'; URL='.$_SERVER['PHP_SELF'].$Param.'">';
include('include/header.inc.php');
echo '<tr><td>';

include('include/helper.inc.php');
include('include/Lehrer.class.php');
include('include/Vertretungen.inc.php');

echo '<style type="text/css">
<!--
.KlassenAenderungen {  
  display: block;
  float: left;
  
  width: 50%; /* Breite.*/
}
.KlassenAenderungen table.Liste {
	width: 99%;
	font-size: 8pt;
}
.KlassenAenderungen table.Liste td {
  font-size: 8pt;	
}	
.ueberschrift {
  font-size: 10pt;
}
-->
</style>';

// Alle Vertretungen von einem Tag anzeigen
if (! $query = mysql_query("SELECT * FROM T_Vertretungen WHERE Datum=$Datum " .
		"ORDER BY Stunde, Klasse, Klasse_Neu")) 
  echo mysql_error();
$Eintraege = array();
while ( $row = mysql_fetch_array($query))
{
	if ( $row['Klasse'] != '')
	  $Eintraege[$row['Klasse']][] = $row;
	elseif ( $row['Klasse_Neu'] != '')
      $Eintraege[$row['Klasse_Neu']][] = $row;	 
}  

$Klasse = '';
$Anz=0;
foreach ($Eintraege as $dieKlasse => $Werte)
foreach ($Werte as $row)
{
  if ( $Klasse != $row['Klasse'])
  {    
    if ( $Klasse != '' ) 
    { 
    	echo '</table></div>';
    	if ( ($Anz % 2) == 0 )
    	  echo '<br style="clear: left;">';
    }
    $Anz++;
    echo ' <div class="KlassenAenderungen">';
    echo '<table class="Liste">';
    echo '<tr><th colspan="6"><span class="ueberschrift">Klasse ';
    echo $row['Klasse'];
    echo '</span>';          
    echo "</th></tr>\n";
    echo '<tr><th width="6%">Block</th><th width="10%">Fach</th>' .
    		'<th width="15%">Lehrer</th><th width="13%">Lehrer neu';
    echo '</th><th width="8%">Raum</th><th>Bemerkung</th>';
    echo "</tr>\n";
    $Klasse = $row['Klasse'];
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
  $Lehrer = new Lehrer($row['Lehrer'],LEHRERID_KUERZEL); 
  echo '<td>'.$Lehrer->Name."</td>\n";
  echo '<td>';
  $Lehrer = new Lehrer($row['Lehrer_Neu'],LEHRERID_KUERZEL); 
  if ( $row['Lehrer_Neu'] != $row['Lehrer'] )
  echo $Lehrer->Name;
  echo "</td>\n";
  echo '<td>';
  if ( $row['Raum_Neu'] != $row['Raum'] && $row['Raum_Neu'] != '')
    echo $row['Raum'].' &rarr;';
  echo "{$row['Raum_Neu']}</td>\n";
  echo' <td>';
  $s = '';
  if ( $row['Raum_Neu'] == '' &&
       $row['Klasse_Neu'] == '' &&
       $row['Lehrer_Neu'] == '' &&
       $row['Fach_Neu'] == '' )
     $s = 'entfällt. ';
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
              $s .= "neben regulärem Unterricht von ".$Lehrer->Name."\n";
          }
          if ( $row['Fach'] != '' && $row['Fach'] != $row['Fach_Neu'] )
            $s .= 'Unterrichtsänderung: '.$row['Fach_Neu']."\n";
          if ( $row['Fach'] == '' && $row['Fach'] != $row['Fach_Neu'] )
            $s .= 'Zusätzlicher Block: '.$row['Fach_Neu']."\n";
  }
  if ($row['Bemerkung']!= '' )
          $s .= trim($row['Bemerkung']);
  echo nl2br(trim($s));
  echo "</td>\n";
  echo "</tr>\n";
}
if ( mysql_num_rows($query) == 0)
  echo '<div class="Hinweis"> - keine Änderungen vorhanden -</div>';
else
  echo '</table></div>';
mysql_free_result($query);

echo '</td></tr>';
echo '<script language="javascript">
function NachUnten () {
  var y = 0;
  if (window.pageYOffset) {
    y = window.pageYOffset;
  } else if (document.body && document.body.scrollTop) {
    y = document.body.scrollTop;
  }
  yalt = y;
  window.scrollBy(0, 10);
  setTimeout("NachUnten()", 1000);
  if (window.pageYOffset) {
    y = window.pageYOffset;
  } else if (document.body && document.body.scrollTop) {
    y = document.body.scrollTop;
  }
  if ( yalt == y )
  {
    window.scrollTo(0,0);
    setTimeout("NachUnten()", 1000);
  }

}
setTimeout("NachUnten();",300);		
</script>';
$_REQUEST['Print']= 1;
include('include/footer.inc.php');
?>