<?php
include_once("include/classes/orga/model/Schuljahr.class.php");
/**
 * Fehlzeiten
 * Zeigt die vorhandenen Fehlzeiten an und ermöglicht die einfache Korrektur
 * 
 * 17.03.06 C. Griep
 * 
 */
DEFINE('USE_KALENDER',1);
$Ueberschrift = 'Fehlzeiten anzeigen';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="https://lehrer.oszimt.de/css/Vertretung.css">';
include('include/header.inc.php');
include('include/turnus.inc.php');
include('include/stupla.inc.php');
include('include/Lehrer.class.php');

if ( (isset($_REQUEST['Art']) || isset($_REQUEST['NArt'])) &&
     isset($_REQUEST['Block']) && isset($_REQUEST['Datum']) )
{
  if ( is_numeric($_REQUEST['Datum']) &&
       is_numeric($_REQUEST['Block']) && is_numeric($_REQUEST['Schueler']) )
  {
    if ( isset($_REQUEST['Art']) && isset($_REQUEST['Fach']) &&
      $_REQUEST['Art'] != '' && $_REQUEST['Fach'] != '' )
    {
      // Neue Fehlzeit
      if ( ! mysql_query('INSERT INTO T_Fehlzeiten (Schueler_id, Art, Datum, Block, Kurs, Lehrer, Schuljahr) '.
        'VALUES ('.$_REQUEST['Schueler'].",'".$_REQUEST['Art']."', '".date('Y-m-d', $_REQUEST['Datum'])."', ".
        $_REQUEST['Block'].", '".$_REQUEST['Fach']."','".
        $_SERVER['REMOTE_USER']."','".$_REQUEST['Schuljahr']."')",$db))
           echo mysql_error();
    }
    elseif ( $_REQUEST['NArt'] != '' )
    {
      // Vorhandene Fehlzeit ändern
      if ( $_REQUEST['NArt'] == 'L' )
        mysql_query('DELETE FROM T_Fehlzeiten WHERE Schueler_id='.$_REQUEST['Schueler'].
          ' AND Block='.$_REQUEST['Block']." AND Datum='".date('Y-m-d',$_REQUEST['Datum'])."'");
      else
      {
        mysql_query("UPDATE T_Fehlzeiten SET Art='".$_REQUEST['NArt']."', Lehrer='".
          $_SERVER['REMOTE_USER']."' WHERE Schueler_id=".
          $_REQUEST['Schueler'].' AND Block='.$_REQUEST['Block']." AND Datum='".
          date('Y-m-d',$_REQUEST['Datum'])."'");
      }
    }
  }
}

