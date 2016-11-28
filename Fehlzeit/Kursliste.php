<?php
include_once("include/classes/orga/model/Schuljahr.class.php");
/**
 * Kursliste
 * Zeigt die Liste eines Kurses an und ermöglicht die Eingabe der Fehlzeiten
 *
 * 01.10.14 C. Griep - Korrektur Feldidentifizierer
 * 17.03.06 C. Griep / 28.12.2010 Binz
 *
 */
DEFINE('ANZ_TAGE', 12);
DEFINE('USE_KALENDER', 1);
// Fehlzeitarten.
// Achtung: Bei Reihenfolgenänderung auch Javascript anpassen!
$Eintraege[] = ' ';
$Eintraege[] = '-';
$Eintraege[] = 'A';
$Eintraege[] = 'K';
$Eintraege[] = 'S';
$Eintraege[] = 'P';
$Eintraege[] = 'V';
$Eintraege[] = 'N';

$AttestEintraege[] = ' ';
$AttestEintraege[] = '-';
$AttestEintraege[] = 'A';
$AttestEintraege[] = 'S';
$AttestEintraege[] = 'P';
$AttestEintraege[] = 'V';
$AttestEintraege[] = 'N';

$Ueberschrift = 'Kursliste anzeigen';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="https://lehrer.oszimt.de/css/oszimt.css">' .
        include('include/header.inc.php');
include('include/turnus.inc.php');
include('include/stupla.inc.php');

function SchreibeUeberschriften($Planeintraege, $MitAusfall = true)
{
  // Überschriften
  global $Wochentagnamen;
  echo '<tr><th>Nr</th><th>Name, Vorname</th>';
  foreach ( $Planeintraege as $key => $value )
  {
    $Block = substr($key, 0, strpos($key, 'B'));
    $Tag = substr($key, strpos($key, 'B') + 1);
    echo '<th align="center">';
    if ( $MitAusfall )
      echo '<input type="Checkbox" value="v" onClick="javascript:toggleAusfall(' . "'" . 'D' . $Block . 'B' . $Tag . "'" . ');"> f.a.<br />';
    echo substr($Wochentagnamen[date('w', $Tag)], 0, 3) . '<br />' . date('d.', $Tag) .
    '<br />' . date('m', $Tag);
    echo "<br />$Block</th>";
  }
  echo '</tr>';
}
?>
<script language="javascript">
  function toggleAusfall(value) {
    if (document.fehlzeiten.elements.length) {
      for (i=0; i<document.fehlzeiten.elements.length; i++) {
        if ( document.fehlzeiten.elements[i].name.indexOf(value) > 0 ) {
          if ( document.fehlzeiten.elements[i].options[1].selected )
            document.fehlzeiten.elements[i].options[0].selected = true;
          else
            document.fehlzeiten.elements[i].options[1].selected = true;
        }
      }
    }
  }
