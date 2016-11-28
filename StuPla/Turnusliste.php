<?php
/*
 * Zeigt für ein komplettes Schuljahr die Turnusse an
 * Wahlweise nach einer Gruppe von Turnussen oder eine Gesamtübersicht über alle
 * Turnusse.
 */
$Monatsnamen[1] = 'Januar';
$Monatsnamen[2] = 'Februar';
$Monatsnamen[3] = 'März';
$Monatsnamen[4] = 'April';
$Monatsnamen[5] = 'Mai';
$Monatsnamen[6] = 'Juni';
$Monatsnamen[7] = 'Juli';
$Monatsnamen[8] = 'August';
$Monatsnamen[9] = 'September';
$Monatsnamen[10] = 'Oktober';
$Monatsnamen[11] = 'November';
$Monatsnamen[12] = 'Dezember';

/*
 * Erstellt ein PDF-Dokument mit dem gewählten Turnus
 */
if ( isset($_REQUEST['Turnus'] )&& is_numeric($_REQUEST['Turnus']) )
{
  include('include/config.php');
  include('include/pdf.inc.php');
  $p = PDF_new();
  PDF_open_file($p, ''); 
  PDF_set_info($p, 'Creator', 'OSZIMT');
  PDF_set_info($p, 'Author', 'Christoph Griep');
  PDF_set_info($p, 'Title', 'Wochenplan');
  PDF_begin_page($p, 595, 842);
  $Fonts = LadeFonts($p);
  $bfont = $Fonts['Arial']['N']; // PDF_findfont($p, 'Helvetica-Bold', 'host', 0);
  $Schuljahr = mysql_real_escape_string($_REQUEST['Schuljahr']);
  $Anfangsjahr = substr($Schuljahr,0,2)+2000;
  $Turnusse = $_REQUEST['Turnus'];
  $Legende[0][0]['value'] = 'Unterricht';
  $Legende[0][1]['value'] = 'Wochenende';
  $Legende[0][1]['Fuellung'] = 0.7;
  $Legende[0][2]['value'] = 'Unterrichtsfrei';
  $Legende[0][2]['Fuellung'] = 0.4;
  $turni = array();
  if ( $Turnusse < 0 )
  {
    $query = mysql_query("SELECT * FROM T_Turnus WHERE SJahr='$Schuljahr' " .
    		'ORDER BY F_ID_Gruppe, Turnus');
    while ( $turnus = mysql_fetch_array($query) )
    {
      $turni[] = $turnus['Turnus'];
      $Turnustabelle[0][Count($turni)]['value'] = $turnus['Turnus'];
      $Turnustabelle[0][Count($turni)]['Fuellung'] = 0.4;
    }
    mysql_free_result($query);
    $Turnustabelle[0][0]['Fuellung'] = 0.4;
    $Turnustabelle[0][0]['value'] = 'Woche';
  }
  else
  {
    $where = "WHERE F_ID_Gruppe=$Turnusse";
    $query = mysql_query('SELECT * FROM T_Turnus ' .
    		"WHERE F_ID_Gruppe=$Turnusse AND SJahr='$Schuljahr' ORDER BY Turnus");
    while ( $row = mysql_fetch_array($query)) 
      $turni[] = $row['Turnus'];
    mysql_free_result($query);  
  }
  
  foreach ( $turni as $key => $value )
  {
    $Tage[str_replace("'",'',$value)] = 0;
  }
  $sql = "SELECT Montag FROM T_Woche WHERE SJahr='$Schuljahr' ORDER BY Montag";
  $Monate = array();
  if ( ! $query = mysql_query($sql) ) echo "Fehler $sql: ".mysql_error();
  $Woche = 1;
  $Monat = 0;
  $ersterTag = 0;
  $letzterTag = 0;
  $Wochen = array();
  while ($row = mysql_fetch_array($query) )
  {
    $Tag = $row['Montag'];
    if ( $ersterTag == 0 ) $ersterTag = $Tag;
    if ( $letzterTag < $Tag ) $letzterTag = $Tag;
    if ( $Turnusse < 0 )
    {
      foreach ( $turni as $key => $value )
        $Turnustabelle[$Woche][$key+1]['value'] = '';
      $Turnustabelle[$Woche][0]['value'] = date('W',$Tag).' ('.date('d.m.Y',$Tag).
        '-'.date('d.m.Y',strtotime('+6 day', $Tag)).')';
      $Wochen[$Woche] = date('W',$Tag);
      $Woche++;
    }
    else
    {
      for ( $i = 1; $i < 8; $i++ )
      {
        $j = $i-1;
        $datum = strtotime("+$j day", $Tag);
        $PosErster = date('w', mktime(0,0,0,date('m',$datum),1,date('Y',$datum)));
        if ( $PosErster == 0 ) $PosErster = 7; // So ist letzter Tag
        $PosErster --; // Mo = 0; So = 6
        $Pos = date('d',$datum)+$PosErster;
        $Woche = ceil($Pos / 7);
        $Monat = date('m',$datum);
        $Monate[date('m',$datum)][$Woche][0]['value'] = ''; // $row['Kommentar'];
        $Monate[date('m',$datum)][$Woche][$i]['value'] = date('d',$datum);
      }
    } // Einzelne Turnusse zeigen
  }
  mysql_free_result($query);
  if ( $Turnusse >= 0 )
    $sql = 'SELECT Montag, Turnus FROM '.
    '(T_WocheTurnus INNER JOIN T_Turnus ON F_ID_Turnus = ID_Turnus) '.
    'INNER JOIN T_Woche ON ID_Woche=F_ID_Woche '.
    "WHERE (F_ID_Gruppe=$Turnusse) AND T_Woche.SJahr='$Schuljahr'".
    ' ORDER BY Montag, Turnus';
  else
    $sql = 'SELECT Montag, Turnus FROM '.
    '(T_WocheTurnus INNER JOIN T_Turnus ON F_ID_Turnus = ID_Turnus) '.
    'INNER JOIN T_Woche ON ID_Woche=F_ID_Woche '.
    "WHERE T_Woche.SJahr='$Schuljahr'".
    ' ORDER BY Montag, F_ID_Gruppe, Turnus';
  if ( ! $query = mysql_query($sql, $db)) echo "Fehler $sql: ".mysql_query($db);
  $Woche = 1;
  $Sommerferien[0] = 0;
  $Sommerferien[1] = 0;
  $Tabelle = array();
  while ( $row = mysql_fetch_array($query) )
  {
    if ( $Turnusse < 0 )
    {
      $Woche = array_search(date('W',$row['Montag']), $Wochen);
      $Turnustabelle[$Woche][array_search($row['Turnus'],$turni)+1]['value'] = 'X';
      $Tage[$row['Turnus']] += 5;
      for ( $i = 0; $i < 5; $i++)
      {
        $Tag = strtotime("+$i day", $row['Montag']);
        $ferien = mysql_query('SELECT Count(*) FROM T_FreiTage WHERE ersterTag <= '.
          $Tag.' AND letzterTag >= '.$Tag);
        if ( $wann = mysql_fetch_row($ferien) )
        {
          if ( $wann[0] > 0 ) $Tage[$row['Turnus']]--;
        }
        mysql_free_result($ferien);
      }
    }
    else
    {
      for ( $i= 0; $i < 7; $i++)
      {
        $datum = strtotime("+$i day",$row['Montag']);
        $Tabelle[$datum] = $row['Turnus'];
      }
    }
  }
  mysql_free_result($query);
  $Monat = 0;
  $Woche = 0;
  if ( $Turnusse >= 0 )
  {
    foreach ( $Tabelle as $datum => $value )
    {
      if ( $Monat != date('m', $datum) )
      {
        $Monat = date('m',$datum);
        $DatumErster = mktime(0,0,0,$Monat,1,date('Y',$datum));
        if ( $DatumErster < $ersterTag ) $ersterTag = $DatumErster;
        $Position = date('w', $DatumErster);
        if ( $Position == 0 ) $Position = 7;
        $Woche = 1;
        // Leerfelder zu Monatsbeginn
        $Monate[$Monat][1][0]['value'] = '';
        for ( $i = 1; $i < $Position; $i++)
          $Monate[$Monat][1][$i]['value'] = '';
        // Tage auffüllen
        for ( $i = 1; $i <= date('t', $datum); $i++ )
        {
          $Monate[$Monat][$Woche][$Position]['value'] = sprintf('%02d',$i);
          $Monate[$Monat][$Woche][0]['value'] = '';
          if ( $Position >= 6 ) $Monate[$Monat][$Woche][$Position]['Fuellung'] = 0.7;
          $Position++;
          if ( $Position > 7 )
          {
            $Position = 1;
            $Monate[$Monat][$Woche][8]['value'] = date('W',mktime(0,0,0,$Monat,$i,date('Y',$datum)));
            $Monate[$Monat][$Woche][8]['Fuellung'] = '0.9';
            $Woche++;
          }
        }
        $DatumLetzter = mktime(0,0,0,date('m',$datum),date('t', $datum),date('Y',$datum));
        if ($DatumLetzter > $letzterTag) $letzterTag = $DatumLetzter;
        // Monat 'gerade' machen
        if ( $Position > 1 )
        {
          for ($i = $Position; $i < 8; $i++ )
          {
            $Monate[$Monat][$Woche][$i]['value'] = '';
            if ( $i >= 6 ) $Monate[$Monat][$Woche][$i]['Fuellung'] = 0.7;
          }
          $Monate[$Monat][$Woche][8]['value'] = date('W',$DatumLetzter);
          $Monate[$Monat][$Woche][8]['Fuellung'] = '0.9';
        }
        // Neuer Monat
        $Taganzahl[$Monat] = date('t', $datum);
        $NeuerMonat = true;
        //$Monate[$Monat][0][0]['value'] = ''; wird weggelassen, damit kein Rahmen
        $Monate[$Monat][0][1]['value'] = 'Mo';
        $Monate[$Monat][0][2]['value'] = 'Di';
        $Monate[$Monat][0][3]['value'] = 'Mi';
        $Monate[$Monat][0][4]['value'] = 'Do';
        $Monate[$Monat][0][5]['value'] = 'Fr';
        $Monate[$Monat][0][6]['value'] = 'Sa';
        $Monate[$Monat][0][7]['value'] = 'So';
        $Monate[$Monat][0][8]['value'] = 'KW';
        $Monate[$Monat][0][6]['Fuellung'] = 0.7;
        $Monate[$Monat][0][7]['Fuellung'] = 0.7;
      }
      else
        $NeuerMonat = false;
      $Position = date('w', $datum);
      if ( $Position == 0 ) $Position = 7;
      if ( $Position == 1 || $NeuerMonat)
      {
        $PosErster = date('w', mktime(0,0,0,date('m',$datum),1,date('Y',$datum)));
        if ( $PosErster == 0 ) $PosErster = 7; // So ist letzter Tag
        $PosErster --; // Mo = 0; So = 6
        $Pos = date('d',$datum)+$PosErster;
        $Woche = ceil($Pos / 7);
        $Monate[$Monat][$Woche][0]['value'] = $value;
      }
      $Monate[$Monat][$Woche][$Position]['value'] = date('d',$datum);
      //$Tage[$value]++;
    }
    // Ferien eintragen
    $sql = 'SELECT * FROM T_FreiTage WHERE '.
      "(ersterTag <= $letzterTag AND letzterTag >= $ersterTag) ".
      'ORDER BY ersterTag';
    $query = mysql_query($sql);
    while ( $row = mysql_fetch_array($query) )
    {
      $Tag = $row['ersterTag'];
      $Sommerferien[0] = $row['ersterTag'];
      $Sommerferien[1] = $row['letzterTag'];
      // Position des Monatsersten feststellen
      while ( $Tag <= $row['letzterTag'] && $Tag <= $letzterTag )
      {
        $PosErster = date('w', mktime(0,0,0,date('m',$Tag),1,date('Y',$Tag)));
        if ( $PosErster == 0 ) $PosErster = 7; // So ist letzter Tag
        $PosErster --; // Mo = 0; So = 6
        //$Monate[date('m',$Tag)][][0]['value'] = $row['Kommentar'];
        $Pos = date('d',$Tag)+$PosErster;
        $Woche = ceil($Pos / 7);
        $TagPos = date('w',$Tag);
        if ( $TagPos == 0 ) $TagPos = 7;
        if ( date('m',$Tag) >= 8 && date('Y',$Tag) == $Anfangsjahr ||
             date('m',$Tag) < 8 && date('Y',$Tag) == $Anfangsjahr+1 )
        {
          $Monate[date('m',$Tag)][$Woche][$TagPos]['Fuellung'] = 0.4;
          if ( ! isset($Monate[date('m',$Tag)][$Woche][$TagPos]['value']) )
            $Monate[date('m',$Tag)][$Woche][$TagPos]['value'] = date('d', $Tag);
          if ( ! isset($Monate[date('m',$Tag)][$Woche][0]['value']) )
            $Monate[date('m',$Tag)][$Woche][0]['value'] = '';
        }
        $Tag = strtotime('+1 day', $Tag);
      }
    }
    mysql_free_result($query);
  } // wenn nicht Gesamtübersicht
  // Sortieren nach Woche
  $Halbjahresende = 0;
  foreach ( $Monate as $monat => $inhalt )
  {
    $M = array();
    for ( $i = 0; $i < Count($inhalt); $i++)
      if ( isset($inhalt[$i]) )
      {
        $M[$i] = $inhalt[$i];
        // Tage berechnen
        for ( $j = 1; $j < Count($inhalt[$i]); $j++)
          if ( ! isset($inhalt[$i][$j]['Fuellung']) &&
               is_numeric($inhalt[$i][$j]['value']) )
          {
            $Tage[$inhalt[$i][0]['value']]++;
            if ( $monat == '01' )
            {
              if ( date('w',mktime(0,0,0,1,$inhalt[$i][$j]['value'],$Anfangsjahr+1)) == 5 )
              {
                $Halbjahresende = mktime(0,0,0,1,$inhalt[$i][$j]['value'],$Anfangsjahr+1);
              }
            }
            //echo $inhalt[$i][0]['value'].' // ';
          }
      }
    $Monate[$monat] = $M;
  }
  reset($Monate);
  PDF_setfont($p, $bfont, 18.0);
  PDF_set_text_pos($p, 50, 800);
  if ( $Turnusse < 0)
  {
  	PDF_show($p, 'Wochenplan aller Turnusse');
  	$Turnusname = '';
  }
  else
  {
    $query = mysql_query("SELECT Name FROM T_TurnusGruppe WHERE ID_Gruppe=$Turnusse");
    $Turnusname = mysql_fetch_row($query);
    mysql_free_result($query);
    $Turnusname = $Turnusname[0];
    PDF_show($p, 'Wochenplan '.implode(',',$turni));
  }
  pdf_continue_text($p,"Schuljahr $Schuljahr");
  PDF_setfont($p, $bfont, 10.0);
  pdf_continue_text($p,'');
  pdf_continue_text($p,$Turnusname);
  $bb = 'oszimtlogo300.jpg';
  if ( file_exists($bb) ) {
    $pim = pdf_load_image($p, 'jpeg', $bb,''); 
    pdf_place_image($p, $pim, 425, 750, 0.4);
    pdf_close_image($p, $pim);
  }
  else
    PDF_continue_text($p, '(OSZ IMT)');

  PDF_continue_text($p, '');
  $font = PDF_findfont($p, 'Helvetica', 'host', 0);
  if ( Count($turni) == 0)
  {
  	PDF_setfont($p, $font, 16.0);
    pdf_show_xy($p, 'Noch keine Daten vorhanden!', 40, 650);
  }
  else
  {    
    PDF_setfont($p, $font, 8.0);
    if ( $Turnusse >= 0 && Count($turni) > 0)
    {
      $Breite = pdf_stringwidth($p, '999', $font, 8.0);
      foreach ( $Monate as $monat => $Tabelle )
      {
        $x = 40;
        $y = 700;
        $TBreite = ($Breite+4+2)*8+30;
        switch ( $monat )
        {
          case 9: $x += $TBreite;
                  break;
          case 10: $x += $TBreite*2;
                   break;
          case 11: $y -= 175;
                   break;
          case 12: $x += $TBreite;
                   $y -= 175;
                   break;
          case 1: $x += $TBreite*2;
                  $y -= 175;
                  break;
          case 2: $y -= 175*2;
                  break;
          case 3: $x += $TBreite;
                  $y -= 175*2;
                  break;
          case 4: $x += $TBreite*2;
                  $y -= 175*2;
                  break;
          case 5: $y -= 175*3;
                  break;
          case 6: $x += $TBreite;
                  $y -= 175*3;
                  break;
          case 7: $x += $TBreite*2;
                  $y -= 175*3;
        }
        pdf_Tabelle_xy($p, $x,$y-14, $Tabelle, 'center', $Breite);
        PDF_setfont($p, $bfont, 14.0);
        if ( $monat > 7 )
          $jahr = $Anfangsjahr;
        else
          $jahr = $Anfangsjahr+1;
        $monat += 0;
        pdf_show_xy($p, $Monatsnamen[$monat].' '.$jahr, $x, $y);
        PDF_setfont($p, $font, 8.0);
      }
    } // spezielle Turnusangeben
    elseif (Count($turni) > 0)
    {
    //   alle Turnusse auflisten
      pdf_Tabelle_xy($p, 40, 740, $Turnustabelle, 'center');
    }
    PDF_setfont($p, $font, 8.0);
    if ( $Turnusse >=0  )
    {
      pdf_show_xy($p, 'Legende:',$TBreite, 47);
      pdf_Tabelle_xy($p, $TBreite+pdf_stringwidth($p, 'Legende:',
        pdf_get_value($p,'font',0),
        pdf_get_value($p,'fontsize',0))+4, 55,
        $Legende, 'center');
      // Bemerkungen auslesen
      $query = mysql_query("SELECT * FROM T_WocheBemerkungen WHERE Schuljahr='$Schuljahr'");
      if ( $bemerkung = mysql_fetch_array($query))
      {
      	$Halbjahr1 = $bemerkung['Halbjahr1'];
      	$Halbjahr2 = $bemerkung['Halbjahr2'];
      }
      else
      {
      	$Halbjahr1 = '';
      	$Halbjahr2 = '';
      }
      mysql_free_result($query);
      // Bemerkungen 1. Halbjahr
      $h = explode("\n", $Halbjahr1);
      $i = 0;
      foreach ( $h as $zeile )
      {
      	$tabs = explode("\t", $zeile);
      	$j = 0;
      	foreach ( $tabs as $z )
      	{
      	   	pdf_show_xy($p, $z, 40+$TBreite*$j, 750-175*2-10*$i);
      	   	$j++;
      	}
      	$i++;
      }
      // Bemerkung 2. Halbjahr
      $h = explode("\n", $Halbjahr2);
      $i = 0;
      foreach ( $h as $zeile )
      {
   	   	pdf_show_xy($p, $zeile, 40+$TBreite*2, 60-10*$i);
      	$i++;
      }
      //pdf_show_xy($p, 'Halbjahresende am '.date('d.m.Y',$Halbjahresende), 40+$TBreite, 700-175*2+40);
      //pdf_show_xy($p, 'Sommerferien', 40+$TBreite*2, 60);
      //pdf_continue_text($p, 'vom '.date('d.',$Sommerferien[0]).
      //  $Monatsnamen[date('m',$Sommerferien[0])+0].'.'.date('Y',$Sommerferien[0]));
      //pdf_continue_text($p, 'bis '.date('d.',$Sommerferien[1]).
      //  $Monatsnamen[date('m',$Sommerferien[1])+0].'.'.date('Y',$Sommerferien[0]));
    }
    pdf_show_xy($p, 'Stand: '.date('d.m.Y'), 500, 30);
    if ( $Turnusse < 0 )
      pdf_set_text_pos($p, 450, 460);
    else
      pdf_set_text_pos($p, 40, 60);
    foreach ( $Tage as $key => $value )
        pdf_continue_text($p, "Turnus $key: ".$value.' Tage');
  }
  pdf_setfont($p, $font, 6.0);
  pdf_show_xy($p, 'Berufliches Gymnasium, Berufsoberschule,',425, 747);
  pdf_continue_text($p, 'Fachoberschule, Berufsfachschule,');
  pdf_continue_text($p, 'Fachschule und Berufsschule');
  pdf_continue_text($p, 'Haarlemer Straße 23-27, 12359 Berlin-Neukölln');
  pdf_continue_text($p, 'Tel.: 030-606-4097     Fax: 030-606-2808');
  pdf_continue_text($p, 'http://www.oszimt.de');
  //pdf_add_weblink($p,$x, $y, $x+pdf_stringwidth($p,'www.oszimt.de'), $y+8, 'http://www.oszimt.de');
  PDF_end_page($p);
  PDF_close($p);
  $buf = PDF_get_buffer($p);
  $len = strlen($buf);
  header('Content-type: application/pdf');
  header("Content-Length: $len");
  header('Content-Disposition: inline; filename=Wochenplan.pdf');
  print $buf;
  PDF_delete($p);
} // wenn Turnus übergeben
else
{
  
  $Ueberschrift = 'Wochenpläne anzeigen';
  // Turnuskombinationen auswerten

  include('include/header.inc.php');
  include('include/turnus.inc.php');
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
  echo '<tr>';
  echo '<td>Turnusplan für <select name="Turnus">';
  $query = mysql_query('SELECT * FROM T_TurnusGruppe ORDER BY Name');
  while ( $turnus = mysql_fetch_array($query))
    echo "<option value=\"{$turnus["ID_Gruppe"]}\">{$turnus["Name"]}</option>";
  mysql_free_result($query);    
  echo "<option value=\"-1\">Gesamtübersicht</option>";
  echo '</select>';
  echo ' Schuljahr <select name="Schuljahr">';
  $query = mysql_query('SELECT DISTINCT SJahr FROM T_Turnus ORDER BY SJahr',$db);
  $Schuljahr = Schuljahr();
  while ( $sjahr = mysql_fetch_row($query) )
  {
    echo '<option';
    if ( $sjahr[0] == $Schuljahr) echo ' selected="selected"';
    echo '>'.$sjahr[0].'</option>';
  }
  mysql_free_result($query);
  echo '</select> <input type="Submit" value="anzeigen">';
  echo '</td></tr>';
  echo '</form>';
  echo '<tr><td>Zum Lesen der Dokumente benötigen Sie den <a href="';
  echo 'http://www.adobe.de/products/acrobat/readstep2.html';
  echo '">Acrobat Reader</a>.</td></tr>';
  echo '<tr><td>&nbsp;</td></tr>';
  echo '<tr><td>&nbsp;</td></tr>';
  echo '<tr><td align="center"><a href="/">Zurück zur Startseite des Lehrerbereichs.</a></td></tr>';
  include('include/footer.inc.php');
}

?>