function TagAnzeigen($Tagdaten, $Aufruf, $Datum, $SchuelerID, $Kurz)
{
  global $Wochentagnamen;
  $anzeigen = false;
  foreach ( $Tagdaten as $block => $Tag )
  {
      if ( isset($Tag['Art']) && $Tag['Art'] != '-' && $Tag['Art'] != '' )
        $anzeigen = true;
  }
  if ( $anzeigen && ! $Kurz )
  {
    echo '<tr><td class="home-content-titel" colspan="11">'.
      $Wochentagnamen[date('w',strtotime($Datum))].
      ', '.$Datum.
      "</td></tr>\n";
    for ( $block = 1; $block < 8; $block++ ) if ( isset($Tagdaten[$block]) )
    {
      $Pflicht = false;          
      $Tag = $Tagdaten[$block];
      if ( !isset($Tag['Art']) || $Tag['Art'] == '' )
        $Art = 'Art';
      else
        $Art = 'NArt';
      if ( ! isset($Tag['Name']) ) $Tag['Name'] = $Tag['Lehrer'];
      if ( ! isset($Tag['Fach']) ) $Tag['Fach'] = $Tag['Kurs'];
      echo '<tr ';
      if ( isset($Tag['Art']))
      {
        if ( $Tag['Art'] == 'V' )
          echo 'bgcolor="#AFAFAF"';
        elseif ( $Tag['Art'] == 'N' )
          echo 'bgcolor="#BF0000"';
        elseif ( $Tag['Art'] != '' && $Tag['Art'] != '-' )
          echo 'bgcolor="#FF9F00"';
      }
      echo '>';
      echo '<td>'.$Tag['Stunde'].'</td><td>'.$Tag['Fach'].'</td><td>'.$Tag['Name'];
      echo "</td><td>\n";
      if ( isset($Tag['Turnus']) && $Tag['Turnus'] != '' ) echo ' ('.$Tag['Turnus'].')';
      echo '</td><td>';
      // Attestpflicht: wird ignoriert!
      if (isset($Tag['Art']) && $Tag['Art'] == '!') 
      {
      	unset($Tag['Art']);
      	$Pflicht = true;
      }      
      if ( ! isset($Tag['Art']) || $Tag['Art'] == '' )
      {
        echo ' anwesend';
        if ( $Pflicht ) 
          echo ' <span style="background-color:red">Attestpflicht!</span>';
      }
      else
      {
        echo '<strong>';
        switch ( $Tag['Art'] )
        {
          case 'N': echo 'unentschuldigt';
	          break;
          case 'S': echo 'schul. Veran.';
                   break;
          case 'A': echo 'Attest';
         	 break;
          case 'K': echo 'Krank';
	          break;
          case 'P': echo 'Privat';
           	   break;
          case '-': echo 'entfallen';
                    break;
          case 'V': echo 'Verspätet';
                    break;
          default:
            echo $Tag['Art'].'!';
        }
        echo '</strong>';
      }     
      echo "</td><td>\n";
      if ( (!isset($Tag['Art']) || $Tag['Art'] != 'K') && ! $Pflicht )
      {
        echo '<td>'.$Aufruf.'Block='.$Tag['Stunde'];
        echo '&Datum='.strtotime($Datum).'&Schueler='.$SchuelerID;
        echo "&$Art=";
        echo 'K&Fach='.$Tag['Fach'];
        echo '#Sch'.$SchuelerID.'">Krank</a>';
      }
      echo "</td><td>\n";
      if ( !isset($Tag['Art']) || $Tag['Art'] != 'N' )
      {
        echo $Aufruf.'Block='.$Tag['Stunde'];
        echo '&Datum='.strtotime($Datum).'&Schueler='.$SchuelerID;
        echo "&$Art=";
        echo 'N&Fach='.$Tag["Fach"];
        echo '#Sch'.$SchuelerID.'">unentschuldigt</a>';
      }
      echo "</td><td>\n";
      if ( !isset($Tag["Art"]) || $Tag["Art"] != "P" )
      {
        echo $Aufruf.'Block='.$Tag["Stunde"];
        echo '&Datum='.strtotime($Datum).'&Schueler='.$SchuelerID;
        echo "&$Art=";
        echo 'P&Fach='.$Tag["Fach"];
        echo '#Sch'.$SchuelerID.'">privat</a>';
      }
      echo "</td><td>\n";
      if ( !isset($Tag["Art"]) || $Tag["Art"] != "S" )
      {
        echo $Aufruf.'Block='.$Tag["Stunde"];
        echo '&Datum='.strtotime($Datum).'&Schueler='.$SchuelerID;
        echo "&$Art=";
        echo 'S&Fach='.$Tag['Fach'];
        echo '#Sch'.$SchuelerID.'">schul. Verans.</a>';
      }
      echo "</td><td>\n";
      if ( isset($Tag["Art"]) && $Tag["Art"] != "" )
      {
        echo $Aufruf.'Block='.$Tag["Stunde"];
        echo '&Datum='.strtotime($Datum).'&Schueler='.$SchuelerID;
        echo "&$Art=";
        echo 'L&Fach='.$Tag["Fach"];
        echo '#Sch'.$SchuelerID.'">anwesend</a>';
      }
      echo '</td>';
      echo '</tr>';
      echo "</td></tr>\n";
    }
  }
}
  
