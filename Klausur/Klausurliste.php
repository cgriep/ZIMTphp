<?php
/*
 * Zeigt die fehlenden Klausurergebnisse für eine Klassenart an.
 * (c) 2006 Christoph Griep
 * 
 */
$Ueberschrift = 'Klausurergebnisse auflisten';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');
include('include/Klausur.inc.php');
include('include/stupla.inc.php');
include('include/turnus.inc.php');
include('include/Lehrer.class.php');

function fachVorhanden($bez,$Faecher)
{
  foreach ( $Faecher as $value )
    if ( $value['Fach'] == $bez )
      return true;
  return false;
}

function zeigeTabelle($Klassen, $Faecher, $Schuljahr, $Version, $db)
{
  echo '<tr bgcolor="darkgrey"><td class="Zwischenueberschrift">Fach</td>';
  foreach ( $Klassen as $key => $value )
  {
    echo '<td class="Zwischenueberschrift">'.$key.'</td>';
  }
  echo '</tr>';
  $Werte = array();
  if ( substr($Schuljahr,0,2) == "II" )
    $Halbjahr = 2;
  else
    $Halbjahr = 1;
  foreach ( $Faecher as $key => $value )
  {
    echo '<tr><td>'.$value['Fach'].' ('.$value['HJ'.$Halbjahr];
    // Sonderfälle: spezielle Anzahl für einen Jahrgang 
    if ( isset($value['JG']) )
    {
      echo ', JG '.$value['JG'];
    }
    echo ')</td>';
    // Prüfen, ob es mehr als ein Fach gibt (OG - en21 zu en usw.)
    $Labor = $value['Fach'].'%L';
    foreach ( $Klassen as $kkey => $kvalue )
    {
      // Prüfen ob die Klasse einem evtl. Jahrgang entspricht
      if ( isset($value['JG']))
      {
        // Falscher Jahrgang?
        if ( strpos($kkey, ' '.$value['JG']) === false )
          $kkey = '';        
      }
      $sql = "SELECT DISTINCT Fach FROM T_Klausurergebnisse WHERE Klasse='".
        $kkey."' AND Schuljahr = '".$Schuljahr."' AND Fach LIKE '".
        $value['Fach']."%' ORDER BY Fach";
      if ( ! $query = mysql_query($sql,$db))
          echo mysql_error($db);
      echo '<td><table class="" align="center" width="100%"><tr>';
      while ( $fachbez = mysql_fetch_array($query) )
      {
        if ( $value['Fach'] != $fachbez['Fach'] && fachVorhanden($fachbez['Fach'],$Faecher) )
        {
          // nicht zeigen
        }
        else
        {
          if ( $value['Fach'] != $fachbez['Fach'] )
          {
            $zeigen = $fachbez['Fach'];
          }
          else
            $zeigen = '';
          echo '<td style="border-style:none;" valign="top" align="center" ';
          if ( count($kvalue[$fachbez['Fach']]) < $value['HJ'.$Halbjahr] ||
            ! isset($kvalue[$fachbez['Fach']]) )
            echo ' class="Liste_VertretungVorhanden"'; //bgcolor="red"';
          echo '>';
          if ( $zeigen != '' ) echo $zeigen.'<br />';
          for ( $i = 0; $i < count($kvalue[$fachbez['Fach']]); $i++ )
          {
            if ( $i > 0 ) echo '<br />';
            echo '<a href="Klausurergebnisse.php?Klausur_id='.
              $kvalue[$fachbez['Fach']][$i]['Klausur_id'].'"';
            echo ' title="Klausur vom '.$kvalue[$fachbez['Fach']][$i]['Datum'].' '.
              $kvalue[$fachbez['Fach']][$i]['Lehrer'].'"';
            echo '>';
            $d =Durchschnitt($kvalue[$fachbez['Fach']][$i]);
            // Unterm Schnitt ??
            if ( Teilnehmeranzahl($kvalue[$fachbez['Fach']][$i]) <
              ($kvalue[$fachbez['Fach']][$i]['Fuenfer']+
              $kvalue[$fachbez['Fach']][$i]['Sechser'])*3 )
                $d = '<em>'.$d.'</em>';
            echo $d;
            $Werte[$kkey]['Schnitt'] += Durchschnitt($kvalue[$fachbez['Fach']][$i]);
            $Werte[$kkey]['Anz']++;
            echo '</a>';
            if ( $kvalue[$fachbez['Fach']][$i]['Kenntnisnahme'] == '' )
              echo '<span title="noch nicht bestätigt">!!</span>';
          }
          if ( count($kvalue[$fachbez['Fach']]) < $value['HJ'.$Halbjahr] ||
            ! isset($kvalue[$fachbez['Fach']]) )
          {
            if ( count($kvalue[$fachbez['Fach']]) > 0 ) echo '<br />';
            echo '<strong>Fehlt: '.($value['HJ'.$Halbjahr]-
              count($kvalue[$fachbez['Fach']])).' Erg.</strong>';
            echo '<br />(';
            $Lehrer = new Lehrer( 
                FachLehrer($fachbez['Fach'], $kkey, $db, $Version), LEHRERID_KUERZEL);
              echo $Lehrer->Name.')';
          }
          echo "</td>\n";
        }
      }
      if ( $Schuljahr == Schuljahr(false) )
      {
        mysql_free_result($query);
        $query = mysql_query('SELECT DISTINCT Fach FROM T_StuPla '.
          "WHERE Version=$Version AND Klasse='".$kkey."' AND Fach LIKE '".
          $value['Fach']."%' AND Fach NOT Like '$Labor' ORDER BY Fach",$db);
        while ( $fach = mysql_fetch_array($query) )
        {
          if ( ! fachVorhanden($fach['Fach'],$Faecher) ||$fach['Fach']==$value['Fach'] )
          if ( !isset($kvalue[$fach['Fach']]) || count($kvalue[$fach['Fach']]) == 0 )
          {
            if ( $value['HJ'.$Halbjahr] > 0 )
            {
              echo '<td class="Liste_VertretungVorhanden" style="border-style:none;" align="center">';
              if ( $fach['Fach'] != $value['Fach'])
                echo $fach['Fach'].'<br />';
              echo '<strong>Fehlt: '.$value['HJ'.$Halbjahr].' Erg.</strong>';
              echo '<br />(';
              $Lehrer = new Lehrer( 
                FachLehrer($fach['Fach'], $kkey, $db, $Version), LEHRERID_KUERZEL);
              echo $Lehrer->Name.')';
              echo '</td>';
            }
            else
              echo '<td>&nbsp;</td>';
          }
        }
      }
      echo '</tr></table></td>';
      mysql_free_result($query);
    }
    echo '</tr>';
  }
  // Gesamtdurchschnitt der Klasse berechnen
  echo '<tr><td><em>Gesamtschnitt</em></td>';
  foreach ( $Klassen as $kkey => $kvalue)
  {
    echo '<td align="center">';
    if ( isset($Werte[$kkey]) && $Werte[$kkey]['Anz'] > 0 )
      echo sprintf('%.02f',($Werte[$kkey]['Schnitt']/$Werte[$kkey]['Anz']));
    else
      echo 'n/a';
    echo '</td>';
  }
  echo '</tr>';
} // zeigeTabelle

