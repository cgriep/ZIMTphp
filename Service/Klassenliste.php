<?php
/**
 * Zeigt eine Liste von Schülern eine Klasse an. 
 * (c) 2006 Christoph Griep
 */
if ( ! isset($_REQUEST['PDF']) )
{
  $Ueberschrift = 'Klassen/Kursliste anzeigen';
  $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
  include('include/header.inc.php');
  include('include/turnus.inc.php');
  include('include/stupla.inc.php');

  echo '<tr><td>';
  if ( isset($_REQUEST['Lehrer']) )
  {
    echo '<h2>Tutanden von '.$_REQUEST['Lehrer'];
    echo '</h2';
    echo '<table class="Liste">';
    echo '<tr><th>Nr</th><th>Name</th><th>Vorname</th>';
    echo '<th>Geburtsdatum</th><th>Bemerkung</th>';
    echo '<th>Klasse</th><th></th>';
    if ( ! $query = mysql_query('SELECT Nr, Name, Vorname, Geburtsdatum, Bemerkung, Klasse FROM T_Schueler '.
       'WHERE Tutor = "'.mysql_real_escape_string($_REQUEST['Lehrer']).
        '" ORDER BY Name, Vorname', $db))
          echo mysql_error($db);
    while ($s = mysql_fetch_array($query) )
    {
      echo '<tr><td>'.$s['Nr'].'</td><td>'.$s['Name'].'</td><td>'.$s['Vorname'].'</td>';
      echo '<td>'.date('d.m.Y',$s['Geburtsdatum']).'</td><td>'.$s['Bemerkung'].'</td>';
      echo '<td>'.$s['Klasse'].'</td>';
    }
    if ( mysql_num_rows($query) == 0 )
      echo '<tr><td colspan="6" align="center"><strong>Keine Namen verfügbar</strong></td></tr>';
    else
      echo '<tr><td colspan="6"><strong>'.mysql_num_rows($query).' SchülerInnen</strong></td></tr>';
    mysql_free_result($query);
    echo '</td></tr></table>';
    echo '<div class="Hinweis">Stand der Daten: ';
    $query = mysql_query('SELECT * FROM T_Stand', $db);
    $stand = mysql_fetch_array($query);
    mysql_free_result($query);
    echo date('d.m.Y H:i',$stand['Stand']);
    echo '</div';
    echo '<hr />';
  }
  if ( ! isset($_REQUEST['Schuljahr'])) $_REQUEST['Schuljahr'] = '';
  if ( (isset($_REQUEST['Kurs']) || isset($_REQUEST['Klasse'])) )
  {    
    $Schuljahr = mysql_real_escape_string($_REQUEST['Schuljahr']);
    if ($Schuljahr == '' )
    {
      $Schuljahr = Schuljahr(false);
    }
    echo '<h2>Teilnehmer de';
    if ( isset($_REQUEST['Kurs']) )
      echo 's Kurses '.$_REQUEST['Kurs'].' ('.$Schuljahr.')';
    else
      echo 'r Klasse '.$_REQUEST['Klasse'].' ('.$Schuljahr.')';
    echo '</h2>';
    echo '<table class="Liste">';
    echo '<tr><th>Nr</th><th>Name</th><th>Vorname</th>';
    if ( isset($_REQUEST['Klasse'] ))
      echo '<th>Geburtsdatum</th><th>Bemerkung</th>';    
    echo '<th>Tutor</th></tr>';
    if ( isset($_REQUEST['Kurs']) )
    {
      if ( ! $query = mysql_query('SELECT Nr, Name, Vorname, Tutor FROM T_Schueler INNER JOIN T_Kurse '.
        'ON T_Schueler.Nr = T_Kurse.Schueler_id WHERE BINARY Kurs = "'.
        mysql_real_escape_string($_REQUEST['Kurs']).
        '" AND Schuljahr = "'.$Schuljahr.'" ORDER BY Name, Vorname', $db))
          echo mysql_error($db);
    }
    else
    {
      if ( ! $query = mysql_query('SELECT Nr, Name, Vorname, Tutor, Geburtsdatum, Bemerkung FROM T_Schueler '.
        'WHERE BINARY Klasse = "'.mysql_real_escape_string($_REQUEST['Klasse']).
        '" ORDER BY Name, Vorname', $db))
          echo mysql_error($db);
    }
    while ($s = mysql_fetch_array($query) )
    {
      echo '<tr><td>'.$s['Nr'].'</td><td>'.$s['Name'].'</td><td>'.$s['Vorname'].'</td>';
      if ( isset($_REQUEST['Klasse'] ) )
        echo '<td>'.date('d.m.Y',$s['Geburtsdatum']).'</td><td>'.$s['Bemerkung'].'</td>';
      echo '<td>'.$s['Tutor'].'</td></tr>';
    }
    if ( mysql_num_rows($query) == 0 )
      echo '<tr><td colspan="6" align="center"><strong>Keine Namen verfügbar</strong></td></tr>';
    else
      echo '<tr><td colspan="6"><strong>'.mysql_num_rows($query).' SchülerInnen</strong></td></tr>';
    mysql_free_result($query);
    echo '</td></tr>';
    echo '</table>';
    if ( isset($_REQUEST['Kurs']) )
    {
      echo '<a href="'.$_SERVER['PHP_SELF'].'?PDF=1&Klasse='.
        $_REQUEST['Kurs'].'&Schuljahr='.$_REQUEST['Schuljahr'];
      echo '" target="_blank">Namensliste</a> (zum Einkleben in das Kursheft)<br />';
      echo '<a href="'.$_SERVER['PHP_SELF'].'?PDF=1&Klasse='.
        $_REQUEST['Kurs'].'&Schuljahr='.$_REQUEST['Schuljahr'];
    }
    else
    {
      echo '<a href="'.$_SERVER['PHP_SELF'].'?PDF=2&Klasse='.
        $_REQUEST['Klasse'].'&Schuljahr='.$_REQUEST['Schuljahr'];
      echo '" target="_blank">Namensliste</a> (zum Einkleben ins Klassenbuch)<br />';
      echo '<a href="'.$_SERVER['PHP_SELF'].'?PDF=2&Klasse='.
        $_REQUEST['Klasse'].'&Schuljahr='.$_REQUEST['Schuljahr'];
    }
    echo '&Excel=1">Excel-Tabelle mit den Daten</a><br />';
    if ( isset($_REQUEST['Klasse']) )
    {
      echo '<a href="Kollegenliste.php?Klasse='.$_REQUEST['Klasse'].
        '">unterrichtende KollegInnen der Klasse</a><br />';
    }
    echo '<div class="Hinweis">Stand der Daten: ';
    $query = mysql_query('SELECT * FROM T_Stand', $db);
    $stand = mysql_fetch_array($query);
    mysql_free_result($query);
    echo date('d.m.Y H:i',$stand['Stand']);
    echo '</div>';
    echo '<hr />';
  }
  if ( isset($_REQUEST['Kursbelegung']) )
  {
    $Schuljahr = mysql_real_escape_string($_REQUEST['Schuljahr']);
    if ($Schuljahr == '' )
    {
      $Schuljahr = Schuljahr(false);
    }
    echo '<h2>Übersicht über die Kursbelegung ';
    echo $_REQUEST['Kursbelegung'].' im Schuljahr ';
    echo $Schuljahr.'</h2>';        
    if ( ! $query = mysql_query('SELECT Nr, Name, Vorname, Tutor, Fach, Kurs FROM T_Schueler INNER JOIN T_Kurse '.
        'ON T_Schueler.Nr = T_Kurse.Schueler_id WHERE Schuljahr="'.
        mysql_real_escape_string($Schuljahr).
        '" AND Klasse="'.mysql_real_escape_string($_REQUEST['Kursbelegung']).
        '" ORDER BY Name, Vorname, Fach', $db))
          echo mysql_error($db);
    $Kurse = array();
    $Belegung = array();
    while ($s = mysql_fetch_array($query) )
    {
      if ( ! in_array($s['Fach'], $Kurse) ) $Kurse[] = $s['Fach'];
      $Belegung[] = $s;
    }
    mysql_free_result($query);
    echo '<table class="Liste">';
    echo '<tr><th>Name</th><th>Vorname</th><th>Tutor</th>';
    // Kurse
    reset($Kurse);
    foreach ( $Kurse as $value )
    {
      echo '<th>'.$value."</th>\n";
    }
    echo "</tr>\n";
    $Name = '';
    $Vorname = '';
    $Tutor = '';
    $Zeile = '';
    reset($Belegung);
    $Kurslehrer = array();
    $Version = getAktuelleVersion(time());
    foreach ( $Belegung as $value )
    {
      if ( $Name != $value['Name'] || $Vorname != $value['Vorname'] ||
           $Tutor != $value['Tutor'] )
      {
        while ( strpos($Zeile,'%%') !== false )
          $Zeile = substr($Zeile, 0, strpos($Zeile,'%%')-1).substr($Zeile,strpos($Zeile,'%%')+6);
        if ( $Name != '' ) echo $Zeile;
        $Zeile = '<tr class="mitRahmen"><td>'.$value['Name'].'</td><td>'.$value['Vorname'].'</td><td>';
        $Zeile .= $value['Tutor'].'</td>';
        reset($Kurse);
        foreach ( $Kurse as $kurs )
          $Zeile .= '<td>%%'.$kurs."%%</td>\n";
        $Zeile .= "</tr>\n";
        $Name = $value['Name'];
        $Vorname = $value['Vorname'];
        $Tutor = $value['Tutor'];
      }
      // Lehrer feststellen
      if ( ! isset($Kurslehrer[$value['Kurs']]))
      {
        $query = mysql_query('SELECT Lehrer FROM T_StuPla WHERE Klasse="'.
          mysql_real_escape_string($_REQUEST['Kursbelegung']).'" AND Fach ="'.
          $value['Kurs'].'" AND Version = '.$Version);
        $row = mysql_fetch_array($query);
        $Kurslehrer[$value['Kurs']] = $row['Lehrer'];
        mysql_free_result($query);
      }
      $Zeile = str_replace('%%'.$value['Fach'].'%%',$value['Kurs'].' '.
        $Kurslehrer[$value['Kurs']],$Zeile);
    }
    while ( strpos($Zeile,'%%') !== false )
          $Zeile = substr($Zeile, 0, strpos($Zeile,'%%')-1).substr($Zeile,strpos($Zeile,'%%')+6);
    echo $Zeile;
    echo '</table>';
    $query = mysql_query('SELECT * FROM T_Stand', $db);
    $stand = mysql_fetch_array($query);
    mysql_free_result($query);
    echo '<div class="Hinweis">Stand der Daten: '.date('d.m.Y H:i',$stand['Stand']).'</div>';
    echo '<hr />';
  }
  echo '<div class="home-content-titel">Klassen aller Abteilungen</div>';
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
  $Version = getAktuelleVersion();
  echo 'Kurs <select name="Klasse">';
  $query = mysql_query('SELECT DISTINCT Klasse FROM T_StuPla WHERE Version='.
     $Version.' ORDER BY Klasse', $db);
  $OGKlassen = array();
  while ( $fach = mysql_fetch_row($query) )
  {
    echo '<option>'.$fach[0].'</option>';
    if ( ereg('OG [0-9]$',$fach[0]) )
      $OGKlassen[] = $fach[0];
  }
  mysql_free_result($query);
  /*
  $query = mysql_query("SELECT DISTINCT Klasse FROM T_Auswaertsklassen ORDER BY Klasse", $db);
  while ( $fach = mysql_fetch_row($query) )
  {
    echo '<option>'.$fach[0].'</option>';
  }
  */
  echo '</select><input type="Submit" value="anzeigen">';
  echo '</form>';
  echo '<hr />';
  echo '<div class="home-content-titel">Kurse der OG</div>';
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
  echo '<input type="hidden" name="Schuljahr" value="" />';
  echo 'Kurs <select name="Kurs">';
  if ( ! $query = mysql_query('SELECT DISTINCT Kurs FROM T_Kurse ORDER BY Kurs', $db))
    echo mysql_error();
  while ( $fach = mysql_fetch_row($query) )
  {
    echo '<option>'.$fach[0].'</option>';
  }
  mysql_free_result($query);
  $Schuljahr = Schuljahr(false);
  echo '</select> Schuljahr <select name="Schuljahr">';
  $query = mysql_query("SELECT DISTINCT Schuljahr FROM T_Kurse ORDER BY Schuljahr", $db);
  while ( $fach = mysql_fetch_row($query) )
  {
    echo '<option';
    if ( ! ( strpos($fach[0], $Schuljahr) === false) )
      echo ' selected="selected"';
    echo '>'.$fach[0].'</option>';
  }
  mysql_free_result($query);
  echo '</select> <input type="Submit" value="anzeigen" />';
  echo '</form>';
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  echo 'Kursbelegung Jahrgang <select name="Kursbelegung">';
  foreach ($OGKlassen as $value )
    echo '<option>'.$value.'</option>';
  $Schuljahr = Schuljahr(false);
  echo '</select> Schuljahr <select name="Schuljahr">';
  $query = mysql_query("SELECT DISTINCT Schuljahr FROM T_Kurse ORDER BY Schuljahr", $db);
  while ( $fach = mysql_fetch_row($query) )
  {
    echo '<option';
    if ( ! ( strpos($fach[0], $Schuljahr) === false) )
      echo ' selected="selected"';
    echo '>'.$fach[0].'</option>';
  }
  mysql_free_result($query);
  echo '</select> <input type="Submit" value="anzeigen" />';
  echo '</form>';
  echo '<hr />';
  echo '<div class="home-content-titel">Tutanden</div>';
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  echo 'Lehrer <select name="Lehrer">';
  if ( ! $query = mysql_query("SELECT DISTINCT Tutor FROM T_Schueler WHERE Klasse LIKE 'OG _' ORDER BY Tutor", $db))
       echo mysql_error($db);
  while ($s = mysql_fetch_array($query) )
  {
    if ( trim($s["Tutor"]) != "" )
    {
      echo '<option value="'.$s["Tutor"].'" ';
      if ( ! (strpos($_SERVER["REMOTE_USER"], $s["Tutor"]) === false ) )
        echo 'selected="selected"';
      echo '>';
      $qu = mysql_query("SELECT Name, Vorname FROM T_StuPla WHERE Lehrer='".$s["Tutor"]."'");
      if ( $lehrer = mysql_fetch_array($qu) )
        echo $lehrer["Name"].", ".$lehrer["Vorname"];
      else
        echo $s["Tutor"];
      echo '</option>';
    }
  }
  mysql_free_result($query);
  echo '</select> <input type="Submit" value="anzeigen" />';
  echo '</form>';
  
  echo '</td></tr>';  
  include('include/footer.inc.php');
}
elseif ( isset($_REQUEST['Klasse']) && isset($_REQUEST['Schuljahr']))
{
  include('include/config.php');
  include('include/pdf.inc.php');
  $Tabelle1 = array();
  $Tabelle2 = array();
  $Schuljahr = $_REQUEST['Schuljahr'];
  switch ( $_REQUEST['PDF'] )
  {
    case 1:
       $sql = "SELECT Nr, Name, Vorname, Tutor FROM T_Schueler INNER JOIN T_Kurse ".
        "ON T_Schueler.Nr = T_Kurse.Schueler_id WHERE BINARY Kurs = '".
        mysql_real_escape_string($_REQUEST["Klasse"]).
         "' AND Schuljahr = '$Schuljahr' ORDER BY Name, Vorname";
        if ( ! $query = mysql_query($sql, $db))
          echo mysql_error($db);
      break;
    case 2:
          if ( ! $query = mysql_query('SELECT Nr, Name, Vorname, Geburtsdatum, Bemerkung FROM T_Schueler '.
          "WHERE BINARY Klasse = '".mysql_real_escape_string($_REQUEST['Klasse']).
          "' ORDER BY Name, Vorname",$db))
            echo mysql_error($db);
          $Tabelle1[0][0]['value'] = 'Nr';
          $Tabelle1[0][1]['value'] = 'Name';
          $Tabelle1[0][2]['value'] = 'Vorname';
          $Tabelle1[0][3]['value'] = 'Geb-Datum';
          $Tabelle1[0][4]['value'] = 'Bemerkung';
          //$Tabelle1[0][5]['value'] = 'Tutor';
  } // switch
  while ( $schueler = mysql_fetch_array($query) )
  {
    if ( $_REQUEST['PDF'] == 1 )
    {
      $Tabelle1[Count($Tabelle1)][0]['value'] = $schueler['Tutor'];
      $Tabelle2[Count($Tabelle2)][0]['value'] = $schueler['Name'].', '.$schueler['Vorname'];
    }
    elseif ( $_REQUEST['PDF'] == 2 )
    {
      $Tabelle1[Count($Tabelle1)][0]['value'] = Count($Tabelle1);
      $Tabelle1[Count($Tabelle1)-1][1]['value'] = $schueler['Name'];
      $Tabelle1[Count($Tabelle1)-1][2]['value'] = $schueler['Vorname'];
      $Tabelle1[Count($Tabelle1)-1][3]['value'] = date('d.m.Y',$schueler['Geburtsdatum']);
      $Tabelle1[Count($Tabelle1)-1][4]['value'] = $schueler['Bemerkung'];
      $Tabelle1[Count($Tabelle1)-1][0]['Breite'] = 20;
      $Tabelle1[Count($Tabelle1)-1][1]['Breite'] = 133;
      $Tabelle1[Count($Tabelle1)-1][2]['Breite'] = 79;
      $Tabelle1[Count($Tabelle1)-1][3]['Breite'] = 79;
      if ( (Count($Tabelle1)-2) % 5 == 0 && Count($Tabelle1) > 2 )
        $Tabelle1[Count($Tabelle1)-1][0]['Hoehe'] = 20;
      else
        $Tabelle1[Count($Tabelle1)-1][0]['Hoehe'] = 17;

      //$Tabelle1[Count($Tabelle1)-1][5]['value'] = $schueler['Tutor'];
    }
  }
  mysql_free_result($query);
  mysql_close();
  if ( !isset($_REQUEST['Excel'] ) )
  {
    $p = PDF_new();
    PDF_open_file($p,'');
    PDF_set_info($p, 'Creator', 'OSZIMT');
    PDF_set_info($p, 'Author', 'Christoph Griep');
    PDF_set_info($p, 'Title', 'Klassenliste');
    PDF_begin_page($p, 595, 842);
    $bfont = PDF_findfont($p, 'Helvetica-Bold', 'host', 0);
    $font = PDF_findfont($p, 'Helvetica', 'host', 0);
    PDF_setfont($p, $font, 8.0);
    if ( $_REQUEST['PDF'] == 1 )
    {
      // Kursheft-Liste
      pdf_rect($p,29,842-32-27*2,182,27*2);
      pdf_stroke($p);
      pdf_show_xy($p, 'Anwesenheitsliste', 33, 842-40);
      PDF_setfont($p, $bfont, 12.0);
      pdf_show_xy($p, 'Kurs '.$_REQUEST['Klasse'], 33, 842-30-27);
      PDF_setfont($p, $font, 8.0);
      pdf_show_xy($p, 'Namen der Schüler', 33, 842-28-27*2);
      PDF_setfont($p, $font, 14.0);
      pdf_Tabelle_xy($p,29,842-3*28.5-2,$Tabelle2,'left',142,22); //20.4);
      PDF_setfont($p, $font, 6.0);
      pdf_Tabelle_xy($p,29+148,842-3*28.5-2,$Tabelle1,'left',28,22); // 20.4);
    }
    elseif ( $_REQUEST['PDF'] == 2 )
    {      
      PDF_setfont($p, $font, 10.0);
      pdf_Tabelle_xy($p,29,842-3*28.5-2,$Tabelle1,'left'); //,-1,17);     
    }
    PDF_end_page($p);
    PDF_close($p);
    $buf = PDF_get_buffer($p);
    $len = strlen($buf);
    header('Content-type: application/pdf');
    header('Content-Length: '.$len);
    header('Content-Disposition: inline; filename=Klassenliste'.str_replace(' ','',
      $_REQUEST['Klasse']).'.pdf');
    print $buf;
    PDF_delete($p);
  }
  else
  {
    if ( $_REQUEST['PDF'] == 1 )
      $header = 'Kurs ';
    else
      $header = 'Klasse ';
    $header .= $_REQUEST['Klasse']."\tSchuljahr ".$_REQUEST['Schuljahr']."\n";
    if ( $_REQUEST['PDF'] == 1 )
      foreach ( $Tabelle2 as $key => $value )
      {
        $Namen = explode(',',$value[0]['value']);
        $header .= '"'.str_replace('"','""',$Namen[0]).'"'."\t";
        $header .= '"'.str_replace('"','""',$Namen[1]).'"'."\t";
        $header .= '"'.str_replace('"','""',$Tabelle1[$key][0]['value']).'"'."\n";
      }
    else
      foreach ( $Tabelle1 as $key => $value )
      {
        foreach ( $value as $kkey => $vvalue)
          $header .= '"'.str_replace('"','""',$vvalue['value']).'"'."\t";
        $header .= "\n";
      }
    $header = str_replace("\r", '', $header);
    # This line will stream the file to the user rather than spray it across the screen
    header('Content-type: application/octet-stream');
    # replace excelfile.xls with whatever you want the filename to default to
    header('Content-Disposition: inline; filename='.str_replace(' ','',
      $_REQUEST['Klasse']).'.xls');
    echo $header;
  }
}
?>