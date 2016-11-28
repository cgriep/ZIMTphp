<?php
/**
 * (c) 2006 Christoph Griep
 * Inventarisierungsliste
 */
$Ueberschrift = 'Inventar anzeigen';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');

if ( isset($_REQUEST['Art_id']) && is_numeric($_REQUEST['Art_id']) )
{
  if ( $_REQUEST['Art_id'] > 0)
  {
    $q = mysql_query('SELECT Art FROM T_Inventardatenarten WHERE Art_id='.$_REQUEST['Art_id']);
    if ( ! $Art = mysql_fetch_array($q) ) $Art = array();
    mysql_free_result($q);
    $Art = $Art['Art'];
  }
  else
  {
    $Art = '';    
  }
  //Liste des Inventars
  echo '<tr><td>';
  echo '<table class="Liste">';
  echo '<tr><th>Inventarnr</th><th>Seriennummer</th><th><a href="'.$_SERVER["PHP_SELF"].
    '?Sort=Bezeichnung&Art_id='.$_REQUEST['Art_id'].'" title="Sortieren nach Bezeichnung">';
  echo 'Bezeichnung</a></th><th>';
  echo '<a href="'.$_SERVER['PHP_SELF'].'?Sort=Lieferant&Art_id='.$_REQUEST['Art_id'].
    '" title="Sortieren nach Lieferant">Lieferant</a></th>';
  echo '<th><a href="'.$_SERVER['PHP_SELF'].'?Sort=Inhalt&Art_id='.$_REQUEST['Art_id'].
    '">'.$Art.'</a></th><th>Bemerkung</th>';
  echo '</tr>';
  $Search = '1';
  $Order = '';
  if ( isset($_REQUEST['Raum']) && is_array($_REQUEST['Raum']))
  {
  	session_unregister('InvListeRaum');  	
  	if (Count($_REQUEST['Raum']) >0)  	
  	{
  		$_SESSION['InvListeRaum'] = $_REQUEST['Raum'];
  	}
  }
  if ( session_is_registered('InvListeRaum'))
  {
  		foreach ($_SESSION['InvListeRaum'] as $value )
  		  $Search = $value.',';
  		$Search = 'F_Raum_id IN ('.$Search.'-1)';
  }  
  if ( isset($_REQUEST['Art']) && is_array($_REQUEST['Art']))
  {
  	session_unregister('InvListeArt');
  	if (Count($_REQUEST['Art']) >0)
  	{
  		$_SESSION['InvListeArt'] = $_REQUEST['Art'];
  	}
  }
  if ( session_is_registered('InvListeArt'))
  {
     	$Search2 = '';
  	  	foreach ($_SESSION['InvListeArt'] as $value )
  		  $Search2 .= $value.',';
  		$Search .= ' AND T_Inventar.F_Art_id IN ('.$Search2.'-1)';
  }
  if ( isset($_REQUEST['Sort']) )
    $Order = mysql_real_escape_string($_REQUEST['Sort']).',';
  $Order .= 'Bezeichnung, Inventar_Nr';
  if ( isset($_REQUEST['Liste']) )
  {
    switch ( $Liste )
    {
     case 'E':
        $Search = 'Entsorgungsdatum>0';
        break;
     case 'O':
        $Search = '(Inventar_Nr="" OR Inventar_Nr IS NULL)';
        break;
    }
  }
  if ( isset($_REQUEST['Inhalt']) )
    $Search .= " AND Inhalt='".mysql_real_escape_string($_REQUEST['Inhalt'])."'";
  if ( $_REQUEST['Art_id'] > 0)
    $Search .= ' AND T_Inventardaten.F_Art_id='.$_REQUEST['Art_id'];
  if ( isset($_REQUEST['Search'] ) && $_REQUEST['Search'] != '' )
  {
    $Search .= " AND (Bezeichnung REGEXP '".mysql_real_escape_string($_REQUEST['Search'])."' OR ".
      "Inhalt='".mysql_real_escape_string($_REQUEST['Search'])."' OR Seriennummer REGEXP '".
      mysql_real_escape_string($_REQUEST['Search'])."')";
  }
  if ( ! $query = mysql_query('SELECT * FROM (T_Inventar INNER JOIN T_Lieferanten '.
    'ON F_Lieferant_id = Lieferant_id) INNER JOIN T_Inventardaten ON '.
    'F_Inventar_id=Inventar_id WHERE '.$Search. 
    ' ORDER BY '.$Order)) echo '<div class="Fehler">Fehler: '.mysql_error().'</div>';
  $Arten = array();
  while ( $inv = mysql_fetch_array($query) )
  {
    echo '<tr><td><a href="Inventar.php?id='.$inv['Inventar_id'].'">';
    if ( trim(stripslashes($inv['Inventar_Nr'])) != '' )
      echo stripslashes($inv['Inventar_Nr']);
    else
      echo '(unbekannt)';
    echo '</a></td><td>';
    echo stripslashes($inv['Seriennummer']);
    echo '</td><td>';
    echo stripslashes($inv['Bezeichnung']).'</td>';
    echo '<td><a href="Lieferant.php?id='.$inv['Lieferant_id'].'">';
    echo stripslashes($inv['Name']).'</a></td>';
    echo '<td>'.stripslashes($inv['Inhalt']).'</td>';
    echo '<td>'.stripslashes($inv['Bemerkung']).'</td>';
    echo '</tr>';
  }
  echo '<tr><td colspan="6">'.mysql_num_rows($query).' Einträge</td></tr>';
  mysql_free_result($query);
  echo '</table>';
  echo '<a href="Inventar.php?id=-1">Neues Inventar hinzufügen</a> / ';
  echo '<a href="'.$_SERVER["PHP_SELF"].'?Liste=E">Entsorgtes Inventar</a> / ';
  echo '<a href="'.$_SERVER["PHP_SELF"].'?Liste=O">unnummeriertes Inventar</a> <br />';
  echo '[ <a href="Inventar.php">Gesamtliste</a> ] ';
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  if ( isset($_REQUEST['Liste']) )
    echo '<input type="hidden" name="Liste" value="'.$_REQUEST['Liste'].'" />';
  echo '<input type="hidden" name="Art_id" value="'.$_REQUEST["Art_id"].'" />';
  if ( !isset($_REQUEST['Search'])) $_REQUEST['Search'] = '';   
  echo 'Inventar <input type="Text" name="Search" value="'.$_REQUEST["Search"].'" /> ';
  echo '<input type="Submit" value="Suchen" /><br />';
  echo '<label>Raum</label> ';
  echo '<select name="Raum[]" multiple="multiple" size="5">';
  $q = mysql_query('SELECT * FROM T_Raeume ORDER BY Raumnummer');
  while ( $Art = mysql_fetch_array($q) ) 
  {
  	echo '<option value="'.$Art['Raum_id'].'" ';
    if ( !session_is_registered('InvListeRaum') || in_array($Art['Raum_id'], 
      $_SESSION['InvListeRaum']))
  	  echo 'selected="selected"';    
    echo '>'.$Art['Raumnummer'].' '.
  	  $Art['Raumbezeichnung'].'</option>'."\n";
  }
  mysql_free_result($q);
  
  echo '</select>';
  echo '<br />';
  echo '<label>Art</label> ';
  echo '<select name="Art[]" multiple="multiple" size="5">';
  $q = mysql_query('SELECT Art_id,Art FROM T_Inventararten ORDER BY Art');
  while ( $Art = mysql_fetch_array($q) ) 
  {
  	echo '<option value="'.$Art['Art_id'].'" ';
  	if ( !isset($_SESSION['InvListeArt']) || in_array($Art['Art_id'], 
  	  $_SESSION['InvListeArt']))
  	  echo 'selected="selected"';
    echo '>'.$Art['Art'].'</option>'."\n";
  }
  mysql_free_result($q);
  
  echo '</select>';
  echo '<br />';
  echo '</form>';
  echo '</td></tr>';
}
elseif ( isset($_REQUEST['Buecher']) )
{
  $Art = "Bücher";
  echo '<tr><td>';
  echo '<table class="Liste">';
  $Search = '1';
  $Order = '';
  if ( isset($_REQUEST['Sort']) )
    $Order = mysql_real_escape_string($_REQUEST['Sort']).',';
  $Order .= 'Bezeichnung, Inventar_Nr';
  if ( isset($_REQUEST['Search'] ) && $_REQUEST['Search'] != '' )
  {
    $Search .= " AND (Bezeichnung REGEXP '".mysql_real_escape_string($_REQUEST["Search"])."' OR ".
      "Seriennummer='".mysql_real_escape_string($_REQUEST["Search"])."' OR Raumnummer='".
      mysql_real_escape_string($_REQUEST["Search"])."' OR Inhalt LIKE '".
      mysql_real_escape_string($_REQUEST["Search"])."')";
  }
  if ( ! $query = mysql_query("SELECT * FROM ((T_Inventar INNER JOIN T_Raeume ".
      "ON F_Raum_id = Raum_id) INNER JOIN T_Inventardaten ON ".
      "F_Inventar_id=Inventar_id) INNER JOIN ".
      "T_Inventardatenarten ON T_Inventardaten.F_Art_id=T_Inventardatenarten.Art_id ".
      "WHERE T_Inventar.F_Art_id=4 AND ($Search) ".
      "ORDER BY $Order")) echo "Fehler: ".mysql_error();
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
  if ( $_REQUEST['Buecher'] == 1 )
  {
    if ( $_REQUEST['Buecher'] == 1 )
    echo '<tr><th>Inventarnr</th><th><a href="'.$_SERVER['PHP_SELF'].
    '?Sort=Bezeichnung&Buecher=1" title="Sortieren nach Bezeichnung">';
    echo 'Bezeichnung</a></th>';
    echo '<th>Menge</th><th><a href="'.$_SERVER["PHP_SELF"].'?Sort=Raumnummer&Buecher=1">';
    echo 'Raum</a></th>';
    echo '<th><a href="'.$_SERVER["PHP_SELF"].'?Sort=Herstellungsjahr&Buecher=1">Jahr</a></th>';
    echo '<th><a href="'.$_SERVER["PHP_SELF"].
    '?Sort=Seriennummer&Buecher=1">ISBN</a>';
    echo '</th><th><a href="'.$_SERVER["PHP_SELF"].
    '?SortI=Verlag&Buecher=1">Verlag</a>';
    echo '</th><th><a href="'.$_SERVER["PHP_SELF"].
    '?SortI=Fachbereich&Buecher=1">Fachbereich</a></th>';
    echo '</tr>';
    foreach ( $Buecher as $buch )
    {
      echo '<tr><td><a href="Inventar.php?id='.$buch["Inventar_id"].'">';
      if ( trim(stripslashes($buch["Inventar_Nr"])) != "" )
        echo stripslashes($buch["Inventar_Nr"]);
      else
        echo "(unbekannt)";
      echo '</a></td><td>';
      echo stripslashes($buch['Bezeichnung']).'</td>';
      echo '<td>'.$buch['Menge'].'</a></td>';
      echo '<td>'.stripslashes($buch['Raumnummer']).'</td>';
      echo '<td>'.stripslashes($buch['Herstellungsjahr']).'</td>';
      echo '<td>'.stripslashes($buch['Seriennummer']).'</td>';
      echo '<td>'.stripslashes($buch['Verlag']).'</td>';
      echo '<td>'.stripslashes($buch['Fachbereich']).'</td>';
      echo '</tr>';
    }
  }
  else
  {
    echo '<tr><th><a href="'.$_SERVER['PHP_SELF'].
    '?Sort=Bezeichnung&Buecher=1" title="Sortieren nach Bezeichnung">';
    echo 'Bezeichnung</a></th>';
    echo '<th>Menge</th>';
    echo '<th><a href="'.$_SERVER['PHP_SELF'].'?Sort=Herstellungsjahr&Buecher=1">Jahr</a></th>';
    echo '<th><a href="'.$_SERVER['PHP_SELF'].
    '?Sort=Seriennummer&Buecher=1">ISBN</a>';
    echo '</th><th><a href="'.$_SERVER['PHP_SELF'].
    '?SortI=Verlag&Buecher=1">Verlag</a>';
    echo '</th>';
    echo '</tr>';
    foreach ( $Gesamt as $name => $menge )
    {
      foreach ( $Buecher as $buch )
        if ( $buch["Bezeichnung"] == $name )
        {
          echo '<tr><td>';
          echo stripslashes($buch['Bezeichnung']).'</td>';
          echo '<td>'.$menge.'</a></td>';
          echo '<td>'.stripslashes($buch['Herstellungsjahr']).'</td>';
          echo '<td>'.stripslashes($buch['Seriennummer']).'</td>';
          echo '<td>'.stripslashes($buch['Verlag']).'</td>';
          echo '</tr>';
          break;
        }
      }
  }
  mysql_free_result($query);
  echo '</table>';
  echo count($Buecher).' Einträge<br />';
  echo '<a href="Inventar.php?id=-1">Neues Inventar hinzufügen</a> / ';
  echo '<a href="'.$_SERVER["PHP_SELF"].'?Liste=E">Entsorgtes Inventar</a> / ';
  echo '<a href="'.$_SERVER["PHP_SELF"].'?Liste=O">unnummeriertes Inventar</a> <br />';
  echo '[ <a href="Inventar.php">Gesamtliste</a> ] ';
  echo '[ <a href="'.$_SERVER["PHP_SELF"].'?Buecher=1">Bücherlisten</a> ] ';
  echo '[ <a href="'.$_SERVER["PHP_SELF"].'?Buecher=2">Bücherliste Zusammenfassung</a> ]';
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  if ( isset($_REQUEST["Liste"]) )
    echo '<input type="hidden" name="Liste" value="'.$_REQUEST["Liste"].'" />';
  if ( ! isset($_REQUEST["Buecher"]) )
    echo '<input type="hidden" name="Art_id" value="'.$_REQUEST["Art_id"].'" />';
  else
    echo '<input type="hidden" name="Buecher" value="'.$_REQUEST["Buecher"].'" />';
  echo 'Inventar <input type="Text" name="Search" value="'.$_REQUEST["Search"].'" /> ';
  echo '<input type="Submit" value="Suchen" />';
  echo '</form>';
  echo '</td></tr>';
}
else
{
  echo '<tr><td>';
  $query = mysql_query('SELECT * FROM T_Inventardatenarten ORDER BY Art');
  while ( $art = mysql_fetch_array($query) )
  {
    echo '[ <a href="'.$_SERVER['PHP_SELF'].'?Art_id='.$art['Art_id'].'">'.$art['Art'].'</a> ]<br />';
  }
  mysql_free_result($query);
  echo '<br />';
  echo '[ <a href="'.$_SERVER['PHP_SELF'].'?Buecher=1">Bücherlisten</a> ]';
  echo '[ <a href="'.$_SERVER['PHP_SELF'].'?Buecher=2">Bücherliste Zusammenfassung</a> ]';
  echo '</td></tr>';
}
include('include/footer.inc.php');
?>