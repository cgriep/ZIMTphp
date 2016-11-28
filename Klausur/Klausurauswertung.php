<?php
/**
 *  Klausurauswertung
 *  Wertet die vorhandenen Klausuren aus.
 *  Möglichkeiten zur Gruppierung nach Klassen, Ausbildungsgängen,
 *  Abteilungen und Fächern.
 * 
 *  14.03.06 C. Griep
 *  
 */
 $Ueberschrift = 'Klausurergebnisse auswerten';
 $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
  include('include/header.inc.php');
 include('include/Klausur.inc.php');
 include('include/Abteilungen.class.php');
 include('include/turnus.inc.php');
 $dieAbteilungen = new Abteilungen($db);

 echo '<tr><td>';
 if ( ! isset($_REQUEST['Print']))
 {
   echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
   echo 'Schuljahr ';
   echo '<select name="Schuljahr">';
   $query = mysql_query('SELECT Schuljahr FROM T_Klausurergebnisse ' .
   		'GROUP BY Schuljahr ORDER BY Datum');
   if ( isset($_REQUEST['Schuljahr']))
     $_SESSION['Schuljahr'] = $_REQUEST['Schuljahr'];
   elseif ( ! session_is_registered('Schuljahr'))
     $_SESSION['Schuljahr'] = Schuljahr(false);
   $da = false;
   while ( $Schuljahr = mysql_fetch_row($query) )
   {
     echo '<option ';
     if ( $_SESSION['Schuljahr'] == $Schuljahr[0] ) 
     {
       echo 'selected="selected"';
       $da = true;
     }
     echo '>'.$Schuljahr[0]."</option>\n";
   }
   echo '<option value="" ';
   if ( !$da ) echo 'selected="selected"';
   echo '>--alle--';
   echo '</option>';
   mysql_free_result($query);
   echo '</select>';
   echo ' <input type="Submit" value="auswählen">';
   echo "</form>\n";
 }
 
 if ( isset($_REQUEST['Klausur_id']) && is_numeric($_REQUEST['Klausur_id']) )
 {
   $query = mysql_query('SELECT * FROM T_Klausurergebnisse ' .
   		'WHERE Klausur_id = '.$_REQUEST['Klausur_id']);
   $Klausur = mysql_fetch_array($query);
   mysql_free_result($query);
 }

$Auswahl = 'WHERE ';
$Ueberschrift = '';
$Param = '';

