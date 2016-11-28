<?php

 $Aufsichtszeit[1] = "Früh-\naufsicht\n7:45-8:00";
 $Aufsichtszeit[2] = "1. Pause\n9:30-9:45";
 $Aufsichtszeit[3] = "2. Pause\n11:15-11:45";
 $Aufsichtszeit[4] = "3. Pause\n13:15-13:30";
 $Aufsichtszeit[5] = "4. Pause\n15:00-15:15";

 include('include/config.php');
 include('include/stupla.inc.php');
 include('include/turnus.inc.php');

 $Version = getAktuelleVersion();
 if ( isset($_REQUEST['GueltigAb']) )
 {
   // Neues Datum eingeben!
   if ( ! is_numeric($_REQUEST['GueltigAb']) )
   {
     $datum = explode('.', $_REQUEST['GueltigAb']);
     $tag = mktime(0,0,0,$datum[1],$datum[0],$datum[2]);
   }
   else
     $tag = $_REQUEST['GueltigAb'];
 }
 else
 {
 	if( date('w')==6 || date('w')==0) 
     $tag = strtotime('+2 day');
    else
     $tag = time();
 }
 $GueltigAb = getGueltigAb($tag, 'T_Aufsichten');
 

function holeLehrername($Lehrer)
{
  if ( ! $query = mysql_query("SELECT Name, Vorname FROM T_StuPla WHERE Lehrer='".
    mysql_real_escape_string($Lehrer)."'")) echo mysql_error();
  $row = mysql_fetch_array($query);
  mysql_free_result($query);
  return $row[0]; // mit Vorname: $row[1].' '.
}

 include('include/pdf.inc.php');

 $query = mysql_query('SELECT * FROM T_Aufsichtsorte ORDER BY Ort');
 $Orte = array();
 while ( $row = mysql_fetch_array($query) )
   $Orte[$row['Ort_id']] = $row;
 mysql_free_result($query);

   $query = mysql_query("SELECT * FROM T_Aufsichten WHERE GueltigAb='$GueltigAb' ".
      'ORDER BY VorStunde, Wochentag');
   $Aufsichten = array();
   while ( $row = mysql_fetch_array($query) )
   {
     $Aufsichten[$row['VorStunde']][$row['Wochentag']][$row['F_Ort_id']][] = $row;
   }
   mysql_free_result($query);
   $Tabelle[0][0]['value'] = 'Zeit';
   //$Tabelle[0][0]['Breite'] = 30;
   $Tabelle[0][1]['value'] = 'Ort';
   $Tabelle[0][1]['Breite'] = 30;
   $Tabelle[0][2]['Breite'] = 80;
   $Tabelle[0][2]['value'] = 'Montag';
   $Tabelle[0][3]['Breite'] = 80;
   $Tabelle[0][3]['value'] = 'Dienstag';
   $Tabelle[0][4]['Breite'] = 80;
   $Tabelle[0][4]['value'] = 'Mittwoch';
   $Tabelle[0][5]['Breite'] = 80;
   $Tabelle[0][5]['value'] = 'Donnerstag';
   $Tabelle[0][6]['Breite'] = 80;
   $Tabelle[0][6]['value'] = 'Freitag';
   for ( $i = 1; $i<7; $i++) $Tabelle[0][$i]['Fuellung'] = 0.7;
   $Zeile = 0;
   for ( $Stunde = 1; $Stunde < 5; $Stunde++)
   {
     reset($Orte);
     $StundeDa = false;
     $Zeile++;
     for ( $i = 0; $i < 7; $i++)
     {
       $Tabelle[$Zeile][$i]['Fuellung'] = 0.2;
       $Tabelle[$Zeile][$i]['Hoehe'] = 2;
     }
     foreach ($Orte as $Ort )
     {
       $Zeile++;
       if ( ! $StundeDa )
       {
         $Tabelle[$Zeile][0]['value'] = $Aufsichtszeit[$Stunde];
         $StundeDa = true;
       }
       // prüfen, ob überhaupt eine Aufsicht vorhanden ist
       $da = false;
       for ( $Wochentag = 1; $Wochentag <= 5; $Wochentag++)
         if ( isset($Aufsichten[$Stunde][$Wochentag][$Ort['Ort_id']] ) && 
              is_array($Aufsichten[$Stunde][$Wochentag][$Ort['Ort_id']]) )
           $da = true;
       if ( $da )
       {
         $Tabelle[$Zeile][1]['value'] = $Ort['Ort'];
         $Tabelle[$Zeile][1]['Fuellung'] = 0.7;
         for ( $Wochentag = 1; $Wochentag <= 5; $Wochentag++)
         {
           $da = false;
           if ( isset($Aufsichten[$Stunde][$Wochentag][$Ort['Ort_id']]) &&
                is_array($Aufsichten[$Stunde][$Wochentag][$Ort['Ort_id']]) )
             foreach ( $Aufsichten[$Stunde][$Wochentag][$Ort['Ort_id']] as $value )
             {
               $Tabelle[$Zeile][$Wochentag+1]['value'] = holeLehrername($value['Lehrer']);
               if ( strrpos($Tabelle[$Zeile][$Wochentag+1]['value'],' ') > 0 )
               {
                 // Mehrfachnamen kürzen
                 $name = substr($Tabelle[$Zeile][$Wochentag+1]['value'],
                   strrpos($Tabelle[$Zeile][$Wochentag+1]['value'],' ')+1);
                 $vollname = substr($Tabelle[$Zeile][$Wochentag+1]['value'],0,strrpos(
                   $Tabelle[$Zeile][$Wochentag+1]['value'],' ')).' '.substr($name,0,1).'.';
                 $Tabelle[$Zeile][$Wochentag+1]['value'] = $vollname;
               }
               $da = true;
             }
           if ( !$da )
             $Tabelle[$Zeile][$Wochentag+1] = '';
         }
       } // leere Zeilen unterdrücken
       else
         $Zeile--;
     }
     //echo '<tr class='Trennlinie'><td colspan='7'></td></tr>';
   }
  $p = PDF_new();
  PDF_open_file($p,'');
  PDF_set_info($p, 'Creator', 'OSZIMT');
  PDF_set_info($p, 'Author', 'Christoph Griep');
  PDF_set_info($p, 'Title', 'Aufsichtsplan');
  PDF_begin_page($p, 595, 842);
  $Fonts = LadeFonts($p);
  $bb = 'oszimtlogo300.jpg';
  if ( file_exists($bb) ) {
    $pim = pdf_load_image($p, 'jpeg', $bb,'');
    pdf_place_image($p, $pim, 475, 800, 0.2);
    pdf_close_image($p, $pim);
  }
  pdf_setfont($p, $Fonts['Arial']['N'], 12.0);
  pdf_show_xy($p, 'Aufsichtsplan',225, 810);
  pdf_setfont($p, $Fonts['Arial']['N'], 8.0);
  pdf_Tabelle_xy($p, 40, 760, $Tabelle, 'center', -1, -1, 1, 1, 0.5);
  pdf_setfont($p, $Fonts['Arial']['N'], 6.0);
  pdf_show_xy($p, 'Berufliches Gymnasium, Berufsoberschule,',450, 795);
  pdf_continue_text($p, 'Fachoberschule, Berufsfachschule,');
  pdf_continue_text($p, 'Fachschule und Berufsschule');
  pdf_continue_text($p, 'Haarlemer Straße 23-27, 12359 Berlin-Neukölln');
  pdf_continue_text($p, 'Tel.: 030-606-4097     Fax: 030-606-2808');
  pdf_continue_text($p, 'http://www.oszimt.de');
  pdf_setfont($p, $Fonts['Arial']['N'], 8.0);
  pdf_show_xy($p, 'Gültig ab: '.date('d.m.Y',$GueltigAb), 225, 800);
  pdf_show_xy($p, 'Stand: '.date('d.m.Y'), 225, 790);
  $query = mysql_query('SELECT Min(GueltigAb) FROM T_Aufsichten WHERE GueltigAb>'.$GueltigAb);
  if ( $next = mysql_fetch_row($query))
  {
  	// wenn nicht vorhanden, kommt ein NULL-Ergebnis
  	if ( $next[0] > $GueltigAb )
  	{
    	 pdf_setfont($p, $Fonts['Arial']['B'],10.0);
  	  pdf_show_xy($p, 'Achtung: Ein neuer Plan gilt ab '.date('d.m.Y', 
    	  strtotime('-1 day',$next[0])), 150, 775);
  	}
  }
  mysql_free_result($query);
  PDF_end_page($p);
  PDF_close($p);
  $buf = PDF_get_buffer($p);
  $len = strlen($buf);
  header('Content-type: application/pdf');
  header("Content-Length: $len");
  header('Content-Disposition: inline; filename=Aufsichtsplan.pdf');
  print $buf;
  PDF_delete($p);
?>