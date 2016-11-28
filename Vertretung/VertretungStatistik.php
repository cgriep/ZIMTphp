<?php
/*
 * VertretungStatistik.php
 * Zeigt die Statistik der Vertretungen an.
 * 
 * Parameter:
 * Datum - Das Datum für das angezeigt werden soll. Wird in die Woche 
 *         umgerechnet, wenn angegeben.
 * Woche - die ID der Woche, die angezeigt werden soll
 * Jahr - Das Schuljahr (kurze Version), das angezeigt werden soll
 * 
 * Letzte Änderungen:
 * 08.01.06 C. Griep
 * 09.02.06 C. Griep - Wochenstatistik korrigiert
 */
$Ueberschrift = 'Vertretungsstatistik';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';

include('include/header.inc.php');
echo '<tr><td>';
/* ----
  Vertretungsstatistik
*/

include('include/helper.inc.php');
include('include/stupla.inc.php');
include('include/Vertretungen.inc.php');

function schreibeWoche($summe, $ID_Woche)
{
  $Montag = getMontag($ID_Woche);
  $Freitag = strtotime('+4 day',$Montag);
  $Turnusliste = array();
  getTurnusliste($ID_Woche, $Turnusliste);
  $Turnusliste[] = 'jede Woche';
  echo '<tr><td>';
  echo date('d.m.Y',$Montag).'-'.date('d.m.Y',$Freitag);
  echo '</td><td>';
  // Unterrichtstage berechnen
  $UTage = 5;
  for ($Tag = 0; $Tag <5; $Tag++)
    if ( istFrei(strtotime("+$Tag day",$Montag)) ) $UTage--;
  echo "$UTage</td>\n";

  if ( !$query = mysql_query('SELECT * FROM T_Vertretungen WHERE Art='.VERTRETUNG_AUSFALL.
    " AND Datum BETWEEN $Montag AND $Freitag")) echo mysql_error();
  $Ausfall = mysql_num_rows($query);
  mysql_free_result($query);
  if (!$query = mysql_query('SELECT * FROM T_Vertretungen WHERE GrundLehrer='.LEHRERKRANK.
    " AND Datum BETWEEN $Montag AND $Freitag AND Lehrer<>Lehrer_Neu")) echo mysql_error();
  $Krank = mysql_num_rows($query);
  mysql_free_result($query);
  $query = mysql_query('SELECT * FROM T_Vertretungen WHERE GrundLehrer='.LEHRERDIENST.
    " AND Datum BETWEEN $Montag AND $Freitag AND Lehrer<>Lehrer_Neu");
  $Dienst = mysql_num_rows($query);
  mysql_free_result($query);
  $query = mysql_query('SELECT * FROM T_Vertretungen WHERE GrundLehrer='.LEHRERFORTBILDUNG.
    " AND Datum BETWEEN $Montag AND $Freitag AND Lehrer<>Lehrer_Neu");
  $Fortbildung = mysql_num_rows($query);
  mysql_free_result($query);
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE GrundLehrer=".LEHRERSONDER.
    " AND Datum BETWEEN $Montag AND $Freitag AND Lehrer<>Lehrer_Neu");
  $Urlaub = mysql_num_rows($query);
  mysql_free_result($query);
  $Summe1 = $Urlaub+$Krank+$Fortbildung+$Dienst;

  echo "<td>$Krank</td><td>".($Fortbildung+$Urlaub)."</td>";
  echo "<td>$Dienst</td><td>$Summe1</td>";

  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE Art=".VERTRETUNG_TEILUNGAUFHEBEN.
    " AND Datum BETWEEN $Montag AND $Freitag");
  $NoTeilung = mysql_num_rows($query);
  mysql_free_result($query);
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE Art=".VERTRETUNG_ZUSATZKLASSE.
    " AND Datum BETWEEN $Montag AND $Freitag");
  $Zusatz = mysql_num_rows($query);
  mysql_free_result($query);
  $sql = "SELECT * FROM T_Vertretungen WHERE Art=".VERTRETUNG_VERTRETEN.
    " OR Art=".VERTRETUNG_RAUMWECHSEL." AND Datum BETWEEN $Montag AND $Freitag " .
    		"AND Lehrer<>Lehrer_Neu";
  $query = mysql_query($sql);
  $Vertretung = mysql_num_rows($query);
  $Lehrer = array();
  while ($row = mysql_fetch_array($query))
  {
    if ( ! in_array($row['Lehrer_Neu'],$Lehrer) )
      $Lehrer[] = $row['Lehrer_Neu'];
  }
  mysql_free_result($query);
  $Version = getAktuelleVersion($Montag);
  $Mehrarbeit = 0;
  foreach ($Lehrer as $derLehrer )
  {
    $sollstunden = berechneWochenstundenzahl($derLehrer, $Turnusliste, $Version);
    $Vertretungsstunden = berechneVertretungsstunden($derLehrer,
             $Montag, strtotime('+5 days',$Montag));
    $Ausfallstunden = berechneAusfallstunden($derLehrer,
             $Montag, strtotime('+5 days',$Montag));
    $Doppelstunden = berechneVertretungsstunden($derLehrer,
             $Montag, strtotime('+5 days',$Montag),
             'AND Art IN '.VERTRETUNG_MEHRARBEITOHNESTATISTIK);
    $Vertretungsstunden -= $Doppelstunden; // Doppelstunden zählen nicht als Vertretung    
    $VertretungWoche = $Vertretungsstunden-$Ausfallstunden;
    if ( $VertretungWoche > 0 )
      $Mehrarbeit += $VertretungWoche;
  }
  $Summe2 = $Zusatz+$NoTeilung+$Vertretung;
  $NoTeilung *= 2;
  $Zusatz *= 2;
  $Summe2 *= 2;
  $Mehrarbeit *= 2;
  $Ausfall *= 2;
  $Vertretung *= 2;
  $Krank *= 2;
  $Fortbildung *= 2;
  $Urlaub *= 2;
  $Dienst *= 2;
  $Summe1 *= 2;
  echo '<td>'.($NoTeilung+$Zusatz).'</td><td>'.($Vertretung-$Mehrarbeit).'</td>';
  echo "<td>$Mehrarbeit</td><td>$Summe2</td><td>$Ausfall</td>";
  echo "</tr>\n";
  $summe[0] = '';
  $summe[1] += $UTage;
  $summe[2] += $Krank;
  $summe[3] += $Fortbildung+$Urlaub;
  $summe[4] += $Dienst;
  $summe[5] += $Summe1;
  $summe[6] += $NoTeilung+$Zusatz;
  $summe[7] += $Vertretung-$Mehrarbeit;
  $summe[8] += $Mehrarbeit;
  $summe[9] += $Summe2;
  $summe[10] += $Ausfall;
  return $summe;
}