function SchuelerAnzeigen($SchuelerID, $Schuljahr, $db, $StuPlaDB, $Kurz = false, $anzeigen= true)
{
  global $Wochentagnamen;
  $tabellenzeile = array();
  // Fehlzeiten anzeigen
  if ( ! $query = mysql_query("SELECT * FROM T_Schueler WHERE Nr = ".$SchuelerID,$db))
      echo mysql_error($db);
  if ( ! $Schueler = mysql_fetch_array($query) ) echo mysql_error ($db);
  mysql_free_result($query);
  $tabellenzeile['Name'] = $Schueler['Name'].', '.$Schueler['Vorname'];
  $tabellenzeile['Tutor'] = $Schueler['Tutor'];
  $tabellenzeile['Klasse'] = $Schueler['Klasse'];
  $Label = 'Sch'.$SchuelerID;
  if ( $anzeigen )
  {
    echo '<tr><th><a id="'.$Label.'" name="'.$Label.'"></a>';
    echo 'Fehlzeiten von '.$Schueler['Vorname'].' '.$Schueler['Name'].
      ' ('.$Schueler['Klasse'].', '.$Schueler['Tutor'].
      ") in Schuljahr $Schuljahr</th></tr>\n";
  }

  //Halbjahreshack
  $arrSchuljahr = Schuljahr::getSchuljahr('OG');
  if ( $arrSchuljahr['halbjahr'] == 2 )
    $sqlSchuljahrAdd = " OR Schuljahr='" . $arrSchuljahr['langform_I'] . "'";
     
  $sql_by_schuelerid = "SELECT * FROM T_Kurse " . 
	"WHERE Schueler_id = " . $SchuelerID .
	" AND (Schuljahr='$Schuljahr' $sqlSchuljahrAdd)";
  // echo $sql_by_schuelerid;
  if ( ! $query = mysql_query($sql_by_schuelerid,  $db))
    echo mysql_error($db);
  $Kurse = array();
  while ( $kurs = mysql_fetch_array($query) )
  {
    $Kurse[] = "'".$kurs["Kurs"]."'";
  }
  $Kurse = implode(',',$Kurse);
  mysql_free_result($query);
  if ( ! $query = mysql_query('SELECT * FROM T_Fehlzeiten WHERE Schueler_id = '.
    $SchuelerID.
    " AND Schuljahr = '$Schuljahr' ORDER BY Datum, Block",$db))
     echo mysql_error($db);
  $Fehltage = 0;
  $FehltageU = 0;
  $Fehlstunden = 0;
  $FehlstundenU = 0;
  $Verspaetungen = 0;
  $TagFehl = 0;
  $TagFehlU = 0;
  $Datum = '';
  $Aufruf = '';
  foreach ( $_REQUEST as $key => $value )
  {
    if ( $key != 'Art' && $key != 'NArt' && $key != 'Datum' && $key != 'Schueler' &&
         $key != 'Block' && $key != 'Fach' && $key != 'Schuljahr' )
       $Aufruf .= $key .'='.$value.'&';
  }
  $Aufruf .= 'Schuljahr='.$Schuljahr.'&';
  $Aufruf = '<a onClick="javascript:return window.confirm(\'Eintrag wirklich verändern?\');"'.
    '" href="'.$_SERVER['PHP_SELF'].'?'.$Aufruf;
  $Tag = array();
  if ( $anzeigen && ! $Kurz )
    echo '<tr><td><table class="Liste">';

  while ( $Fehlzeit = mysql_fetch_array($query) )
  {
    if ( $Datum != $Fehlzeit['Datum'] )
    {
      if ( $Datum != '' )
      {
        // Spätere Blöcke prüfen
        $sql = "SELECT Name,Stunde,Fach, Turnus FROM T_StuPla " .
	"WHERE Version = $Version AND Fach IN (" . $Kurse . ") " .
	"AND Stunde >= $Block " .
	"AND Wochentag = ".date('w',strtotime($Datum)) . 
	" ORDER BY Stunde";
	// echo $sql;
        if ( ! $pquery = mysql_query($sql,$db)) echo mysql_error($db);
        while ( $fach = mysql_fetch_array($pquery) )
        {
          if ( TurnusAktuell($fach['Turnus'], strtotime($Datum), $db))
          {
            if ( ! $Kurz && $anzeigen )
            {
              $Tag[$fach['Stunde']] = $fach;
            }
            $anwesend = true;
          }
        }
        mysql_free_result($pquery);
        // Auswertung des Tages !!!
        if ( $TagFehlU > 0 && ! $anwesend && $TagFehl == 0)
          $FehltageU++;
        else if ( $TagFehl > 0 && ! $anwesend && $TagFehlU == 0)
          $Fehltage++;
        else
        {
          $FehlstundenU += $TagFehlU;
          $Fehlstunden += $TagFehl;
        }
      }      
      if ( $anzeigen ) 
        TagAnzeigen($Tag, $Aufruf, $Datum, $SchuelerID, $Kurz);
      $Tag = array();
      $Block = 1;
      $anwesend = false;
      /*
      if ( ! $Kurz && $anzeigen )
        echo '<tr><td class="home-content-titel">'.$Wochentagnamen[date("w",strtotime($Fehlzeit["Datum"]))].
          ", ".$Fehlzeit['Datum'].
          '</td></tr>';
      */
      $Datum = $Fehlzeit['Datum'];
      $Version = getAktuelleVersion($Datum);
      $TagFehl = 0;
      $TagFehlU = 0;
    }
    if ( $Fehlzeit['Block'] > $Block )
    {
      $pquery = mysql_query("SELECT Name, Stunde, Fach, Turnus FROM T_StuPla WHERE Version = $Version AND Fach IN (".$Kurse.
        ') AND Stunde < '.$Fehlzeit['Block']." AND Stunde >= $Block AND Wochentag = ".date('w',strtotime($Datum)).
          ' ORDER BY Stunde',$db);
      while ( $fach = mysql_fetch_array($pquery) )
      {
        if ( TurnusAktuell($fach['Turnus'], strtotime($Datum), $db) )
        {
          $Tag[$fach['Stunde']] = $fach;
          $anwesend = true;
        }
      }
      mysql_free_result($pquery);
    }
    $Tag[$Fehlzeit['Block']] = $Fehlzeit;
    $Tag[$Fehlzeit['Block']]['Stunde'] = $Fehlzeit['Block'];
    if ( $Fehlzeit['Art'] == 'V' )
    {
      $anwesend = true;
      $Verspaetungen++;
    }
    if ( $Fehlzeit['Art'] == 'S' )
    {
      $anwesend = true;
    }
    if ( $Fehlzeit['Art'] == 'A' || $Fehlzeit['Art'] == 'K' || $Fehlzeit['Art'] == 'P' )
    {
        $TagFehl++;
    }
    if ( $Fehlzeit['Art'] == 'N' )
    {
        $TagFehlU++;
    }
    $Block = $Fehlzeit['Block']+1;
  }
  if ( $Datum != '' )
  {
    // Spätere Blöcke des letzten Tages prüfen
    $sql = "SELECT Name, Stunde, Fach, Turnus FROM T_StuPla " .
    		"WHERE Version = $Version AND Fach IN (".$Kurse.
      ") AND Stunde >= $Block AND Wochentag = ".date("w",strtotime($Datum)).
      ' ORDER BY Stunde';
    if ( ! $pquery = mysql_query($sql,$db))
        echo mysql_error($db);
    while ( $fach = mysql_fetch_array($pquery) )
    {
      if ( TurnusAktuell($fach['Turnus'], strtotime($Datum), $db) )
      {
        $Tagdaten[$fach['Stunde']] = $fach;
        $anwesend = true;
      }
    }
    mysql_free_result($pquery);
  }
  mysql_free_result($query);
  // Auswertung des Tages !!!
  if ( $TagFehlU > 0 && ! $anwesend && $TagFehl == 0)
    $FehltageU++;
  else if ( $TagFehl > 0 && ! $anwesend && $TagFehlU == 0)
    $Fehltage++;
  else
  {
    $FehlstundenU += $TagFehlU;
    $Fehlstunden += $TagFehl;
  }
  if ( $anzeigen )
  {
  	TagAnzeigen($Tag, $Aufruf, $Datum, $SchuelerID, $Kurz);
    if ( ! $Kurz )
      echo '</table></td></tr>';
  }
  // Wir rechnen in Blöcken, brauchen aber Stunden! 
  $FehlstundenU = $FehlstundenU *2;
  $Fehlstunden = $Fehlstunden*2;
  if ( $anzeigen )
  {
    echo '<tr><td><hr /></td></tr>';
    echo '<tr><th>Zusammenfassung</th></tr>';
    echo '<tr><td>Fehlstunden: '.(($Fehlstunden+$FehlstundenU)).' davon unentschuldigt '.($FehlstundenU).'</td></tr>';
    echo '<tr><td>Fehltage: '.($Fehltage+$FehltageU)." davon unentschuldigt $FehltageU</td></tr>";
    echo '<tr><td>Verspätungen: '.($Verspaetungen).'</td></tr>';
  }
  $tabellenzeile['FS'] = ($Fehlstunden+$FehlstundenU);
  $tabellenzeile['FSU'] = $FehlstundenU;
  $tabellenzeile['FT'] = ($Fehltage+$FehltageU);
  $tabellenzeile['FTU'] = $FehltageU;
  $tabellenzeile['V'] = $Verspaetungen;
  if ( ! $Kurz && $anzeigen )
  {
    if ( ! $query = mysql_query('SELECT T_Kurse.Kurs, Bearbeitetbis, Lehrer ' .
    		'FROM T_Kurse LEFT JOIN T_Fehlzeitenstand ON '.
      'T_Kurse.Kurs = T_Fehlzeitenstand.Kurs WHERE Schueler_id = '.$SchuelerID.
        " AND Schuljahr='$Schuljahr' ORDER BY Bearbeitetbis",$db)) echo mysql_error();
    echo '<tr><td><em>Aktualität: Fehlzeiten bearbeitet...</em></td></tr>';
    while ( $was = mysql_fetch_row($query) )
    {
      echo '<tr><td>';
      echo $was[0]." (";
      if ( $was[2] != "" )
        echo $was[2].") bis ".date("d.m.Y",$was[1]);
      else
        echo "bisher nicht bearbeitet)";
      echo '</td></tr>';
    }
    mysql_free_result($query);
  }
  if ( $anzeigen ) echo "<tr><td><hr /></td></tr>";
  return $tabellenzeile;
} // function SchuelerAnzeigen