</script>
<?php
echo '<tr><td><table>';
if ( isset($_REQUEST['Kurs']) )
{
  if ( isset($_REQUEST['Save']) )
  {
    // Datensätze sichern
    $GroesstesDatum = 0;
    foreach ( $_REQUEST as $key => $value )
    {
      if ( substr($key, 0, 1) == 'S' && !strpos($key, 'D') === false &&
              !strpos($key, 'B') === false )
      {
        // Eintrag gefunden
        $Schuelernr = substr($key, 1, strpos($key, 'D') - 1);
        $Datumzahl = substr($key, strpos($key, 'B') + 1);
        $Block = substr($key, strpos($key, 'D') + 1, 1);
        if ( trim($value) == '' )
        {
          if ( !mysql_query('DELETE FROM T_Fehlzeiten WHERE Schueler_id = ' . $Schuelernr .
                          ' AND Datum = "' .
                          date('Y-m-d', $Datumzahl) . '" AND Block=' . $Block, $db) )
            echo mysql_error($db);
        }
        else
        {
          // Eintragen
          $Schuljahr = $_REQUEST['Schuljahr'];
          $Kurs = $_REQUEST['Kurs'];
          if ( $Datumzahl > $GroesstesDatum )
            $GroesstesDatum = $Datumzahl;
          if ( !mysql_query('INSERT INTO T_Fehlzeiten (Schueler_id, Art, Datum, Block, Kurs, Lehrer, Schuljahr) ' .
                          "VALUES ($Schuelernr,'" . $value . "', '" . date('Y-m-d', $Datumzahl) . "', $Block, '$Kurs','" .
                          $_SERVER['REMOTE_USER'] . "','" . $Schuljahr . "')", $db) )
          {
            $query = mysql_query('SELECT Art FROM T_Fehlzeiten WHERE Schueler_id = ' .
                    $Schuelernr . " AND Block=$Block AND Datum='" . date('Y-m-d', $Datumzahl) . "'", $db);
            $eintrag = mysql_fetch_row($query);
            mysql_free_result($query);
            if ( $eintrag[0] != $value )
              if ( !mysql_query("UPDATE T_Fehlzeiten SET Art = '$value',Lehrer='" .
                              $_SERVER['REMOTE_USER'] . "' WHERE Schueler_id=$Schuelernr AND Block=$Block AND " .
                              "Datum='" . date('Y-m-d', $Datumzahl) . "'", $db) )
                echo mysql_error($db);
          }
        }
      }
    }
    $stand = explode('.', $_REQUEST['Bearbeitetbis']);
    $stand = strtotime($stand[2] . '-' . $stand[1] . '-' . $stand[0]);
    if ( $stand < $GroesstesDatum )
      $stand = $GroesstesDatum;
    if ( !mysql_query("INSERT INTO T_Fehlzeitenstand (Lehrer,Kurs,Bearbeitetbis) VALUES ('" .
                    $_SERVER['REMOTE_USER'] . "','" . $_REQUEST['Kurs'] . "'," . $stand . ')', $db) )
    {
      if ( !mysql_query("UPDATE T_Fehlzeitenstand SET Lehrer='" . $_SERVER['REMOTE_USER'] .
                      "',Bearbeitetbis=" . $stand . ", Mahnung=0 WHERE Kurs='" . $_REQUEST['Kurs'] . "'", $db) )
        echo '<div class="Fehler">Fehler: ' . mysql_error($db) . '</div>';
    }
  }//if ( isset($_REQUEST['Save']) )
  
  echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post" id="fehlzeiten" name="fehlzeiten">';
  echo '<input type="hidden" name="Kurs" value="' . $_REQUEST['Kurs'] . '" />';
  echo '<tr><td colspan="' . ANZ_TAGE . '" align="center">';
  echo '<span class="home-content-titel">Teilnehmer des Kurses ' . $_REQUEST["Kurs"] . '</span>';
  echo '</td></tr>';
  if ( isset($_REQUEST['Datum']) && is_numeric($_REQUEST['Datum']) )
  {
    $intDatumTS = $_REQUEST['Datum'];
    echo '<input type="hidden" name="Datum" value="' . $_REQUEST['Datum'] . '">';
  }
  else
  {
    $intDatumTS = time();
    while ( sindFerien($intDatumTS, $db) ) //$intDatumTS auf ersten Tag nach den Ferien schieben
      $intDatumTS = strtotime('+1 day', $intDatumTS);
  }
  $intDatumTS = strtotime(date('Y-m-d', $intDatumTS));
  // Feststellen, welche Version des Stundenplanes gilt
  $version = getAktuelleVersion($intDatumTS);

  // Halbjahreshack (evtl. liegt der Stundenplan noch im letzten Halbjahr, da HJ-Wechsel in winschool falsch)
  $strKursHJ = $_REQUEST['Kurs'];
  for ( $i = 0; $i < strlen($strKursHJ); $i++ )
  {
    if ( !is_numeric($strKursHJ[$i]) )
    {
      if ( $strKursHJ[$i + 1] == '4' )
      {
        $strKursHJ[$i + 1] = '3'; //Kurs aus letztem Semester
        break;
      }
      elseif ( $strKursHJ[$i + 1] == '2' )
      {
        $strKursHJ[$i + 1] = '1'; //Kurs aus letztem Semester
        break;
      }
    }
  }

  $strSql = "SELECT DISTINCT Stunde, Wochentag, Turnus FROM T_StuPla WHERE (BINARY Fach = '" . $_REQUEST['Kurs'] . "' OR BINARY Fach = '$strKursHJ') AND Version = $version ORDER BY Wochentag, Stunde";
  $query = mysql_query($strSql, $db);
  //echo "<br>$strSql<br>";
  if ( mysql_num_rows($query) == 0 )
  {
    $version = getAktuelleVersion();
    $strSql = "SELECT DISTINCT Stunde, Wochentag, Turnus FROM T_StuPla WHERE (BINARY Fach = '" . $_REQUEST['Kurs'] . "' OR BINARY Fach = '$strKursHJ') AND Version = $version ORDER BY Wochentag, Stunde";
  }
  $Plananz = 0;
  $Plaene = array();
  while ( $plan = mysql_fetch_array($query) )
  {
    $nr = count($Plaene);
    $Plaene[$nr]['Block'] = $plan['Stunde'];
    $Plaene[$nr]['Tag'] = $plan['Wochentag'];
    $Plaene[$nr]['Turnus'] = $plan['Turnus'];
  }
  mysql_free_result($query);
  echo '<tr><td colspan="4"><a href="' . $_SERVER["PHP_SELF"] . "?Datum=" . strtotime("-3 week", $intDatumTS) .
  "&Kurs=" . $_REQUEST["Kurs"] . '">Datum weiter zurück</a><br />Vorher Speichern!</td><td colspan="' . (ANZ_TAGE - 3) . '" align="right">';
  echo '<a href="' . $_SERVER["PHP_SELF"] . "?Datum=" . strtotime("+3 week", $intDatumTS) .
  "&Kurs=" . $_REQUEST["Kurs"] . '">Datum weiter vor</a><br />Vorher speichern!</td></tr>';
  echo '<tr><td colspan="' . (ANZ_TAGE + 2) . '" class="home-content-titel">Legende</td></tr>';
  echo '<tr><td colspan="' . (ANZ_TAGE + 2) . '" class="home-content">A-Krank (Attest), ' .
  'K-Krank (ohne Attest), P-Privat, N-Unentschuldigt, V-Verspätet</td></tr>';
  echo '<tr><td colspan="' . (ANZ_TAGE + 2) . '" class="home-content">- ausgefallen ' .
  '("f.a.") , S-bei schulischer Veranstaltung (keine Fehlzeit), ' .
  '<span style="background-color:red;">&nbsp;&nbsp;</span> Attestpflicht</td></tr>';
  echo '<tr><td colspan="' . (ANZ_TAGE + 2) . '" align="center"><input type="Submit" name="Save" value="Speichern" /></td></tr>';
  echo '<tr><td colspan="' . (ANZ_TAGE + 2) . '" align="center">Bearbeitet bis <input type="text" name="Bearbeitetbis" value="';
  $sql = "SELECT * FROM T_Fehlzeitenstand WHERE Kurs='" . $_REQUEST["Kurs"] . "'";
  //echo "$sql<br>";
  $query = mysql_query($sql, $db);  
  if ( !$stand = mysql_fetch_array($query) )
  {
    $stand['Lehrer'] = $_SERVER['REMOTE_USER'];
    $stand['Bearbeitetbis'] = time();
  }
  mysql_free_result($query);
  echo date('d.m.Y', $stand['Bearbeitetbis']);
  echo '" ';
  ?>
  onClick="popUpCalendar(this,fehlzeiten['Bearbeitetbis'],'dd.mm.yyyy')"
  onBlur="autoCorrectDate('fehlzeiten','Bearbeitetbis' , false )"
  <?php
  echo ' /> von ' . $stand['Lehrer'];

  $anz = 0;
  $Planeintraege = array();
  $Tage = array();
  $mindatum = time();

  //Infos der Schuljahre holen
  $arrSchuljahr = Schuljahr::getSchuljahr('OG');
  $intMinDatumTS = $intMaxDatumTS = 0;
  if ( $arrSchuljahr['halbjahr'] == 1 ) //erstes Halbjahr
  {
    $intMinDatumTS = $arrSchuljahr['ersterTag'];
    $intMaxDatumTS = $arrSchuljahr['letzterTagHalbjahr'];
  }
  else //zweites Halbjahr
  {
    $intMinDatumTS = $arrSchuljahr['letzterTagHalbjahr'] + 1;
    $intMaxDatumTS = $arrSchuljahr['letzterTag'];
  }
  if ( $intDatumTS <= $intMinDatumTS )
    $intDatumTS = $intMinDatumTS; //Datum darf nicht vor Halbjahresbeginn liegen 

  $mindatum = $intDatumTS;
  $intMaxEintrag = 14;
  $intAnzEintrag = 0;
  do
  {
    for ( $j = 0; $j < count($Plaene); $j++ )
    {
      if ( date('w', $intDatumTS) == $Plaene[$j]['Tag'] && TurnusAktuell($Plaene[$j]['Turnus'], $intDatumTS, $db) && !sindFerien($intDatumTS, $db) )
      {
        $Planeintraege[$Plaene[$j]['Block'] . 'B' . $intDatumTS]['Turnus'] = $Plaene[$j]['Turnus'];
        $Tage[] = $intDatumTS + $Plaene[$j]['Block'] * 0.1;
        $intAnzEintrag++;
      }
    }
    $intDatumTS = strtotime('+1 day', $intDatumTS);
  } while ( $intAnzEintrag < $intMaxEintrag && $intDatumTS <= $intMaxDatumTS );

  $arrSchuljahr = Schuljahr::getSchuljahr('OG', $mindatum);
  $Schuljahr = $arrSchuljahr['langform'];

  $sql = 'SELECT Nr, Name, Vorname, Tutor FROM T_Schueler INNER JOIN T_Kurse ' .
          "ON T_Schueler.Nr = T_Kurse.Schueler_id WHERE BINARY Kurs = '" . $_REQUEST["Kurs"] .
          "' AND Schuljahr ='" . $Schuljahr . "' ORDER BY Name, Vorname";
  
  //echo "$sql<br>";
  if ( !$query = mysql_query($sql, $db) )
    echo 'Fehler: ' . mysql_error($db);
  if ( mysql_num_rows($query) == 0 )
  {
    $arrSchuljahr = Schuljahr::getSchuljahr('OG', mktime(0, 0, 0, date('m') + 1, 1, date('Y')));
    $Schuljahr = $arrSchuljahr['langform'];

    $sql = 'SELECT Nr, Name, Vorname, Tutor FROM T_Schueler INNER JOIN T_Kurse ' .
            "ON T_Schueler.Nr = T_Kurse.Schueler_id WHERE BINARY Kurs = '" . $_REQUEST['Kurs'] .
            "' AND Schuljahr ='" . $Schuljahr . "' ORDER BY Name, Vorname";
    if ( !$query = mysql_query($sql, $db) )
      echo 'Fehler: ' . mysql_error($db);
  }
  echo ' (Schuljahr ' . $Schuljahr . ')';
  echo '</td></tr>';
  echo '<tr><td colspan="2"></td><td colspan="' . (ANZ_TAGE) .
  '" align="center">Tag, Datum, Block</td></tr>' . "\n";

  $Schueler = array();
  while ( $s = mysql_fetch_array($query) )
  {
    $Schueler[] = $s;
    $sql = 'SELECT *, UNIX_TIMESTAMP(Aenderung) AS TS FROM T_Fehlzeiten WHERE Schueler_id = ' .
            $s['Nr'] . ' AND Kurs = "' . mysql_real_escape_string($_REQUEST['Kurs']) . '"';
    
    //echo "<br>$sql<br>";
    
    if ( !$query2 = mysql_query($sql, $db) )
      echo mysql_error($db);
    while ( $fehlzeit = mysql_fetch_array($query2) )
    {
      // falls zusätzliche Zeiten
      if ( strtotime($fehlzeit['Datum']) >= $mindatum && strtotime($fehlzeit['Datum']) <= $intDatumTS )
      {
        if ( !isset($Planeintraege[$fehlzeit['Block'] . 'B' . strtotime($fehlzeit['Datum'])]['Turnus']) )
        {
          $Planeintraege[$fehlzeit['Block'] . 'B' . strtotime($fehlzeit['Datum'])]['Turnus'] = 'e';
          $Tage[] = strtotime($fehlzeit['Datum']) + 0.1 * $fehlzeit['Block'];
        }
        $Planeintraege[$fehlzeit['Block'] . 'B' . strtotime($fehlzeit['Datum'])][$s['Nr']]['Art'] =
                $fehlzeit['Art'];
        $info[$fehlzeit['Block'] . 'B' . strtotime($fehlzeit['Datum'])][$s['Nr']]['Wer'] =
                $fehlzeit['Lehrer'];
        $info[$fehlzeit['Block'] . 'B' . strtotime($fehlzeit['Datum'])][$s['Nr']]['Wann'] =
                $fehlzeit['TS'];
      }
    }
    mysql_free_result($query2);
  }
  mysql_free_result($query);

  array_multisort($Tage, SORT_ASC, SORT_NUMERIC, $Planeintraege);
  SchreibeUeberschriften($Planeintraege);
  // Plan aufbauen
  $Color = false;
  foreach ( $Schueler as $nr => $s )
  {
    echo '<tr ';
    if ( $Color )
    {
      echo 'bgcolor="#5F5F5F"';  //'bgcolor="#808080"';
    }
    $Color = !$Color;
    $anz++;
    echo '><td>' . $anz . '</td><td><a href="Fehlzeiten.php?Schueler_id=' . $s["Nr"];
    echo '" target="_blank">' . $s["Name"] . ", " . $s["Vorname"] . "</a>\n";
    echo '<br /><font size="-1">' .
    '<em class="funktion">(' . $s["Tutor"] . ')</em></font></td>';
    foreach ( $Planeintraege as $key => $value )
    {
      echo '<td ';
      if ( isset($value[$s['Nr']]['Art']) && $value[$s['Nr']]['Art'] == '!' )
        echo 'bgcolor="red"';
      if ( isset($value[$s['Nr']]['Art']) && $value[$s['Nr']]['Art'] == 'X' )
      {
        echo 'bgcolor="yellow"';
        echo '>Abgang'; // Schüler ist abgegangen
      }
      else
      {
        echo '><select id="I' . $s['Nr'] . 'D' . $key . '" name="S' . $s['Nr'] . 'D' . $key . '" size="1" ';
        if ( $value[$s['Nr']] != '' )
        {
          echo 'title="';
          echo "eingetragen von " . $info[$key][$s["Nr"]]["Wer"] . " am " . date("d.m.Y H:i", $info[$key][$s["Nr"]]["Wann"]);
          echo '"';
        }
        echo ">\n";
        // Bei Attestpflicht eingeschränkte Wahl der Gründe
        $Pflicht = false;
        if ( isset($value[$s['Nr']]['Art']) && $value[$s['Nr']]['Art'] == '!' )
        {
          $Eintr = $AttestEintraege;
          $Pflicht = true;
        }
        else
          $Eintr = $Eintraege;
        foreach ( $Eintr as $ekey => $evalue )
        {
          if ( $Pflicht && $evalue == ' ' )
            $fvalue = '!';
          else
            $fvalue = $evalue;
          echo '<option value="' . $fvalue . '"';
          if ( isset($value[$s['Nr']]['Art']) &&
                  $value[$s['Nr']]['Art'] == $fvalue )
            echo ' selected="selected"';
          echo ">" . $evalue . '</option>';
        }
        echo "</select>\n";
      }
      echo '</td>';
    }
    echo "</tr>\n";
  }
  if ( !isset($Schueler) || Count($Schueler) == 0 )
    echo '<tr><td colspan="' . (ANZ_TAGE + 1) . '"><div class="Hinweis">Keine Schüler für den Kurs gefunden</div></td></tr>';
  else
  {
    SchreibeUeberschriften($Planeintraege, false);
    echo '<tr><td colspan="' . (ANZ_TAGE + 1) . '" align="center"><input type="Submit" name="Save" value="Speichern" /></td></tr>';
    echo '<input type="hidden" name="Schuljahr" value="' . $Schuljahr . '" />';
  }
  echo '<tr><td colspan="' . (ANZ_TAGE + 1) . '"><hr /></td></tr>';
  echo '</form>';
}
echo '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
echo '<tr><td colspan="' . ANZ_TAGE . '">';

$arrSchuljahr = Schuljahr::getSchuljahr('OG');
$strSql = "SELECT DISTINCT Kurs FROM T_Kurse WHERE Schuljahr = '" . $arrSchuljahr['langform'] . "' ORDER BY Kurs";

$query = mysql_query($strSql, $db);
echo 'Kurs <select name="Kurs">';

while ( $fach = mysql_fetch_row($query) )
  if ( trim($fach[0]) != '' )
    echo '<option>' . $fach[0] . '</option>';

mysql_free_result($query);
echo '</select><input type="Submit" value="anzeigen">';
echo '</td></tr>';
echo '</form>';
echo '</td></tr></table>';
include("include/footer.inc.php");
?>