$Felder = array('Klasse', 'Lehrer', 'Raum');

if ( isset($_REQUEST['Datum']) )
{
  $Datum = explode('.',$_REQUEST['Datum']);
  if ( ! checkdate($Datum[1],$Datum[0],$Datum[2]) )
    $_REQUEST['Woche'] = getID_Woche();
  else
    $_REQUEST['Woche'] = getID_Woche(mktime(0,0,0,$Datum[1],$Datum[0],$Datum[2]));
}

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
echo '<input type="Text" name="Datum" value="'.date('d.m.Y').'" size="10" maxlength="10"/>
<input type="Submit" value="Anzeigen">
</form>';
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
echo '<input type="Text" name="Jahr" value="'.getSJahr().'" size="10" maxlength="10"/>
<input type="Submit" value="Anzeigen">
</form>';
echo '<br />';
echo "<table class=\"Liste\">\n";
echo '<tr><td class="Zwischenueberschrift" colspan="7">Schuljahr ';
echo getSJahr();
echo "</td></tr>\n";
echo "<tr><th>Art</th><th>Name</th><th>Von</th><th>Bis</th><th>Grund</th><th>Hinweis</th>";
echo "<th>vorh.<br />Änderungen</th></tr>\n";
$SJahrBeginn = getSJahrBeginn();
$query = mysql_query("SELECT * FROM T_Verhinderungen WHERE Von >= $SJahrBeginn ".
   " ORDER BY Art, Wer, Von");