if ( isset($_REQUEST['Schueler_id']) && is_numeric($_REQUEST['Schueler_id']) )
{
  if ( ! isset($_REQUEST['Schuljahr']) )
  {
    $arrSchuljahr = Schuljahr::getSchuljahr('OG');
    $strSchuljahr = $arrSchuljahr['langform'];
    $_REQUEST['Schuljahr'] = $strSchuljahr;
  }
  SchuelerAnzeigen($_REQUEST['Schueler_id'], $_REQUEST['Schuljahr'], $db, $db);
}
if ( isset($_REQUEST['Lehrer']) )
{
  $arrSchuljahr = Schuljahr::getSchuljahr('OG');
  $Schuljahr = $arrSchuljahr['langform'];
  
  if ( ! $query = mysql_query("SELECT Nr FROM T_Schueler WHERE Tutor='".$_REQUEST['Lehrer'].
    "' AND Klasse LIKE 'OG%'",$db))
    echo mysql_error($db);
  if ( isset($_REQUEST['Kurz']) )
    $Kurz = true;
  else
    $Kurz = false;
  while ( $schueler = mysql_fetch_row($query) )
    SchuelerAnzeigen($schueler[0], $Schuljahr, $db, $db, $Kurz);
  mysql_free_result($query);
}
if ( isset($_REQUEST['Klassenstufe']) )
{
  if ( isset($_REQUEST['NeuRechnen']))
  {
    session_unset();
  }
  echo '<tr><td>';
  if ( $_REQUEST['Schuljahr'] == '' )
  {
    $arrSchuljahr = Schuljahr::getSchuljahr('OG');
    $Schuljahr = $arrSchuljahr['langform'];
  }
  else
    $Schuljahr = $_REQUEST['Schuljahr'];
  //if ( session_is_registered('SchuelerNummer'))  --> MOD_OSZ: Binz, 21.08.2014 (wird von akt. PHP-Version nicht unterstuetzt)
  if ( isset($_SESSION['SchuelerNummer']) )
    $Anfang = $_SESSION['SchuelerNummer'];
  else
    $Anfang = '';
  if ( ! is_numeric($Anfang) || $_SESSION['Schuljahr'] != $Schuljahr ||
       $_SESSION['Klassenstufe'] != $_REQUEST['Klassenstufe'] )
  {
    $Anfang = 0;
    $_SESSION['Klassenstufe'] = $_REQUEST['Klassenstufe'];
    $_SESSION['Schuljahr'] = $Schuljahr;
    $_SESSION['fs'] = 0;
    $_SESSION['fsu'] = 0;
    $_SESSION['ft'] = 0;
    $_SESSION['ftu'] = 0;
    $_SESSION['v'] = 0;
    $_SESSION['FKeine'] = 0;
    $_SESSION['F1bis10'] = 0;
    $_SESSION['F11bis20'] = 0;
    $_SESSION['F21bis40'] = 0;
    $_SESSION['Fmehr40'] = 0;
    $_SESSION['F1bis10u'] = 0;
    $_SESSION['F11bis20u'] = 0;
    $_SESSION['F21bis40u'] = 0;
    $_SESSION['Fmehr40u'] = 0;
  }
  if ( ! $query = mysql_query('SELECT Nr FROM T_Schueler WHERE Klasse="'.
    mysql_real_escape_string($_REQUEST['Klassenstufe']).'" ORDER BY Name, Vorname ' .
    		'LIMIT '.$Anfang.',10',$db))
    echo mysql_error($db);
  if ( mysql_num_rows($query) < 10 )
  {
    $fertig = true; 
    //session_unregister('SchuelerNummer');  --> MOD_OSZ: Binz, 21.08.2014 (wird von akt. PHP-Version nicht unterstuetzt)
    unset($_SESSION['SchuelerNummer']);    
  }
  else
  {
    $fertig = false;
    $_SESSION['SchuelerNummer'] = $Anfang+10;
  }
  echo '<h2>Klassenstufe '.$_REQUEST['Klassenstufe']." in Schuljahr $Schuljahr</h2>";
  echo '<table border="1" style="text-align:center">';
  echo '<tr><th>Schüler/in</th><th>Tutor</th><th>Fehltage</th><th>davon unent.</th>';
  echo '<th>Fehlstunden</th><th>davon unent.</th><th>Verspätungen</th></tr>';
  //if ( session_is_registered('Zeilen') && is_array($_SESSION['Zeilen']) )  --> MOD_OSZ: Binz, 21.08.2014 (wird von akt. PHP-Version nicht unterstuetzt)
  if ( isset($_SESSION['Zeilen']) && is_array($_SESSION['Zeilen']) )
    foreach ( $_SESSION['Zeilen'] as $key => $value )
      echo $value;
  while ( $schueler = mysql_fetch_row($query) )
  {
    $tabelle = SchuelerAnzeigen($schueler[0], $Schuljahr, $db, $db, false, false);
    $s = '<tr><td>'.$tabelle['Name'].'</td><td>'.$tabelle['Tutor'].'</td><td>'.$tabelle['FT'].'</td>';
    $s .= '<td>'.$tabelle['FTU'].'</td><td>'.$tabelle['FS'].'</td><td>'.$tabelle['FSU'].'</td>';
    $s .= '<td>'.$tabelle['V'].'</td></tr>';
    echo $s;
    $_SESSION['Zeilen'][] = $s;
    $_SESSION['v'] = $_SESSION['v'] + $tabelle['V'];
    $_SESSION['fs'] = $_SESSION['fs'] + $tabelle['FS'];
    $_SESSION['fsu'] = $_SESSION['fsu'] + $tabelle['FSU'];
    $_SESSION['ft'] = $_SESSION['ft'] + $tabelle['FT'];
    if ( $tabelle['FT'] == 0 ) $_SESSION['FKeine']++;
    else if ( $tabelle['FT'] < 11 ) $_SESSION['F1bis10']++;
    else if ( $tabelle['FT'] < 21 ) $_SESSION['F11bis20']++;
    else if ( $tabelle['FT'] < 41 ) $_SESSION['F21bis40']++;
    else $_SESSION['Fmehr40']++;
    $_SESSION['ftu'] = $_SESSION['ftu'] + $tabelle['FTU'];
    if ( $tabelle['FTU'] == 0 )
    {
    }
    else if ( $tabelle['FTU'] < 11 ) $_SESSION['F1bis10u']++;
    else if ( $tabelle['FTU'] < 21 ) $_SESSION['F11bis20u']++;
    else if ( $tabelle['FTU'] < 41 ) $_SESSION['F21bis40u']++;
    else $_SESSION['Fmehr40u']++;
    flush(); // Zeile direkt ausgeben
  }
  mysql_free_result($query);
  if ( ! $fertig )
    echo '<a href="'.$_SERVER['PHP_SELF'].'?Schuljahr='.$Schuljahr.'&Klassenstufe='.
      $_REQUEST["Klassenstufe"].'">Hier klicken zum Weiterrechnen ...</a>';
  else
  {
    echo '<tr><td><i>Gesamt</i></td><td></td><td>'.$_SESSION["ft"].'</td><td>'.$_SESSION["ftu"].'</td>';
    echo '<td>'.$_SESSION["fs"].'</td><td>'.$_SESSION["fsu"].'</td><td>'.$_SESSION["v"].'</td></tr>';
  }
  echo '</table>';
  if ( $fertig )
  {
    echo '<table>';
    echo '<tr><th colspan="3">Fehltage-Statistik</th></tr>';
    echo '<tr><td></td><td>Anzahl Schüler</td><td>unentschuldigt/Schüler</td></tr>';
    echo "<tr><td>keine Fehltage</td><td>".$_SESSION["FKeine"]."</td><td></td></tr>";
    echo "<tr><td>1 bis 10 Fehltage</td><td>".$_SESSION["F1bis10"].
      "</td><td>".$_SESSION["F1bis10u"]."</td></tr>";
    echo "<tr><td>11 bis 20 Fehltage</td><td>".$_SESSION["F11bis20"].
      "</td><td>".$_SESSION["F11bis20u"]."</td></tr>";
    echo "<tr><td>21 bis 40 Fehltage</td><td>".$_SESSION["F21bis40"]."</td><td>".
      $_SESSION["F21bis40u"]."</td></tr>";
    echo "<tr><td>mehr als 40 Fehltage</td><td>".$_SESSION["Fmehr40"]."</td><td>".
      $_SESSION["Fmehr40u"]."</td></tr>";
    echo "</table>\n";
  }
  echo '</td></tr>';
}
?>

