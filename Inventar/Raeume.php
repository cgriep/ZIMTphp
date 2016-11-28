<?php
/*
 * Raeume.php
 * Ermöglicht das Editieren der Raumdaten
 * (c) 2006 Christoph Griep
 * 
 */
$Ueberschrift = 'Räume';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');
include('inventar.inc.php');
echo '<tr><td>';  
  
if ( isset($_REQUEST['ip']))
{
  // Speichern
  $ip = $_REQUEST['ip'];
  $ip['Raumnummer'] = trim(str_replace(' ','',$ip['Raumnummer']));
  if ( $ip['Raumnummer'] == '' ) $ip['Raumnummer'] = '(unbekannt)';
  if ( ! is_numeric($ip['Kapazitaet']) ) $ip['Kapazitaet'] = 0;
  if ( isset($ip['Langfristig'])) 
    $ip['Langfristig'] = 1;
  else
    $ip['Langfristig'] = 0;  
  if ( isset($ip['Reservierbar'])) 
    $ip['Reservierbar'] = 1;
  else
    $ip['Reservierbar'] = 0;
  if ( isset($ip['Raum_id']) && is_numeric($ip['Raum_id']) )
  {
    // update
    $sql = 'UPDATE T_Raeume SET Raumbezeichnung="'.addslashes($ip['Raumbezeichnung']).'",';
    $sql .= 'Raumnummer="'.mysql_real_escape_string($ip['Raumnummer']).'",';
    $sql .= 'Verantwortlich="'.mysql_real_escape_string($ip['Verantwortlich']).'",';
    $sql .= 'Beschreibung="'.mysql_real_escape_string($ip['Beschreibung']).'",';
    $sql .= "Bearbeiter='".$_SERVER['REMOTE_USER']."',";
    $sql .= 'Kapazitaet='.$ip['Kapazitaet'].',';
    $sql .= "Verwendung='".$ip['Verwendung']."',";
    $sql .= 'Langfristig='.$ip['Langfristig'].',';
    $sql .= 'Reservierbar='.$ip['Reservierbar'].',';
    $sql .= 'Reservierungsberechtigung="'.
        mysql_real_escape_string(str_replace(',',"\n",$ip['Reservierungsberechtigung'])).'"';
    $sql .= ' WHERE Raum_id='.$ip['Raum_id'];
  }
  else
  {
    // Neuer Raum
    $sql = 'INSERT INTO T_Raeume (Raumbezeichnung,Raumnummer,Verantwortlich,'.
      'Beschreibung,Bearbeiter,Kapazitaet,Verwendung,Reservierbar,Langfristig,' .
      'Reservierungsberechtigung)';
    $sql .= " VALUES ('";
    $sql .= mysql_real_escape_string($ip["Raumbezeichnung"])."','".
            mysql_real_escape_string($ip['Raumnummer'])."','";
    $sql .= mysql_real_escape_string($ip['Verantwortlich'])."','".
            mysql_real_escape_string($ip['Beschreibung'])."','";
    $sql .= $_SERVER['REMOTE_USER']."',".$ip['Kapazitaet'].",'".
      mysql_real_escape_string($ip['Verwendung'])."',{$ip['Reservierbar']},{$ip['Langfristig']},'" .
      		mysql_real_escape_string(str_replace(',',"\n",$ip['Reservierungsberechtigung']))."')";
  }
  if (! mysql_query($sql) ) echo '<div class="Fehler">Fehler: '.$sql.': '.mysql_error().'</div>';
  if ( ! is_numeric($ip['Raum_id'] ) )
    $_REQUEST['id'] = mysql_insert_id();
  else
    $_REQUEST['id'] = $ip['Raum_id'];
  echo '<div class="Hinweis">&gt;&gt;&gt; Raum '.$ip['Raumnummer'].' gespeichert.</div>';
}

