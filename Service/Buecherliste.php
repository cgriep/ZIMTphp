<?php
/*
 * Zeigt eine Liste aller Bücher an
 * (c) 2006 Christoph Griep
 * 
 */
define('USE_OVERLIB',1);
$Ueberschrift = 'Bücherliste vom '.date('d.m.Y H:i');
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');

  $Art = 'Bücher';
  echo '<tr><td>';
  
  if ( ! isset($_REQUEST['Search'])) $_REQUEST['Search'] = '';
  echo '<form class="Verhinderung" action="'.$_SERVER['PHP_SELF'].'" method="post">';
  echo 'Titel,Raum,ISBN <input type="Text" name="Search" value="'.$_REQUEST['Search'].'" /> ';
  echo '<input type="Submit" value="Suchen" />';
  echo '<br />Zum Sortieren der Tabelle nach einer Spalte klicken Sie auf ' .
  		'die entsprechende Überschrift.';
  echo '</form>';
  echo '</td></tr>';  
  echo "<tr><td>\n";
  echo 'Soweit vorhanden, erscheinen Bemerkungen zum Buch wenn die Maus über den Titel geführt wird.';
  echo '<table class="Liste">';
  $Search = '1';
  $Order = '';
  if ( isset($_REQUEST['Sort']) && trim($_REQUEST['Sort']) != '')
    $Order = mysql_real_escape_string($_REQUEST['Sort']).',';
  $Order .= 'Bezeichnung, Inventar_Nr';
  if ( isset($_REQUEST['Search'] ) && trim($_REQUEST['Search']) != '' )
  {
    $Search .= " AND (Bezeichnung REGEXP '".mysql_real_escape_string($_REQUEST['Search'])."' OR ".
      "Seriennummer='".mysql_real_escape_string($_REQUEST['Search'])."' OR Raumnummer='".
      mysql_real_escape_string($_REQUEST['Search'])."' OR Inhalt LIKE '".
      mysql_real_escape_string($_REQUEST['Search'])."')";
  }
  if ( ! $query = mysql_query("SELECT *, T_Inventar.Bemerkung AS Bemerkung FROM ((T_Inventar INNER JOIN T_Raeume ".
      "ON F_Raum_id = Raum_id) INNER JOIN T_Inventardaten ON ".
      "F_Inventar_id=Inventar_id) INNER JOIN ".
      "T_Inventardatenarten ON T_Inventardaten.F_Art_id=T_Inventardatenarten.Art_id ".
      "WHERE T_Inventar.F_Art_id=4 AND ($Search) ".
      "ORDER BY $Order")) echo '<div class="Fehler">Fehler: '.mysql_error().'</div>';
  $Search = '';
  if ( isset($_REQUEST['Search']) && $_REQUEST['Search'] != '')
    $Search = '&Search='.$_REQUEST['Search'];
  $Arten = array();
  $Buecher = array();
  $buch['Raumnummer'] = '';
  $sort = array();
  $Gesamt = array();
  while ( $inv = mysql_fetch_array($query) )
  {
      if ( $buch['Raumnummer'] != $inv['Raumnummer'] ||
           $buch['Bezeichnung'] != $inv['Bezeichnung'] )
      {
        if ($buch['Raumnummer'] != '' )
        {
          $Buecher[] = $buch;
          if ( isset($_REQUEST['SortI']) )
            $sort[] = $buch[$_REQUEST['SortI']];
          if ( ! isset($Gesamt[$buch['Bezeichnung']]) )
            $Gesamt[$buch['Bezeichnung']] = $buch['Menge'];
          else
            $Gesamt[$buch['Bezeichnung']] += $buch['Menge'];
        }
        $buch = array();
        $buch['Bezeichnung'] = $inv['Bezeichnung'];
        $buch['Inventar_Nr'] = $inv['Inventar_Nr'];
        $buch['Inventar_id'] = $inv['Inventar_id'];
        $buch['Raumnummer'] = $inv['Raumnummer'];
        $buch['Bemerkung'] = $inv['Bemerkung'];
        $buch['Seriennummer']=$inv['Seriennummer'];
        $buch['Herstellungsjahr'] = $inv['Herstellungsjahr'];
      }
      $buch[$inv['Art']] = $inv['Inhalt'];
  }
  // Sortieren nach Inhalt
  if ( isset($_REQUEST['SortI']) )
  {
    array_multisort($sort, SORT_ASC, $Buecher);
  }
  {
    echo '<tr><th><a href="'.$_SERVER['PHP_SELF'].
    '?Sort=Bezeichnung&Buecher=1'.$Search.'" title="Sortieren nach Bezeichnung">';
    echo 'Bezeichnung</a></th>';
    echo '<th>Menge</th><th><a href="'.$_SERVER['PHP_SELF'].'?Sort=Raumnummer'.
      $Search.'&Buecher=1">';
    echo 'Raum</a></th>';
    echo '<th><a href="'.$_SERVER['PHP_SELF'].'?Sort=Herstellungsjahr&Buecher=1'.
      $Search.'">Jahr</a></th>';
    echo '<th><a href="'.$_SERVER['PHP_SELF'].
    '?Sort=Seriennummer&Buecher=1'.$Search.'">ISBN</a>';
    echo '</th><th><a href="'.$_SERVER['PHP_SELF'].
    '?SortI=Verlag&Buecher=1'.$Search.'">Verlag</a>';
    echo '</th><th><a href="'.$_SERVER['PHP_SELF'].
    '?SortI=Fachbereich&Buecher=1'.$Search.'">Fachbereich</a></th>';
    echo '</tr>';
    foreach ( $Buecher as $buch )
    {
      if ( $buch['Menge'] > 0)
      {
        echo '<tr><td><span ';
        if ( $buch['Bemerkung'] != '' )
        {
        	echo ' onMouseOver="return overlib(\''.nl2br($buch['Bemerkung']);
        	echo '\',CAPTION,\''.$buch['Bezeichnung'].'\');" ';
        	echo 'onMouseOut="return nd();"';
        }
        echo '>';
        echo stripslashes($buch['Bezeichnung']).'</span></td>';
        echo '<td>'.$buch['Menge'].'</a></td>';
        echo '<td>'.stripslashes($buch['Raumnummer']).'</td>';
        echo '<td>'.stripslashes($buch['Herstellungsjahr']).'</td>';
        echo '<td>'.stripslashes($buch['Seriennummer']).'</td>';
        echo '<td>'.stripslashes($buch['Verlag']).'</td>';
        echo '<td>'.stripslashes($buch['Fachbereich']).'</td>';
        echo "</tr>\n";
      }
    }
  }
  mysql_free_result($query);
  echo '</table>';
  echo count($Buecher).' Einträge<br />';

include("include/footer.inc.php");
?>