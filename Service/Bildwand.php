<?php
/*
 * Zeigt die Bilder des Kollegium als PDF-Datei an
 * (c) 2006 Christoph Griep
 */
  include('include/config.php');
  include('include/pdf.inc.php');
  $p = PDF_new();
  PDF_open_file($p, ''); 
  PDF_set_info($p, 'Creator', 'OSZIMT');
  PDF_set_info($p, 'Author', 'Christoph Griep');
  PDF_set_info($p, 'Title', 'Bildwand');
  PDF_begin_page($p, 595, 842);
  $Fonts = LadeFonts($p);
  $bfont = $Fonts['Arial']['B']; // PDF_findfont($p, 'Helvetica-Bold', 'host', 0);
  $bb = 'Logo.jpg'; //'oszimtlogo300.jpg';
  PDF_setfont($p, $bfont, 18.0);
  PDF_set_text_pos($p, 50, 800);
  if ( file_exists($bb) ) {
    $pim = pdf_load_image($p, 'jpeg', $bb,''); 
    pdf_place_image($p, $pim, 420, 750, 0.05);
    pdf_close_image($p, $pim);
  }
  if ( isset( $_REQUEST['Seite']) && is_numeric($_REQUEST['Seite']))
  {
    $Limit = 'LIMIT '.(($_REQUEST['Seite']-1)*42).',42'; 
  }
  else
    $Seite = -1;
  if ( isset($_REQUEST['Kuerzel']))
  {
  	$Kuerzel = ' AND Kuerzel>="'.mysql_real_escape_string($_REQUEST['Kuerzel']).'"';
  }
  else
    $Kuerzel = '';
  pdf_setfont($p, $Fonts['Arial']['B'], 20.0);
  pdf_show_xy($p, 'Kollegium des Oberstufenzentrums',65, 790);
  // Rahmen um die Seite (Schnittkante)
  pdf_rect($p, 0,0,595,842);
  pdf_stroke($p);
  // Bilder laden und anzeigen
  $x = 40;
  $y = 730;
  $i = 0;
  $query = mysql_query('SELECT * FROM T_Lehrer WHERE Vorname<>"" '.$Kuerzel.' ORDER BY Name '.$Limit);
  while ( $lehrer = mysql_fetch_array($query) )
  {
    if ( $lehrer['Vorname'] != '' )
    {
      if ( $lehrer['Bild'] != '' )
      {
        $bild = $lehrer['Bild'];
        if ( isset($_REQUEST['neueBreite']))
        { 
          $neuebreite = 150;
          $altesBild=imagecreatefromstring ($lehrer['Bild']);
          $breite = imageSX($altesBild);
          $hoehe = imageSY($altesBild);
          $neueHoehe=intval($hoehe*$neuebreite/$breite);
          $neuesBild=imagecreatetruecolor($neuebreite,$neueHoehe);
          ImageCopyResized($neuesBild,$altesBild,0,0,0,0,$neuebreite,$neueHoehe,$breite,$hoehe);
          ob_start();  
          ImageJPEG($neuesBild);
          $bild = ob_get_contents();
          ob_end_clean();
        } 
        $pvt_filename = '/pvt/image/Bild';
        pdf_create_pvf($p, $pvt_filename,$bild,'');
        $image = pdf_load_image($p, "jpeg",$pvt_filename,"");
        pdf_fit_image($p, $image, $x,$y-65,"boxsize {50 70} position 50 fitmethod meet");
        pdf_delete_pvf($p, $pvt_filename);
      }
      else
      {
       pdf_setcolor($p,'fill','gray',0.9,0,0,0);
         pdf_rect($p,  $x, $y-65, 50, 70); 
         pdf_fill($p);
         pdf_setcolor($p,'fill','gray',0,0,0,0); 
        pdf_setfont($p, $Fonts['Arial']['B'], 8.0);       
        $Name = "Kein";
        pdf_show_xy($p, $Name, $x+25-pdf_stringwidth($p, $Name,
          pdf_get_value($p,'font',0),
          pdf_get_value($p,'fontsize',0))/2, $y-10);
        $Name = "Bild";
        pdf_show_xy($p, $Name, $x+25-pdf_stringwidth($p, $Name,
          pdf_get_value($p,'font',0),
          pdf_get_value($p,'fontsize',0))/2, $y-25);
        $Name = "vorhanden";
        pdf_show_xy($p, $Name, $x+25-pdf_stringwidth($p, $Name,
          pdf_get_value($p,'font',0),
          pdf_get_value($p,'fontsize',0))/2, $y-40);
        pdf_delete_pvf($p, $pvt_filename);
      }
      pdf_setfont($p, $Fonts['Arial']['B'], 6.0);
      $Name = '';
      if ( $lehrer['Geschlecht'] == 'M')
        $Name = 'Herr ';
      else
        $Name = 'Frau ';
      $Name .= $lehrer['Name'];
      pdf_show_xy($p, $Name, $x+25-pdf_stringwidth($p, $Name, 
          pdf_get_value($p,'font',0),
          pdf_get_value($p,'fontsize',0))/2, $y-75);
      $x += 95;
      if ( $x >= 550 )
      {
        $x = 40;
        $y -= 90;
        if ( $y < 100 )
        {
          $y = 730;
          pdf_end_page($p);
          PDF_begin_page($p, 595, 842);
          $bb = 'Logo.jpg';
          PDF_setfont($p, $bfont, 18.0);
          PDF_set_text_pos($p, 50, 800);
          if ( file_exists($bb) ) {
            $pim = pdf_load_image($p, 'jpeg', $bb,'');
            pdf_place_image($p, $pim, 420, 750, 0.05);
            pdf_close_image($p, $pim);
          }
          pdf_setfont($p, $Fonts['Arial']['B'], 20.0);
          pdf_show_xy($p, 'Kollegium des Oberstufenzentrums',65, 790);
          pdf_rect($p, 0,0,595,842);
          pdf_stroke($p);  
        }
      }
    }
   }
  mysql_free_result($query);
  PDF_end_page($p);
  PDF_close($p);
  $buf = PDF_get_buffer($p);
  $len = strlen($buf);
  header('Content-type: application/pdf');
  header("Content-Length: $len");
  header('Content-Disposition: inline; filename=Bildwand.pdf');
  print $buf;
  PDF_delete($p);

?>