<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
<tr>
 <td>Fehlzeiten von Schüler/in <select name="Schueler_id">
 <?php
if ( ! $query = mysql_query("SELECT DISTINCT Nr, Name, Vorname, Klasse FROM T_Schueler INNER JOIN T_Kurse ".
     "ON T_Schueler.Nr = T_Kurse.Schueler_id WHERE Klasse LIKE 'OG _' ORDER BY Name, Vorname", $db))
       echo mysql_error($db);
while ($s = mysql_fetch_array($query) )
{
  echo '<option value="'.$s["Nr"].'">'.$s["Name"].", ".$s["Vorname"]." (".$s["Klasse"].')</option>';
}
mysql_free_result($query);
echo '</select> ';
echo ' Schuljahr ';
$arrSchuljahr = Schuljahr::getSchuljahr('OG');
$Schuljahr = $arrSchuljahr['langform'];
echo '<select name="Schuljahr">';
$query = mysql_query("SELECT DISTINCT Schuljahr FROM T_Kurse ORDER BY Schuljahr", $db);
 while ( $fach = mysql_fetch_row($query) )
 {
   echo '<option';
   if ( ! ( strpos($fach[0], $Schuljahr) === false) )
     echo ' selected="selected"';
   echo '>'.$fach[0].'</option>';
 }
 echo '</select>';
