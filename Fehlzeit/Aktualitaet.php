<?php
include_once("include/classes/orga/model/Schuljahr.class.php");
/*
 * Aktualitaet.php
 * Zeigt an, wann die Fehlzeiten der einzelnen Kurse zuletzt bearbeitet worden
 * sind.
 * Es wird immer das aktuelle Schuljahr angezeigt.
 * 
 * Letzte Änderung:
 * 09.01.06 C. Griep
 * 17.03.06 C. Griep - BmA-Kurse werden nicht mehr angezeigt
 *
 * Ist der Parameter Mail gesetzt, wird bei zu lang zurückliegenden Einträgen
 * (mehr als 1 Monat und 10 Tage) eine Erinnerungsmail verschickt. 
 */
$Ueberschrift = 'Fehlzeitenaktualität anzeigen';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="https://lehrer.oszimt.de/css/oszimt.css">';
include('include/header.inc.php');
include('include/turnus.inc.php');
include('include/stupla.inc.php');
include('include/Lehrer.class.php');

echo '<tr><td>';

$arrSchuljahr = Schuljahr::getSchuljahr('OG');
$Schuljahr = $arrSchuljahr['langform'];

echo '<h2>Schuljahr '.$Schuljahr.'<h2>';
echo '</td></tr>';
$sql = 'SELECT DISTINCT T_Kurse.Kurs AS Kurs, Bearbeitetbis, Mahnung, '.
  'T_Fehlzeitenstand.Lehrer AS Lehrer, Schuljahr '.
  'FROM T_Kurse LEFT JOIN T_Fehlzeitenstand ON T_Fehlzeitenstand.Kurs= ' .
  "T_Kurse.Kurs WHERE Schuljahr='$Schuljahr' ORDER BY T_Kurse.Kurs";

//echo '<br>'.$sql.'<br>';
$version = getAktuelleVersion();
if ( ! $query = mysql_query($sql,$db)) 
{
	echo '<div class="Fehler">Fehler '.mysql_error().'</div>';
}
echo '<tr><td><em>Aktualität: Fehlzeiten bearbeitet...</em></td></tr>';

$Abijahrgang = '';
$Jahrgang = explode(' ',$Schuljahr);
$Jahr = substr($Jahrgang[1], 3, 1); // Jahr ausfiltern
if ( $Jahrgang[0] == 'II')
{
	// K4 - Meldungen in längerem Abstand absetzen! (Abiturjahrgang)
    $Abijahrgang = 'OG '.(($Jahr+7)%10);    
} 
while ( $was = mysql_fetch_array($query) )
{
  // Winschool-Halbjahreshack (evtl. liegt der Stundenplan noch im 1.Halbjahr, da HJ-Wechsel in winschool falsch)
  $arrSchuljahr = Schuljahr::getSchuljahr('OG');
  if ( $arrSchuljahr['halbjahr'] == 2 )
  {
    $strKursHJ = $was['Kurs'];
    for($i = 0; $i < strlen($strKursHJ); $i++)
    {
      if( !is_numeric($strKursHJ[$i]) )
      {
        if ( $strKursHJ[$i+1] == '4' )
        {
          $strKursHJ[$i+1] = '3'; //Kurs aus letztem Semester
          break;
        }
        elseif ( $strKursHJ[$i+1] == '2' )
        {
          $strKursHJ[$i+1] = '1'; //Kurs aus letztem Semester
          break;
        }
      }
    }
  }

  $sql = "SELECT Klasse FROM T_StuPla WHERE (Fach='" . $was['Kurs'] . "' OR Fach='" . $strKursHJ . "') AND Klasse LIKE 'OG _' AND Version=$version LIMIT 1";
  //echo "<br>$sql<br>";
  $q = mysql_query($sql);
  
  if ( $klasse = mysql_fetch_array($q)) 
  {
        echo '<tr><td>';
        echo $was['Kurs'].' (';
        if ( $Abijahrgang == $klasse['Klasse'])
        {
            echo 'Abi,';
            if ( date('m')>=5 )
               echo 'fertig!,';
        }    
        $arrSchuljahr = Schuljahr::getSchuljahr('OG', $was['Bearbeitetbis']);
        if ( $was['Lehrer'] != '' && $arrSchuljahr['langform'] == $Schuljahr )
        {
          echo $was['Lehrer'].') bis '.date('d.m.Y',$was['Bearbeitetbis']);
          // wenn mehr als ein Monat vergangen ist 
          // und nicht Abijahrgang oder Abijahrgang noch vorhanden (< Mai)
          if ( $was['Bearbeitetbis'] < strtotime('-1 month -10 days') && 
               ($Abijahrgang != $klasse['Klasse'] || 
                date('m') < 5))
          {
          	
          }
          else
            mysql_query('UPDATE T_Fehlzeitenstand SET Mahnung=0 WHERE Kurs="'.$was['Kurs'].'"');
        }
        else
        {
          // Winschool-Halbjahreshack (evtl. liegt der Stundenplan noch im 1.Halbjahr, da HJ-Wechsel in winschool falsch)
          $arrSchuljahr = Schuljahr::getSchuljahr('OG');
          if ( $arrSchuljahr['halbjahr'] == 2 )
          {
            $strKursHJ = $was['Kurs'];
            for($i = 0; $i < strlen($strKursHJ); $i++)
            {
              if( !is_numeric($strKursHJ[$i]) )
              {
                if ( $strKursHJ[$i+1] == '4' )
                {
                  $strKursHJ[$i+1] = '3'; //Kurs aus letztem Semester
                  break;
                }
                elseif ( $strKursHJ[$i+1] == '2' )
                {
                  $strKursHJ[$i+1] = '1'; //Kurs aus letztem Semester
                  break;
                }
              }
            }
          }
          echo 'bisher nicht bearbeitet)';          
          $qu = mysql_query("SELECT Lehrer FROM T_StuPla WHERE (Fach='" . $was['Kurs'] . "' OR Fach='" . $strKursHJ . "') AND Version=" . $version);
          if ( $lehrer = mysql_fetch_row($qu) )
          {
          	$l = new Lehrer($lehrer[0], LEHRERID_KUERZEL);
            echo ' -- '.$l->Vorname.', '.$l->Name;            
          }
          else
          {
            echo ' -- Lehrer konnte nicht ermittelt werden!';
            // TODO Mail an Ansorge
          }
          mysql_free_result($qu);
        }        
        echo '</td></tr>';
  }
  mysql_free_result($q);      
}
mysql_free_result($query);

 include('include/footer.inc.php');
?>