if ( isset($_REQUEST['Fach']) )
{
  $_REQUEST['Fach'] = mysql_real_escape_string($_REQUEST['Fach']);
  $Auswahl .="Fach LIKE '".$_REQUEST['Fach']."' COLLATE 'latin1_german1_ci' AND ";
  $Param .= 'Fach='.$_REQUEST['Fach'].'&';
  $Ueberschrift = 'Fach '.$_REQUEST['Fach'].' ';
}
if ( isset($_REQUEST['Abteilung']) && is_numeric($_REQUEST['Abteilung']))
{
  $Auswahl .='Abteilung='.$_REQUEST['Abteilung'].' AND ';
  $Param .= 'Abteilung='.$_REQUEST['Abteilung'].'&';
  $Ueberschrift .= $dieAbteilungen->getAbteilung($_REQUEST['Abteilung']).' ';
}
if ( isset($_REQUEST['Klasse']) )
{
  $_REQUEST['Klasse'] = mysql_real_escape_string($_REQUEST['Klasse']);
  $Auswahl .="Klasse LIKE '".$_REQUEST['Klasse']."' AND ";
  $Param .= 'Klasse='.$_REQUEST['Klasse'].'&';
  $Ueberschrift .= ' Klasse '.$_REQUEST['Klasse'].' ';
}
$Auswahl .= '1';
if ( session_is_registered('Schuljahr') && 
     $_SESSION['Schuljahr'] != '' )
{
     $Auswahl .= " AND Schuljahr='{$_SESSION['Schuljahr']}' ";
     $Ueberschrift .= ' Schuljahr '.$_SESSION['Schuljahr'];
}
if ( $Auswahl != 'WHERE 1' ) {
  echo '<table class="Liste">';
  echo '<tr><td class="Zwischenueberschrift" colspan="6">Durchschnittsergebnisse für '.
    str_replace('%','',$Ueberschrift).'</td></tr>';
  echo '<tr><th>Klasse</th><th>Fach</th><th>Lehrkraft</th><th align="center">Datum</th>' .
  		'<th>Schnitt</th><th>Teilnehmer</th></tr>';
  $query = mysql_query("SELECT * FROM T_Klausurergebnisse $Auswahl ORDER BY Datum DESC");
  $Gesamt = 0;
  $Anzahl = 0;
  $GesamtSchueler = 0;
  while ( $Klausur = mysql_fetch_array($query) )
  {
    echo '<tr><td>'.$Klausur['Klasse'].'</td><td>'.$Klausur['Fach'].
      '</td><td>'.$Klausur['Lehrer'].
      '</td><td align="center">'.$Klausur['Datum'].'</td><td align="right">'.
      Durchschnitt($Klausur).'</td><td align="center">'.Teilnehmeranzahl($Klausur).'</td></tr>';
    $Gesamt += Durchschnitt($Klausur);
    $Anzahl += 1;
    $GesamtSchueler += Teilnehmeranzahl($Klausur);
  }
  mysql_free_result($query);
  echo '<tr><td colspan="4"><strong>Gesamtdurchschnitt</strong></td>' .
  		'<td align="right"><strong>';
  if ( $Anzahl != 0 )
    echo number_format($Gesamt/$Anzahl,2);
  else
    echo "n/a";
  echo '</strong></td><td></td></tr>';
  echo '<tr><td colspan="3">Gesamtanzahl Klausuren</td><td align="center">'.$Anzahl.
    "</td><td></td><td></td></tr>\n";
  echo '<tr><td colspan="5">Gesamtanzahl Teilnehmer</td><td align="center">'.
    $GesamtSchueler."</td></tr>\n";
  echo '</table>';
  echo "<br />\n";
  if ( $Anzahl > 1 ) {
    echo '<a href="Grafik.php?'.$Param.'x=600&y=400">';
    echo '<img src="Grafik.php?'.$Param.'" border="0"/></a>';
  }
}


 echo '</td></tr>';
 echo '<tr><td><hr /></td></tr>';
 // Fächer, Klassen usw. auswählen
 echo '<tr><td>';
 echo '<table width="75%">';
 echo '<tr><th colspan="8">Vorhandene Ergebnisse</th></tr>';
 foreach ( $dieAbteilungen->Abteilungen as $key => $Abt )
 {
   echo '<tr><th colspan="8" align="center"><a href="Klausurauswertung.php?Abteilung='.$key.'">'.
     $Abt['Abteilung'].'</a></a></th></tr>';
   echo '<tr><td colspan="8" align="center">nach Fächern zusammengefasst</td></tr>';
   $query = mysql_query("SELECT DISTINCT Fach FROM T_Klausurergebnisse WHERE Abteilung = '".
     $key."' ORDER BY Fach");
   $nr = 0;
   while ( $fach = mysql_fetch_row($query))
   {
     if ( ($nr % 8) == 0 ) echo '<tr>';
     echo '<td><a href="Klausurauswertung.php?Abteilung='.$key.'&Fach='.$fach[0].
       '">'.$fach[0].'</a></td>';
     $nr++;
     if ( ($nr % 8) == 0 ) echo '</tr>'."\n";
   }
   if ( $nr % 8 != 0 ) echo '</tr>';
   if ( $key == 4 )
   {
     echo '<tr><td colspan="8" align="center">Kurse zusammengefasst nach Fachart</td></tr>';
     // Sonderfall Abteilung 4: Kurse
     $query = mysql_query('SELECT DISTINCT Fach FROM T_Faecher ' .
     		'WHERE Art LIKE "OG _" ORDER BY Fach');
     $nr = 0;
     while ( $fach = mysql_fetch_array($query) )
     {
       if ( ($nr % 8) == 0 ) echo '<tr>';
       echo '<td><a href="Klausurauswertung.php?Abteilung='.$key.'&Fach='.$fach['Fach'].
         '%">'.$fach['Fach'].'</a></td>';
       $nr++;
       if ( ($nr % 8) == 0 ) echo '</tr>'."\n";
     }
     if ( $nr % 8 != 0 ) echo '</tr>';
     mysql_free_result($query);
   }
   $nr = 0;
   $Klassenart = array();
   echo '<tr><td colspan="8" align="center">nach Klassen zusammengefasst</td></tr>';
   $query = mysql_query("SELECT DISTINCT Klasse FROM T_Klausurergebnisse WHERE Abteilung = '".
     mysql_real_escape_string($key)."' ORDER BY Klasse");
   while ( $fach = mysql_fetch_row($query))
   {
     if ( ($nr % 8) == 0 ) echo '<tr>';
     echo '<td><a href="Klausurauswertung.php?Abteilung='.$key.'&Klasse='.$fach[0].
       '">'.$fach[0].'</a></td>';
     $nr++;
     list($Art, $nummer) = explode(' ',$fach[0]);
     $Klassenart[$Art] = 1;
     if ( ($nr % 8) == 0 ) echo '</tr>'."\n";
   }
   if ( $nr % 8 != 0 ) echo '</tr>';
   echo '</td></tr>';
   $nr = 0;
   echo '<tr><td colspan="8" align="center">nach Klassenarten zusammengefasst</td></tr>';
   foreach ( $Klassenart as $Art => $k)
   {
     if ( ($nr % 8) == 0 ) echo '<tr>';
     echo '<td><a href="Klausurauswertung.php?Abteilung='.$key.'&Klasse='.$Art.
       '%">'.$Art.'</a></td>';
     $nr++;
     if ( ($nr % 8) == 0 ) echo '</tr>'."\n";
   }
   if ( $nr % 8 != 0 ) echo '</tr>';
   echo '</td></tr>';
 }
 echo '<tr><td colspan="8"><hr /></td></tr>';
 echo '<tr><td colspan="8" align="center">Schulweit nach Fächern zusammengefasst</td></tr>';
 $query = mysql_query("SELECT DISTINCT Fach FROM T_Faecher ORDER BY Fach");
 $nr = 0;
 while ( $fach = mysql_fetch_array($query) )
 {
   if ( ($nr % 8) == 0 ) echo '<tr>';
   echo '<td><a href="Klausurauswertung.php?Fach='.$fach[0].
     '%">'.$fach[0].'</a></td>';
   $nr++;
   if ( ($nr % 8) == 0 ) echo '</tr>'."\n";
 }
 if ( $nr % 8 != 0 ) echo '</tr>';
 mysql_free_result($query);
 echo '</table>';
 include('include/footer.inc.php');
?>