?>
<input type="Submit" value="anzeigen"><br />
In der Liste Anfangsbuchstaben eingeben, um hinzuspringen
</td></tr>
</form>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
<tr>
 <td>Tutanden von Tutor/in <select name="Lehrer">
 <?php
if ( ! $query = mysql_query("SELECT DISTINCT Tutor FROM T_Schueler WHERE Klasse LIKE 'OG _' ORDER BY Tutor", $db))
       echo mysql_error($db);
while ($s = mysql_fetch_array($query) )
{
  if ( trim($s['Tutor']) != '' )
  {
    echo '<option value="'.$s['Tutor'].'" ';
    if ( ! (strpos($_SERVER['REMOTE_USER'], $s['Tutor']) === false ) )
      echo 'selected="selected"';
    echo '>';
    $Lehrer = new Lehrer($s['Tutor'], LEHRERID_KUERZEL);
    echo $Lehrer->Name;
    if ( $Lehrer->Vorname != '' ) echo ', '.$Lehrer->Vorname;
    echo '</option>';
  }
}
mysql_free_result($query);
echo '</select> ';
echo '<input type="Checkbox" name="Kurz" value="v"> Kurzform';
?>
<input type="Submit" value="anzeigen"><br />
In der Liste Anfangsbuchstaben eingeben, um hinzuspringen
</td></tr>
</form>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
<tr>
 <td>Schülerübersichtsliste von Klassenstufe <select name="Klassenstufe">
 <?php
if ( ! $query = mysql_query("SELECT DISTINCT Klasse FROM T_Schueler WHERE Klasse LIKE 'OG _' ORDER BY Klasse", $db))
       echo mysql_error($db);
while ($s = mysql_fetch_array($query) )
{
  echo '<option>'.$s["Klasse"].'</option>';
}
mysql_free_result($query);
echo '</select> ';
echo ' Schuljahr ';
$arrSchuljahr = Schuljahr::getSchuljahr('OG');
$Schuljahr = $arrSchuljahr['langform'];
echo '<select name="Schuljahr">';
$query = mysql_query("SELECT DISTINCT Schuljahr FROM T_Kurse ORDER BY Schuljahr", $db);
 while ( $fach = mysql_fetch_row($query) )
 {
   echo '<option';
   if ( ! ( strpos($fach[0], $Schuljahr) === false) )
     echo ' selected="selected"';
   echo '>'.$fach[0].'</option>';
 }
 echo '</select>';
?>
Achtung: Berechnung dauert lange!
<input type="Submit" value="anzeigen" name="NeuRechnen"/>
</td></tr>
</form>
<?php
 include("include/footer.inc.php");
?>