// Beginn der Ausgabe
echo '<tr><td>';

if ( isset($_REQUEST['Art']) )
{
  $Schuljahr = $_REQUEST['Schuljahr'];
  echo "<h2>Schuljahr: $Schuljahr</h2><br />";
  
  // Hinzufügen der aktuellen Klassen wenn aktuelles Schuljahr
  // wenn aktuellesSchuljahr dann aktuelle Klassen hinzufügen
  $Klassen = array();
  $Jahrgang = Schuljahr(false);
  // ArtAlle: Enthält die allgemeine Klassenbezeichnung
  $ArtAlle = str_replace('1','_',$_REQUEST['Art']);
  $ArtAlle = str_replace('2','_',$ArtAlle);
  $ArtAlle = str_replace('3','_',$ArtAlle);
  $ArtAlle = str_replace('4','_',$ArtAlle);
  
  // die Klausuranzahlen einlesen, die erforderlich sind
  $query = mysql_query("SELECT * FROM T_Faecher WHERE Art LIKE '".
    mysql_real_escape_string($ArtAlle)."' ORDER BY Fach", $db);
  while ( $fach = mysql_fetch_array($query) )
  {
  	// Prüfen, ob eine Jahrgangsspezifische Angabe erfolgt ist. In diesem Fall 
  	// anpassen, sonst für alle Jahrgänge festlegen 
    $Faecher[] = $fach;    
  }
  mysql_free_result($query);
  
  if ( $Schuljahr == $Jahrgang )
  {       
    $Jahrgang = explode(' ',$Jahrgang);
    $Jahr = substr($Jahrgang[1], 3, 1); // Jahr ausfiltern
    // Berechnung welche Jahrgangsbezeichnung die Klassen haben 
    /*
    Klärung - eigentlich sollte der Einer des Jahr immer mit dem Jahrgangstart übereinstimmen
    daher sollte diese Berechnung nach Halbjahr nicht notwendig sein. 
    if ( $Jahrgang[0] == 'II')
      $Jahr = ($Jahr+9)%10;
    else
      $Jahr = $Jahr % 10; // 30.11. - ursprünglich Jahr+1 mod 10
    */
    // stattdessen: Sicherstellen dass das Jahr einstellig ist
    if ( $Jahrgang[0] == 'II') $Jahr--;
    $Jahr = $Jahr % 10;
    $FaecherNeu = array();
    foreach ( $Faecher as $Fach )
    {     
      if ( strpos($Fach['Art'],'1') !== false )
      {
        $Fach['Art'] = str_replace('1',$Jahr,$Fach['Art']);
        $Fach['JG'] = $Jahr;
      }
      elseif ( strpos($Fach['Art'],'2') !== false )
      {
        $J = ($Jahr + 9 ) % 10;
        $Fach['Art'] = str_replace('2',$J,$Fach['Art']);
        $Fach['JG'] = $J;
      }
      elseif ( strpos($Fach['Art'],'3') !== false )
      {
        $J = ($Jahr + 8 ) % 10;
        $Fach['Art'] = str_replace('3',$J,$Fach['Art']);
        $Fach['JG'] = $J;
      }
      elseif ( strpos($Fach['Art'],'4') !== false )
      {
        $J = ($Jahr + 7 ) % 10;
        $Fach['Art'] = str_replace('4',$J,$Fach['Art']);
        $Fach['JG'] = $J;
      }
      
      $FaecherNeu[] = $Fach;
    }
    $Faecher = $FaecherNeu;     
    $Version = getAktuelleVersion();
    // Alle Klassen der gewählten Art einlesen
    $query = mysql_query('SELECT DISTINCT Klasse FROM T_StuPla '.
      "WHERE Version=$Version AND Klasse LIKE '".
      mysql_real_escape_string($ArtAlle)."'",$db);
    while ( $fach = mysql_fetch_array($query) )
    {
      foreach ( $Faecher as $key => $value )
        $Klassen[$fach['Klasse']][$value['Fach']]= array();
    }
    mysql_free_result($query);
  }
  else
  {
  	 $_REQUEST['Art'] = $ArtAlle;  	 
  }
  $sql = "SELECT * FROM T_Klausurergebnisse WHERE BINARY Klasse LIKE '".
    mysql_real_escape_string($ArtAlle).
    "' AND Schuljahr = '$Schuljahr' ORDER BY Klasse";
  $query = mysql_query($sql,$db);
  // Alle Ergebnisse einlesen
  while ( $ergebnis = mysql_fetch_array($query) )
  {
    $nr = count($Klassen[$ergebnis['Klasse']][$ergebnis['Fach']]);
    $Klassen[$ergebnis['Klasse']][$ergebnis['Fach']][$nr] = $ergebnis;
  }
  mysql_free_result($query);
  echo '<tr><td><table class="Liste">';
  $dKlassen = array();
  foreach ( $Klassen as $Fach => $Klasse )
  {
    $dKlassen[$Fach] = $Klasse;
    if ( Count($dKlassen) == 3 )
    {
      zeigeTabelle($dKlassen, $Faecher, $Schuljahr, $Version, $db);
      $dKlassen = array();
    }
  }
  if ( Count($dKlassen) > 0 )
    zeigeTabelle($dKlassen, $Faecher, $Schuljahr, $Version, $db);
  echo '</table></td></tr>';
    echo '<tr><td colspan="'.(count($Klassen)+1).'"><hr /></td></tr>';
}
if ( ! isset($_REQUEST['Print'] ) )
{
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
<tr><td>Klassenart <select name="Art">
<?php
$query = mysql_query('SELECT DISTINCT Art FROM T_Faecher ORDER BY Art',$db);
while ( $art = mysql_fetch_row($query) )
{
  echo '<option>'.$art[0]."</option>\n";
}
mysql_free_result($query);
?>
</select>
<label for="Schuljahr">Schuljahr</label>
<select name="Schuljahr">
<?php
  $sj1 = Schuljahr(false);
  $sj2 = Schuljahr(false, strtotime('-6 month'));
  $sj3 = Schuljahr(false, strtotime('+6 month'));
  // vorhandene Schuljahre anzeigen
  $query = mysql_query('SELECT Schuljahr FROM T_Klausurergebnisse GROUP BY Schuljahr ORDER BY Datum');
  while ( $Schuljahr = mysql_fetch_row($query) )
  {
    echo '<option ';
    if ( $sj1 == $Schuljahr[0] )
    {
      echo 'selected="selected"';
      $sj1 = "";
    }
    echo '>'.$Schuljahr[0]."</option>\n";
    if ( $Schuljahr[0] == $sj2 ) $sj2 = '';
    if ( $Schuljahr[0] == $sj3 ) $sj3 = '';
  }
  mysql_free_result($query);
  if ( $sj2 != '' ) echo '<option>'.$sj2.'</option>';
  if ( $sj1 != '' ) echo '<option selected="selected">'.$sj1.'</option>';
  if ( $sj3 != '' ) echo '<option>'.$sj3.'</option>';
?>
</select>
<input type="Submit" value="Anzeigen">
</td></tr>
</form>
<?php
 }
 include('include/footer.inc.php');
?>