while ( $eintrag = mysql_fetch_array($query) )
{
    echo "<tr>\n";
    echo "<td>{$eintrag["Art"]}</td><td><a href=\"../StuPla/PlanAnzeigen.php?{$eintrag["Art"]}=";
    echo "{$eintrag["Wer"]}\" target=\"_blank\">{$eintrag["Wer"]}</a></td>\n";
    echo "<td>".date("d.m.Y",$eintrag["Von"])."</td><td>".date("d.m.Y",$eintrag["Bis"])."</td>\n";
    echo "<td>";
    echo $Gruende[$eintrag['Grund']];
    echo "</td>\n";
    echo '<td>'.nl2br($eintrag['Hinweis'])."</td>\n";
    echo '<td><a href="../Verhinderung/Verhinderung.php?Verhinderung_id='.$eintrag["Verhinderung_id"];
    echo '">Bearbeiten</a>';
    echo "</td>\n";
    echo "</tr>\n";
}
mysql_free_result($query);
echo "</table>\n";

// Statistik für die angegebene Woche ausgeben
if ( isset($_REQUEST["Woche"]) && is_numeric($_REQUEST["Woche"]) )
{
  echo "<h1>Vertretungen für die ".getKW($_REQUEST["Woche"]).". KW</h1>";
  $Montag = getMontag($_REQUEST["Woche"]);
  $Freitag = strtotime("+4 day",$Montag);
  $Turnusliste = array();
  getTurnusliste($_REQUEST["Woche"], $Turnusliste);
  $Turnusliste[] = "jede Woche";
  echo 'Zeitfenster von '.date("d.m.Y",$Montag)." bis ".date("d.m.Y",$Freitag)."<br />\n";
  echo "Turnusse: ".implode(",",$Turnusliste)."<br />";
  // Unterrichtstage berechnen
  $UTage = 5;
  for ($Tag = 0; $Tag <6; $Tag++)
    if ( istFrei(strtotime("+$Tag day",$Montag)) ) $UTage--;
  echo "Unterrichtstage: $UTage<br />\n";
  echo '<h2>Krankheit</h2>';
  if (!$query = mysql_query("SELECT * FROM T_Vertretungen WHERE GrundLehrer=".LEHRERKRANK.
    " AND Datum BETWEEN $Montag AND $Freitag AND Lehrer<>Lehrer_Neu")) echo mysql_error();
  while ($row = mysql_fetch_array($query))
    echo date("d.m.Y", $row["Datum"])." ".$row["Stunde"].": ".$row["Lehrer"]."<br />";
  $Krank = mysql_num_rows($query);
  mysql_free_result($query);
  echo '<h2>Dienstl. Verh.</h2>';
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE GrundLehrer=".LEHRERDIENST.
    " AND Datum BETWEEN $Montag AND $Freitag AND Lehrer<>Lehrer_Neu");
  while ($row = mysql_fetch_array($query))
    echo date("d.m.Y", $row["Datum"])." ".$row["Stunde"].": ".$row["Lehrer"]."<br />";
  $Dienst = mysql_num_rows($query);
  mysql_free_result($query);
  echo '<h2>Fortbildung</h2>';
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE GrundLehrer=".LEHRERFORTBILDUNG.
    " AND Datum BETWEEN $Montag AND $Freitag AND Lehrer<>Lehrer_Neu");
  while ($row = mysql_fetch_array($query))
    echo date("d.m.Y", $row["Datum"])." ".$row["Stunde"].": ".$row["Lehrer"]."<br />";
  $Fortbildung = mysql_num_rows($query);
  mysql_free_result($query);
  echo '<h2>Urlaub</h2>';
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE GrundLehrer=".LEHRERSONDER.
    " AND Datum BETWEEN $Montag AND $Freitag AND Lehrer<>Lehrer_Neu");
  while ($row = mysql_fetch_array($query))
    echo date("d.m.Y", $row["Datum"])." ".$row["Stunde"].": ".$row["Lehrer"]."<br />";
  $Urlaub = mysql_num_rows($query);
  mysql_free_result($query);
  $Summe1 = $Urlaub+$Krank+$Fortbildung+$Dienst;
  $Summe1 *= 2;
  $Krank *= 2;
  $Urlaub *= 2;
  $Dienst *= 2;
  $Fortbildung *= 2;
  echo '<h2>Summe Ausfälle in Stunden</h2>';
  echo "Krankheit: $Krank<br />";
  echo "Fortbildung: $Fortbildung<br />";
  echo "Sonderurlaub: $Urlaub<br />";
  echo "Dienstl. Verhinderung: $Dienst<br />";
  echo "Summe 1: $Summe1<br />\n";
  echo "<hr />\n";
  echo '<h2>Ausfall</h2>';
  if ( !$query = mysql_query("SELECT * FROM T_Vertretungen WHERE Art=".VERTRETUNG_AUSFALL.
    " AND Datum BETWEEN $Montag AND $Freitag")) echo mysql_error();
  while ($row = mysql_fetch_array($query))
    echo date("d.m.Y", $row["Datum"])." ".$row["Stunde"].": ".$row["Lehrer"]."<br />";
  $Ausfall = mysql_num_rows($query);
  mysql_free_result($query);
  echo '<h2>Teilung aufheben</h2>';
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE Art=".VERTRETUNG_TEILUNGAUFHEBEN.
    " AND Datum BETWEEN $Montag AND $Freitag");
  while ($row = mysql_fetch_array($query))
    echo date("d.m.Y", $row["Datum"])." ".$row["Stunde"].": ".$row["Lehrer"]."<br />";
  $NoTeilung = mysql_num_rows($query);
  mysql_free_result($query);
  echo '<h2>Zusatzklasse</h2>';
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE Art=".VERTRETUNG_ZUSATZKLASSE.
    " AND Datum BETWEEN $Montag AND $Freitag");
  $Zusatz = mysql_num_rows($query);
  while ($row = mysql_fetch_array($query))
    echo date("d.m.Y", $row["Datum"])." ".$row["Stunde"].": ".$row["Lehrer_Neu"]."<br />";
  mysql_free_result($query);
  echo '<h2>Vertretung</h2>';
  $query = mysql_query("SELECT * FROM T_Vertretungen WHERE (Art=".VERTRETUNG_VERTRETEN.
    " OR Art=".VERTRETUNG_RAUMWECHSEL.") AND Datum BETWEEN $Montag AND $Freitag " .
    		"AND Lehrer<>Lehrer_Neu ");
  $Vertretung = mysql_num_rows($query);
  $Lehrer = array();
  while ($row = mysql_fetch_array($query))
  {
    echo date("d.m.Y", $row["Datum"])." ".$row["Stunde"].": ".$row["Lehrer"].
       " &rarr; ".$row["Lehrer_Neu"]."<br />";
    if ( ! in_array($row["Lehrer_Neu"],$Lehrer) )
      $Lehrer[] = $row["Lehrer_Neu"];
  }
  mysql_free_result($query);
  $Version = getAktuelleVersion($Montag);
  echo '<h2>Berechnung der Sollblockzahl</h2>';
  $Mehrarbeit = 0;
  foreach ($Lehrer as $derLehrer )
  {
    $sollstunden = berechneWochenstundenzahl($derLehrer, $Turnusliste, $Version);
    $Vertretungsstunden = berechneVertretungsstunden($derLehrer,
             $Montag, strtotime("+5 days",$Montag));
    $Ausfallstunden = berechneAusfallstunden($derLehrer,
             $Montag, strtotime("+5 days",$Montag));
    $Doppelstunden = berechneVertretungsstunden($derLehrer,
             $Montag, strtotime("+5 days",$Montag),
             "AND Art IN ".VERTRETUNG_MEHRARBEITOHNESTATISTIK);
    $Vertretungsstunden -= $Doppelstunden; // Doppelstunden zählen nicht als Vertretung
    $VertretungWoche = $Vertretungsstunden-$Ausfallstunden;
    echo $derLehrer." Sollblöcke.:".$sollstunden." / Vertretungsblöcke:".$VertretungWoche." = ".
      ($sollstunden+$VertretungWoche)."<br />";
    if ( $VertretungWoche > 0 )
      $Mehrarbeit += $VertretungWoche;
  }
  $Summe2 = $Zusatz+$NoTeilung+$Vertretung;
  $Summe2 *= 2;
  $NoTeilung *= 2;
  $Vertretung *= 2;
  $Zusatz *= 2;
  $Mehrarbeit *= 2;
  $Ausfall *= 2;  
  echo '<h2>Gesamt in Stunden</h2>';
  echo "Aufhebung Teilung: $NoTeilung<br />";
  echo "Doppelunterricht: $Zusatz<br />";
  echo "Gesamt-Vertretung : $Vertretung<br />";
  echo "Vertretung Mehrarbeit: $Mehrarbeit<br />";
  echo "Vertretung aus Bestand : ".($Vertretung-$Mehrarbeit)."<br />";
  echo "Summe 2 (Doppel+Teilung+Vertretung): $Summe2<br />\n";
  echo "Ausfall: $Ausfall<br />";
  echo "Kontrolle: Summe1-Summe2=".($Summe1-$Summe2)."<br />\n";
}
if ( isset($_REQUEST["Jahr"]) )
{
  echo "<h1>Vertretungsstatistik für das Schuljahr ".$_REQUEST["Jahr"]."</h1>";
  echo "<table class=\"Liste\">\n";
  echo '<tr><th>von-bis</th><th>U-Tage</th><th>Krank</th><th>Fortb.<br />S-Url.</th>';
  echo "<th>dienstl.<br />Verh.</th><th>Sum1</th>\n";
  echo "<th>Aufh.<br />Teil.</th><th>Res.</th><th>Mehra.</th><th>Sum2</th><th>Ausf.</th>\n";
  echo "</tr>\n";
  $summe = array();
  $summe[0] = "";
  for ( $i = 1; $i<11; $i++) $summe[$i]= 0;
  $query = mysql_query("SELECT * FROM T_Woche WHERE SJahr ='{$_REQUEST["Jahr"]}' ORDER BY Montag");
  $Jahr = 0;
  while ( $woche = mysql_fetch_array($query) )
  {
    if ( $Jahr != $woche["Jahr"] )
    {
      // Halbjahresende
      echo '<tr>';
        foreach ( $summe as $Wert )
         echo "<td class=\"Zwischenueberschrift\">$Wert</td>";
      echo "</tr>\n";
      echo '<tr ><td class="Zwischenueberschrift" colspan="11">Halbjahr II</td></tr>';
      $Jahr = $woche["Jahr"];
      for ($i=1;$i<11; $i++) $summe[$i] = 0;
    }
    $summe = schreibeWoche($summe, $woche["ID_Woche"]);
  }
  mysql_free_result($query);
  echo '<tr>';
  foreach ( $summe as $Wert )
       echo "<td class=\"Zwischenueberschrift\">$Wert</td>";
  echo "</tr>\n";
  echo "</table>\n";
}
echo '</td></tr>';
include('include/footer.inc.php');

?>