if ( isset($_REQUEST['id']) )
{
  $query = mysql_query('SELECT * FROM T_Raeume WHERE Raum_id = '.$_REQUEST['id']);
  if ( ! $raum = mysql_fetch_array($query) )
  {
    unset($raum);
    $raum['Raumbezeichnung'] = '';
    $raum['Raumnummer'] = '';
    $raum['Verantwortlich'] = '';
    $raum['Verwendung'] = '';
    $raum['Kapazitaet'] = 0;
    $raum['Beschreibung'] = '';
    $raum['Langfristig'] = 0;
    $raum['Reservierungsberechtigung'] = '';
    $raum['Bearbeiter'] = $_SERVER['REMOTE_USER'];
    $raum['Stand'] = date('d.m.Y H:i');
    $raum['Reservierbar'] = false;
  }   
  mysql_free_result($query);
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="Raum" class="Formular">';
  echo '<label for="Bezeichnung">Bezeichnug</label> ';
  echo '<input type="Text" name="ip[Raumbezeichnung]" id="Bezeichnung" value="'.
      htmlentities($raum['Raumbezeichnung']).'" size="50" maxlength="50" />';
  echo '<br/>';
  echo '<label for="Raumnummer">Raumnummer</label> ';
  echo '<input type="Text" name="ip[Raumnummer]" id="Raumnummer" value="'.
     stripslashes($raum['Raumnummer']).'" size="10" maxlength="10" />';
  echo '<br />';
  echo '<label for="Verantwortlich">Verantwortlich</label>';
  echo '<input type="Text" name="ip[Verantwortlich]" id="Verantwortlich" value="'.
     htmlentities($raum['Verantwortlich']);
  echo '" size="20" maxlength="20" /><br />';
  echo '<label for="Verwendung">Verwendung</label> ';
  echo '<input type="Text" name="ip[Verwendung]" id="Verwendung" value="'.
     htmlentities($raum['Verwendung']).
    '" size="50" maxlength="100" />';
  echo "<br/>\n";
  echo '<label for="Kapazitaet">Kapazität</label> ';
  echo '<input type="Text" name="ip[Kapazitaet]" id="Kapazitaet" value="'.
     $raum['Kapazitaet'].
    '" size="2" maxlength="3" />';
  echo "<br/>\n";  
  echo '<label for="Beschreibung">Beschreibung</label>';
  echo '<textarea name="ip[Beschreibung]" cols="60" rows="5">';
  echo stripslashes($raum['Beschreibung']);
  echo '</textarea>';
  echo '<fieldset><legend>Online-Reservierungssystem</legend>';
  echo '<label for="Reservierbar">Reservierbar</label> ';
  echo '<input type="checkbox" name="ip[Reservierbar]" id="Reservierbar" value="1" ';
  if ( $raum['Reservierbar']) echo 'checked="checked"';
  echo '/> (wenn der Raum online reservierbar sein soll)';
  echo "<br />\n";
  echo '<label for="Langfristig">Langfristig reservierbar</label> ';
  echo '<input type="checkbox" name="ip[Langfristig]" id="Langfristig" value="1" ';
  if ( $raum['Langfristig']) echo 'checked="checked"';
  echo '/> (wenn der Raum auch mehr als 3 Wochen im Voraus reservierbar sein soll)';
  echo "<br />\n";
  echo '<label for="Reservierungsberechtigung">Reservierungsberechtigte</label>';
  echo '<textarea name="ip[Reservierungsberechtigung]" id="Reservierungsberechtigung">';
  echo $raum['Reservierungsberechtigung'];
  echo '</textarea> (Liste an EMail(user)namen ohne @... wer ' .
  		'online reservieren darf,freilassen für alle. Trennen mit Komma oder neue Zeile)<br />';  
  echo '</fieldset>';
  
  echo '<div class="small">Stand '.$raum['Stand'].' / Bearbeiter '.$raum['Bearbeiter'];
  echo '</div>';
  if ( isset($raum['Bearbeiter']) && ! user_berechtigt($raum['Bearbeiter']) )
    echo 'Sie sind nicht berechtigt, diese Daten zu ändern.';
  else
    echo '<input type="Submit" value="Speichern" />';
  echo '&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?id=-1">Neuer Raum</a> / ';
  if ( isset($raum['Raum_id']) && $raum['Raum_id'] > 0)
  {
    echo '<input type="hidden" name="ip[Raum_id]" value="'.$raum['Raum_id'];
    echo '" /><br />';
  }  
  echo '</form>';
  // Zugehörige Informationen speichern
  if ( isset($raum['Raum_id']) && is_numeric($raum['Raum_id']) )
  {
    echo '<h2>Zugehöriges Inventar in '.$raum['Raumnummer'].'</h2>';
    $sql = 'SELECT * FROM T_Inventar INNER JOIN T_Inventararten ON F_Art_id=Art_id '.
      'WHERE F_Raum_id='.$raum['Raum_id'].' ORDER BY Bezeichnung';
    if ( ! $query = mysql_query($sql)) 
      echo '<div class="Fehler">Fehler '.$sql.': '.mysql_error().'</div>';
    echo '<table class="Liste">';
    echo '<tr><th>Bezeichnung</th><th>Art</th><th>Anschaffung</th><th>Inventarnr</th><th>Seriennummer</th>';
    echo '<th>Gewährleistung<br />bis</th><th>Reparaturen</th></tr>';
    while ( $daten = mysql_fetch_array($query) )
    {
      echo '<tr><td><a href="Inventar.php?id='.$daten['Inventar_id'].'">';
      echo stripslashes($daten['Bezeichnung']).'</a></td><td>';
      echo stripslashes($daten['Art']).'</td><td>';
      if ( $daten['Anschaffungsdatum'] != 0 )
        echo date('d.m.Y',$daten['Anschaffungsdatum']);
      echo '</td><td>';
      echo stripslashes($daten['Inventar_Nr']).'</td><td>';
      echo stripslashes($daten['Seriennummer']).'</td><td ';
      if ( isset($daten['Gewaehrleistungsdatum']) && $daten['Gewaehrleistungsdatum'] != 0 )
      {
        if ( $daten['Gewaehrleistungsdatum'] < time() )
          echo ' bgcolor="red">';
        else
          echo ' bgcolor="green">';
        echo date('d.m.Y',$daten['Gewaehrleistungsdatum']);
      }
      else echo '>';
      echo '</td><td align="center">';
      $qu = mysql_query('SELECT Count(*) FROM T_Reparaturen WHERE F_Inventar_id='.$daten['Inventar_id']);
      $anz = mysql_fetch_row($qu);
      mysql_free_result($qu);
      if ( ! is_numeric($anz[0]) ) $anz[0] = 0;
      echo $anz[0];
      echo '</td></tr>';
    }
    echo '</table>';
    if ( mysql_num_rows($query) > 0 && ! isset($_REQUEST['Print']))
      echo '<a href="LabelPDF.php?Raum='.$raum['Raum_id'].
        '" class="nichtDrucken">Inventarlabels drucken</a>';
    mysql_free_result($query);    
    echo '<hr class="nichtDrucken"/>';
    echo '<div class="nichtDrucken"><a href="'.$_SERVER["PHP_SELF"];
    echo '">zur Übersichtsliste der Räume</a></div>';
  }
}
else
{
  // Liste der Räume
  echo '<table class="Liste">';
  echo '<tr><th>Raum</th><th>Bezeichnung</th><th>Verantwortlich</th><th>Beschreibung</th>';
  echo '<th>Verwendung</th><th>Kapazität</th></tr>';
  $Search = '';
  if ( isset($_REQUEST['Search'] ) && $_REQUEST['Search'] != '' )
  {
    $Search = "WHERE Raumnummer REGEXP '".mysql_real_escape_string($_REQUEST['Search'])."' OR ";
    $Search .= "Raumbezeichnung REGEXP '".mysql_real_escape_string($_REQUEST['Search'])."' OR ";
    $Search .= "Verantwortlich REGEXP '".mysql_real_escape_string($_REQUEST['Search'])."' OR ";
    $Search .= "Beschreibung REGEXP '".mysql_real_escape_string($_REQUEST['Search'])."'";
  }
  $query = mysql_query("SELECT * FROM T_Raeume $Search ORDER BY Raumnummer");
  while ( $inv = mysql_fetch_array($query) )
  {
    echo '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?id='.$inv['Raum_id'].'">';
    if ( trim(stripslashes($inv['Raumnummer'])) != '' )
      echo stripslashes($inv['Raumnummer']);
    else
      echo '(unbekannt)';
    echo '</a></td><td>'.stripslashes($inv['Raumbezeichnung']);
    echo '</td><td>'.stripslashes($inv['Verantwortlich']);
    echo '</td><td>'.nl2br(stripslashes($inv['Beschreibung']));
    echo '</td><td>'.$inv['Verwendung'];
    echo '</td><td>'.$inv['Kapazitaet'];
    echo '</td></tr>';
  }
  mysql_free_result($query);
  echo '</table>';
  echo '<a href="'.$_SERVER['PHP_SELF'].'?id=-1">Neuen Raum hinzufügen</a> / ';
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" class="Formular">';
  if ( isset($_REQUEST['Liste']) )
    echo '<input type="hidden" name="Liste" value="'.$_REQUEST['Liste'].'" />';
  if ( ! isset($_REQUEST['Search'])) 
    $_REQUEST['Search'] = '';
  echo 'Raum <input type="Text" name="Search" value="'.$_REQUEST['Search'].'" /> ';
  echo '<input type="Submit" value="Suchen" />';
  echo '</form>';
  
}
echo '</td></tr>';
include('include/footer.inc.php');
?>