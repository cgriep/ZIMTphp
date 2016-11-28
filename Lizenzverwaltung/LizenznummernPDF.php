<?php
/**
 * Zeigt die Lizenznummern als PDF -Datei an
 * (c) 2006 Christoph Griep
 */
function LizenznummernPDF($VN = -1)
{
  require_once('include/pdf.inc.php');
  require_once('msdnaaconfig.inc.php');
  if ( ! is_numeric($VN) )
  {
    $nummern = explode(',',$VN);
    if ( Count($nummern) > 1 )
      $VN = $nummern;
    else
    {
      if ( ! $query = mysql_query('SELECT DISTINCT Vertragsnummer '.
        'FROM T_Lizenznehmer WHERE Art= "'.
        $VN.'"')) echo mysql_error();
      $VN = array();
      while ( $row = mysql_fetch_array($query) )
      {
        $VN[]= $row['Vertragsnummer'];
      }
      mysql_free_result($query);
    }
    if ( is_array($VN) )
    {
      if (!$query = mysql_query('SELECT * FROM T_Lizenznehmer INNER JOIN T_Produkte ON ' .
      ' T_Produkte.id = T_Lizenznehmer.ProduktID WHERE Vertragsnummer IN ('.
        implode(',',$VN).') ORDER BY Vertragsnummer, Datum DESC')) echo mysql_error();
    }
    else
      die('Keine Einträge für '.$VN.' gefunden!');
  }
  else
    $query = mysql_query('SELECT * FROM T_Lizenznehmer INNER JOIN T_Produkte ON ' .
      ' T_Produkte.id = T_Lizenznehmer.ProduktID WHERE Vertragsnummer = '.$VN.
      ' ORDER BY Datum DESC');
  if ( mysql_num_rows($query) == 0 )
    die ('Keine Einträge für '.$VN.' gefunden');
  $p = PDF_new();
  PDF_open_file($p,'');
  PDF_set_info($p, 'Creator', 'OSZIMT');
  PDF_set_info($p, 'Author', 'Christoph Griep');
  PDF_set_info($p, 'Title', 'Lizenznummern Vertrag '.$VN);
  $fontb = PDF_findfont($p, 'Helvetica-Bold', 'host', 0);
  $font = PDF_findfont($p, 'Helvetica', 'host', 0);
  $ifont = PDF_findfont($p, 'Helvetica-Oblique', 'host', 0);
  $fertig = false;
  $row = mysql_fetch_object($query);
  while (!$fertig )
  {
    PDF_begin_page($p, 595, 842);
    PDF_setfont($p, $fontb, 18.0);
    if ( file_exists('../Lizenz/oszimtlogo300.jpg') ) {
      $pim = pdf_load_image($p, 'jpeg', '../Lizenz/oszimtlogo300.jpg','');
      pdf_place_image($p, $pim, 425, 725, 0.4);
      pdf_close_image($p, $pim);
    }
    else
      PDF_continue_text($p, '(OSZIMT)');
    PDF_set_text_pos($p, 50, 700);
    PDF_show($p, 'Lizenznummern für Vereinbarung Nr. '.$row->Vertragsnummer);
    PDF_setfont($p, $fontb, 14.0);
    $Name = $row->Vorname.' '.$row->Name;
    $Klasse = $row->Art;
    PDF_continue_text($p,'Lizenznehmer '.$Name.' ');
    if ( strtoupper($Klasse) != 'LEHRER')
      PDF_show($p,'(Klasse '.$Klasse.')');
    else
      PDF_show($p,'(Lehrer)');
    PDF_continue_text($p,'');
    PDF_continue_text($p,'');
    PDF_continue_text($p,'');
    PDF_setfont($p, $fontb, 12.0);
    zeige_text_absatz($p, 'Folgende Lizenzen sind gemäß Vereinbarung Nr. '.
      $row->Vertragsnummer.' auf Ihren Namen registriert:');
    PDF_setfont($p, $ifont, 12.0);
    pdf_show_xy($p, 'Produkt', 50, pdf_get_value($p, 'texty',0));
    pdf_show_xy($p, 'Produktschlüssel', 300, pdf_get_value($p, 'texty',0));
    pdf_continue_text($p,'');
    PDF_setfont($p, $font, 12.0);
    $Ansprechpartner = '';
    $LetztesDatum = 0;
    $Vertragsnummer = $row->Vertragsnummer;
    do {
      PDF_show_xy($p, $row->Produkt, 50, pdf_get_value($p, 'texty',0));
      PDF_show_xy($p, $row->Serialkey, 300, pdf_get_value($p, 'texty',0));
      PDF_continue_text($p,'');
      if ( $LetztesDatum < strtotime($row->Datum) ) {
        $Ansprechpartner = $row->Ansprechpartner;
        $LetztesDatum = strtotime($row->Datum);
      }
      $fertig = !($row = mysql_fetch_object($query));
    } while ( ! $fertig &&  $row->Vertragsnummer == $Vertragsnummer);
    PDF_set_text_pos($p, 50, pdf_get_value($p, 'texty',0));
    PDF_continue_text($p, 'Sie dürfen diese Produktschlüssel nicht an Dritte weitergeben!');
    PDF_continue_text($p, '');
    PDF_continue_text($p, '');
    zeige_text_absatz($p, 'Sie dürfen die Software unter Beachtung der MSDNAA-Richtlinien auf ihrem privaten PC installieren und nutzen. Das Nutzungsrecht bleibt auch nach Ausscheiden aus dem OSZ IMT erhalten, sofern Sie die Nutzungsrichtlinien weiterhin einhalten.');
    pdf_continue_text($p,'');
    PDF_set_text_pos($p, 50, pdf_get_value($p, 'texty',0));
    $anz = 0;
    /*
    PDF_setfont($p, $fontb, 12.0);
    PDF_continue_text($p, 'Sie haben folgende CD(s) zum Installieren:');
    PDF_setfont($p, $font, 12.0);
    pdf_continue_text($p,'');
    $query2 = mysql_query("SELECT CD.id, Bezeichnung, Datum, Produkt FROM CD INNER JOIN ".
      "Produkte ON Produkte.id = CD.ProduktID WHERE VertragID = ".$Vertragsnummer);
    pdf_continue_text($p,"");
    PDF_setfont($p, $ifont, 12.0);
    pdf_show_xy($p, "Datum", 50, pdf_get_value($p, "texty"));
    pdf_show_xy($p, "CD-Nr", 130, pdf_get_value($p, "texty"));
    pdf_show_xy($p, "Bezeichnung", 200, pdf_get_value($p, "texty"));
    pdf_continue_text($p,"");
    PDF_setfont($p, $font, 12.0);
    while ($row2 = mysql_fetch_object($query2) ) {
      PDF_show_xy($p, $row2->Datum, 50, pdf_get_value($p, "texty"));
      PDF_show_xy($p, $row2->id, 130, pdf_get_value($p, "texty"));
      PDF_set_text_pos($p, 200, pdf_get_value($p, "texty"));
      zeige_text_absatz($p, $row2->Bezeichnung." (".$row2->Produkt.")", false);
      $anz++;
    }
    mysql_free_result($query2);
    if ( $anz == 0 ) PDF_continue_text($p, "-- keine --");
    PDF_continue_text($p,"");
    PDF_set_text_pos($p, 50, pdf_get_value($p, "texty"));
    if ( $anz != 0 ) {
      PDF_Continue_text($p, "Bitte geben Sie die CD(s) schnellstmöglich, spätestens nach einer Woche, zurück.");
      PDF_Continue_text($p, "");
      zeige_text_absatz($p, "Beachten Sie bitte, dass Sie die Software gemäß den Nutzungsrichtlinien nur dann verwenden dürfen, wenn Sie die CD(s) an das OSZ IMT zurückgeben.");
      zeige_text_absatz($p, "Sie dürfen diese CD(s) nicht an Dritte weitergeben! Berechtigte Interessenten müssen die Software über die Ausleihprozedur des OSZ IMT beantragen. ");
      pdf_continue_text($p,"");
      zeige_text_absatz($p, "Sie dürfen maximal eine Kopie dieser CD(s) als private Sicherheitskopie anfertigen.");
    }
    */
    pdf_continue_text($p,'');
    pdf_continue_text($p,'');
    pdf_continue_text($p,'');
    zeige_text_absatz($p, 'Ihr MSDNAA-Verwaltungsteam vom OSZ IMT');
    pdf_continue_text($p,'Berlin, '.date('d.m.Y', $LetztesDatum));
    PDF_end_page($p);
    if ( $anz != 0 ) {
      PDF_begin_page($p, 595, 842);
      if ( file_exists('oszimtlogo300.jpg') ) {
        $pim = pdf_load_image($p, 'jpeg', 'oszimtlogo300.jpg','');
        pdf_place_image($p, $pim, 425, 750, 0.4);
        pdf_close_image($p, $pim);
      }
      else
        PDF_continue_text($p, '(OSZIMT)');
      PDF_set_text_pos($p, 50, 750);
      PDF_setfont($p, $fontb, 10.0);
      pdf_continue_text($p,'Liebe(r) Kollege/in '.$Ansprechpartner.',');
      pdf_continue_text($p, '');
      if ( strtoupper($Klasse) != 'LEHRER' ) {
        zeige_text_absatz($p,  'Bitte lassen Sie dieses Formular vom Schüler unterschreiben und übergeben Sie die CD(s) und die Seite mit den Lizenznummern an den Schüler. Sammeln Sie die CD(s) spätestens nach einer Woche wieder ein und reichen Sie sie mit der unterschriebenen Seite ebenfalls an die MSDNAA-Verwaltung zurück. Danke für Ihre Mithilfe.');
       }
      else
        zeige_text_absatz($p,  'Bitte unterschreiben Sie dieses Formular und geben Sie es an die MSDNAA-Verwaltung zurück. Auf der beiliegenden, für Sie bestimmten Seite finden Sie Ihre persönlichen Lizenznummern. Bitte geben Sie die CD(s) spätestens in einer Woche zurück. Danke für Ihre Mithilfe.');
      PDF_set_text_pos($p, 50, 650);
      PDF_setfont($p, $fontb, 14.0);
      PDF_show($p, 'CD(s) für Vereinbarung Nr. '.$Vertragsnummer);
      PDF_continue_text($p,'Lizenznehmer '.$Name.' ');
      if ( strtoupper($Klasse) != 'LEHRER')
        PDF_show($p,'(Klasse '.$Klasse.')');
      else
        PDF_show($p,'(Lehrer)');
      PDF_continue_text($p,'');
      PDF_continue_text($p,'');
      PDF_continue_text($p,'');
      PDF_setfont($p, $font, 12.0);
      zeige_text_absatz($p,'Ich habe die Erläuterungen zu den Lizenzen gelesen und die CD(s) erhalten. Ich werde die folgenden CD(s) schnellstmöglich, spätestens nach einer Woche, an das OSZ IMT zurückgeben. Mir ist bekannt, dass die Nutzungserlaubnis der Lizenz erlischt, wenn die CD(s) nicht zurückgegeben werden.');
      $query2 = mysql_query('SELECT CD.id, Bezeichnung, Datum, Produkt FROM CD INNER JOIN '.
        'T_Produkte ON T_Produkte.id = CD.ProduktID WHERE VertragID = '.$Vertragsnummer);
      pdf_continue_text($p,'');
      PDF_setfont($p, $ifont, 12.0);
      pdf_show_xy($p, 'Datum', 50, pdf_get_value($p, 'texty'));
      pdf_show_xy($p, 'CD-Nr', 130, pdf_get_value($p, 'texty'));
      pdf_show_xy($p, 'Bezeichnung', 200, pdf_get_value($p, 'texty'));
      PDF_setfont($p, $font, 12.0);

      pdf_continue_text($p,'');
      $anz = 0;
      while ($row2 = mysql_fetch_object($query2) ) {
        PDF_show_xy($p, $row2->Datum, 50, pdf_get_value($p, 'texty',0));
        PDF_show_xy($p, $row2->id, 130, pdf_get_value($p, 'texty',0));
        PDF_set_text_pos($p, 200, pdf_get_value($p, 'texty',0));
        zeige_text_absatz($p, $row2->Bezeichnung.' ('.$row2->Produkt.')', false);
        $anz++;
      }
      mysql_free_result($query2);
      if ( $anz == 0 )
        pdf_continue_text($p, '-- keine --');
      PDF_continue_text($p,'');
      PDF_continue_text($p,'');
      PDF_continue_text($p,'');
      PDF_continue_text($p,'');
      pdf_moveto($p, 50, pdf_get_value($p,'texty',0));
      pdf_lineto($p, 200,pdf_get_value($p,'texty',0));
      pdf_stroke($p);
      pdf_moveto($p, 350, pdf_get_value($p,'texty',0));
      pdf_lineto($p, 500,pdf_get_value($p,'texty',0));
      pdf_stroke($p);
      PDF_continue_text($p,'');
      pdf_show_xy($p,'Ort, Datum', 50, pdf_get_value($p, 'texty',0));
      PDF_show_xy($p,$Name, 350, pdf_get_value($p, 'texty',0));
      PDF_setfont($p, $font, 6.0);
      //  pdf_continue_text($p, '(Bei Minderjährigen Unterschrift der Erziehungsberechtigten)');
      pdf_show_xy($p, 'Wird von der MSDNAA-Verwaltung des OSZ IMT ausgefüllt', 350,50);
      PDF_setfont($p, $font, 8.0);
      pdf_continue_text($p,'');
      $y = pdf_get_value($p, 'texty',0);
      pdf_set_text_pos($p, 50, $y);
      PDF_setfont($p, $fontb, 8.0);
      pdf_show_xy($p, 'CD zurück am:', 350, $y);
      pdf_continue_text($p,'');
      pdf_continue_text($p,'');
      pdf_show_xy($p, 'CD ausgebucht:', 350, pdf_get_value($p, 'texty'));
      PDF_end_page($p);
    } // if Anz != 0
  }
  mysql_free_result($query);
  PDF_close($p);
  $buf = PDF_get_buffer($p);
  PDF_delete($p);
  if ( isset($db) )mysql_close($db);
  return $buf;
}

?>
