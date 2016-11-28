<?php
/**
 * Zeigt die Terminliste als PDF an
 * (c) 2006 Christoph Griep
 */
 DEFINE('ANZ_TAGE', 12);
 if ( isset($_REQUEST['Jahr']) && is_numeric($_REQUEST['Jahr']) )
   $jahr = $_REQUEST['Jahr'];
 elseif ( date('m') < 8 )
   $jahr = date('Y')-1;
 else
   $jahr = date('Y');
 $Schuljahr = sprintf('%02d',($jahr-2000)).'/'.sprintf('%02d',$jahr-1999);
 $Ueberschrift = 'Terminplan '.$Schuljahr;
 include('include/config.php');
 include('include/pdf.inc.php');
 include('include/Termine.class.php');

 $Termine = new Termine($db);

function baueTabelleAuf($halbjahr = 1, $Terminobjekt)
{
 global $jahr;
 global $Schuljahr;
 global $db;
 if ( $halbjahr != 1 ) $jahr++;
 $termine = array();
 $zeile = 0;
 $termine[0][0]['value'] = ''; // Tagesnummer
 $termine[0][1]['value'] = $jahr;
 $termine[0][2]['value'] = '';
 $termine[0][3]['value'] = '';
 $termine[0][4]['value'] = '';
 $termine[0][5]['value'] = '';
 $termine[0][6]['value'] = '';
 for ( $i = 0; $i < 7; $i++)
   $termine[0][$i]['Rahmen'] = '';
 if ( $halbjahr != 1 )
 {
   $termine[0][7]['value'] = ''; // Tagesnummer
   $termine[0][7]['Rahmen'] = '';
 }
 $zeile++;
 $termine[$zeile][0]['value'] = '';
 $termine[$zeile][0]['Rahmen'] = '';
 if ( $halbjahr == 1 )
 {
   $termine[$zeile][1]['value'] = 'August';
   $termine[$zeile][1]['Breite'] = 80;
   $termine[$zeile][2]['value'] = 'September';
   $termine[$zeile][2]['Breite'] = 80;
   $termine[$zeile][3]['value'] = 'Oktober';
   $termine[$zeile][3]['Breite'] = 80;
   $termine[$zeile][4]['value'] = 'November';
   $termine[$zeile][4]['Breite'] = 80;
   $termine[$zeile][5]['value'] = 'Dezember';
   $termine[$zeile][5]['Breite'] = 80;
   $termine[$zeile][6]['value'] = 'Januar';
   $termine[$zeile][6]['Breite'] = 80;
   $termine[$zeile][7]['value'] = '';
   $termine[$zeile][7]['Rahmen'] = '';
 }
 else
 {
   $termine[$zeile][1]['value'] = 'Februar';
   $termine[$zeile][1]['Breite'] = 80;
   $termine[$zeile][2]['value'] = 'März';
   $termine[$zeile][2]['Breite'] = 80;
   $termine[$zeile][3]['value'] = 'April';
   $termine[$zeile][3]['Breite'] = 80;
   $termine[$zeile][4]['value'] = 'Mai';
   $termine[$zeile][4]['Breite'] = 80;
   $termine[$zeile][5]['value'] = 'Juni';
   $termine[$zeile][5]['Breite'] = 80;
   $termine[$zeile][6]['value'] = 'Juli';
   $termine[$zeile][6]['Breite'] = 80;
   $termine[$zeile][7]['value'] = '';
   $termine[$zeile][7]['Rahmen'] = '';
 }
 // gleich wird wieder was abgezogen
 for ( $tag = 1; $tag < 32; $tag++ )
 {
   if ( $halbjahr == 1 )
     $monat = 8;
   else
     $monat = 2; // 2. fängt im Februar an
   $monatspalte = 1;
   $termine[$zeile+$tag][0]['value'] = $tag;
   while ( $monat != 0 )
   {
     if ( date('t',mktime(0,0,0,$monat,1,$jahr)) >= $tag )
     {
       $Inhalt = '';
       $tagzahl = mktime(0,0,0,$monat, $tag, $jahr);
       $t = date('w', $tagzahl);
       $sql = 'SELECT Count(*) FROM T_FreiTage WHERE ersterTag <= '.$tagzahl.' AND '.
         'letzterTag >= '.$tagzahl;
       $query = mysql_query($sql, $db);
       $row = mysql_fetch_row($query);
       mysql_free_result($query);
       if ($row[0] > 0 )
         $Ferien = true;
       else
         $Ferien = false;
       if ( $t == 0 || $t == 6 )
         $termine[$zeile+$tag][$monatspalte]['Fuellung'] = 0.9;
       elseif ( $Ferien )
         $termine[$zeile+$tag][$monatspalte]['Fuellung'] = 0.7;
       if ( $t == 1 )
       {
         // Turnus eintragen
         $t = '';
         $sql = 'SELECT Turnus FROM '.
            '(T_WocheTurnus INNER JOIN T_Turnus ON F_ID_Turnus = ID_Turnus) '.
            'INNER JOIN T_Woche ON ID_Woche=F_ID_Woche '.
            "WHERE Montag=$tagzahl AND T_Woche.SJahr='$Schuljahr' ORDER BY Turnus";
         if ( ! $query = mysql_query($sql,$db)) echo mysql_error($db);
         while ( $turnus = mysql_fetch_row($query) )
         {
           $t .= $turnus[0].' ';
         }
         mysql_free_result($query);
         $Inhalt .= $t;
       }
       if ( $Ferien ) {
         $sql = 'SELECT Kommentar FROM T_FreiTage WHERE ersterTag = '.$tagzahl;
         $query = mysql_query($sql,$db);
         $row = mysql_fetch_row($query);
         mysql_free_result($query);
         $Inhalt .= $row[0];
       }
       // Termine anfügen
       if ( ! $query = mysql_query("SELECT *, DATE_FORMAT(Stand,'%d.%m.%Y %H:%i') AS St ".
         "FROM T_Termin_Termine WHERE NOT Vorlaeufig AND Datum BETWEEN ".
         $tagzahl.' AND '.mktime(23,59,59,$monat,$tag,$jahr),$db)) echo mysql_error($db);
       while ( $termin = mysql_fetch_array($query) )
       {
         $xt = '';
         $art = explode(',',$termin['Betroffene']);
         if ( $Terminobjekt->istBetroffen($art) )
         {
           $xt .= trim($termin['Bezeichnung']);
           if ( $Inhalt != '' ) $Inhalt .= "\n";
           $Inhalt .= $xt; //  . date('d.m.Y',$termin['Datum']);
           //if ( date('H:i',$termin['Datum']) != '00:00' )
           //   $Inhalt .= ' '.date('H:i',$termin['Datum']);
         } // Count > 0 (Terminfilter!)
       }
       mysql_free_result($query);
       $termine[$zeile+$tag][$monatspalte]['value'] = $Inhalt;
     }
     else
     {
       // Zelle ohne Rahmen
       $termine[$zeile+$tag][$monatspalte]['value'] = '';
       $termine[$zeile+$tag][$monatspalte]['Rahmen'] = '';
     }
     $monat++;
     $monatspalte++;
     if ( $monat == 2 )
     {
       $monat = 0;
       $jahr--;
     }
     elseif ( $monat == 13 )
     {
       //$monatspalte++;
       $monat = 1;
       $jahr++;
     }
     elseif ( $monat == 8 ) $monat = 0;
   }
   $termine[$zeile+$tag][$monatspalte]['value'] = $tag;
 }
 return $termine;
}


 $p = PDF_new();
 PDF_open_file($p,'');
 PDF_set_info($p, 'Creator', 'OSZIMT');
 PDF_set_info($p, 'Author', 'Christoph Griep');
 PDF_set_info($p, 'Title', 'Terminkalender OSZIMT Stand '.date('d.m.Y H:i'));
 PDF_begin_page($p, 595, 842);
 $bfont = PDF_findfont($p, 'Helvetica-Bold', 'host', 0);
 $font = PDF_findfont($p, 'Helvetica', 'host', 0);
 PDF_setfont($p, $bfont, 12.0);
 PDF_show_xy($p, 'Terminplan '.$Schuljahr,240,810);
 PDF_setfont($p, $font, 6);
 if ( $Termine->istGefiltert() )
 {
   pdf_continue_text($p, '');
   $y = pdf_get_value($p, 'texty',0);
   pdf_set_text_pos($p, 15, $y);
   zeige_text_absatz($p,'Angezeigt werden Termine für '.$Termine->holeFilterNamen());
 }
 $y = pdf_get_value($p, 'texty',0);
 PDF_show_xy($p, 'Stand: '.$Termine->holeStand(), 240,$y-12);
 $tabelle1 = baueTabelleAuf(1, $Termine);
 $tabelle2 = baueTabelleAuf(2, $Termine);
 $y = pdf_get_value($p, 'texty',0);
 pdf_Tabelle_xy($p, 15,$y, $tabelle1, 60);

 if ( isset($_REQUEST['Fontsize']) && is_numeric($_REQUEST['Fontsize']) )
   $fontsize= $_REQUEST['Fontsize'];
 else
   $fontsize = 6;
 PDF_end_page($p);
 PDF_begin_page($p, 595, 842);
 PDF_setfont($p, $bfont, 12.0);
 PDF_show_xy($p, 'Terminplan '.$Schuljahr,240,810);
 PDF_setfont($p, $font, $fontsize);
 if ( $Termine->istGefiltert() )
 {
   pdf_continue_text($p, '');
   $y = pdf_get_value($p, 'texty',0);
   pdf_set_text_pos($p, 15, $y);
   zeige_text_absatz($p,'Angezeigt werden Termine für '.$Termine->holeFilterNamen());
 }
 $y = pdf_get_value($p, 'texty',0);
 PDF_show_xy($p, 'Stand: '.$Termine->holeStand(), 240,$y-12);
 $y = pdf_get_value($p, 'texty',0);
 pdf_Tabelle_xy($p, 15,$y, $tabelle2, 60);
 PDF_end_page($p);
 PDF_close($p);
 mysql_close($db);

 $buf = PDF_get_buffer($p);
 $len = strlen($buf);
 header('Content-type: application/pdf');
 header('Content-Length: '.$len);
 header('Content-Disposition: inline; filename=Terminplan.pdf');
 print $buf;
 PDF_delete($p);

?>