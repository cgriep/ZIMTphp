<?php
/**
 * Antragsformular f�r die MSDNAA-Bestellung
 * (c) 2006 Christoph Griep
 * 
 */
include('include/pdf.inc.php');
include('../Lizenzverwaltung/msdnaaconfig.inc.php');

$p = PDF_new();
PDF_open_file($p,'');
PDF_set_info($p, 'Creator', 'OSZIMT');
PDF_set_info($p, 'Author', 'Christoph Griep');
PDF_set_info($p, 'Title', 'Nutzungsvereinbarung MSDNAA');

$Nummern = explode(',',$_REQUEST['Antragnr']);

foreach ( $Nummern as $key => $AntragNr) {
  if ( ! is_numeric($AntragNr) ) {
    PDF_close($p);
    die ('Keine Antragnr angegeben!');
  }

  $query = mysql_query('SELECT Name, Vorname, Art, Ansprechpartner, Produkt, Eingang ' .
  		'FROM T_Antraege WHERE Vertragsnummer = '.$AntragNr.' ORDER BY Eingang');
  $Produkte = array();
  while ( $row = mysql_fetch_object($query) )
  {
    $Produkte[] = $row->Produkt;
    $Name = $row->Vorname.' '.$row->Name;
    $Art = $row->Art;
    $Wer = $row->Ansprechpartner;
    $Eingang = $row->Eingang;
  }
  mysql_free_result($query);

  // Produkte 36 und 37 sind die Nicht-Microsoft-Produkte!
  $ohne = array();
  $ohne[] = 36;
  $ohne[] = 37;

  if ( Count(array_diff($Produkte,$ohne)) != 0 )
  {
    PDF_begin_page($p, 595, 842);
    
    $bb = 'oszimtlogo300.jpg';
    $bfont = PDF_findfont($p, 'Helvetica-Bold', 'winansi', 0);
    PDF_setfont($p, $bfont, 18.0);    
    if ( file_exists($bb) ) {
      $pim = pdf_load_image($p, 'jpeg', $bb, '');
      pdf_place_image($p, $pim, 435, 750, 0.4);
      pdf_close_image($p, $pim);
    }
    else
      PDF_show_xy($p, '(OSZ IMT)',435, 750);
    
    PDF_show_xy($p, 'VN: '.$AntragNr,480,730);
    PDF_set_text_pos($p, 50, 730);
    PDF_show($p, 'Nutzungsvereinbarung f�r Sch�ler und Lehrer');
    pdf_continue_text($p,'');    
    PDF_continue_text($p, '');
    $font = PDF_findfont($p, 'Helvetica', 'winansi', 0);
    PDF_setfont($p, $font, 10.0);
    if ( strtoupper($Art ) == 'LEHRER' ) $Was = 'Lehrer';
    else $Was = 'Sch�ler';

    $Text = "Als Mitglied der MSDN (R) Academic Alliance (MSDNAA), ist das OSZ IMT, dessen $Was Sie sind, autorisiert, Ihnen Programmsoftware zur Nutzung auf Ihrem privaten Computer zu �berlassen.";

    zeige_text_absatz($p, $Text);

    $Text = "Sie m�ssen Ihre Zustimmung zu den unten stehenden MSDNAA-Nutzungsrichtlinien, " .
    		"dem MSDN Endbenutzer-Lizenzvertrag (EULA) und der MSDNAA-Erg�nzungsvereinbarung " .
    		"zum Endbenutzer-Lizenzvertrag sowie s�mtlichen vom " .
    		"OSZ IMT erlassenen Richtlinien erkl�ren.";

    zeige_text_absatz($p, $Text);

    $Text = 'Die MSDNAA-Verwalter des OSZ IMT stellen die Einhaltung der ' .
    		'Richtlinien durch Sch�ler, Lehrkr�fte und Verwaltungspersonal ' .
    		'sicher und erfassen Daten, um die Nutzung durch Sch�ler und Lehrer ' .
    		'zu belegen. Diese Daten werden auf Anfrage von Microsoft (R) ' .
    		'zusammengefasst weitergegeben.';

    zeige_text_absatz($p, $Text);
    $Text = 'Indem Sie die Software installieren, kopieren oder auf andere ' .
    		'Weise Gebrauch davon machen, verpflichten Sie sich, sich an die ' .
    		'Bestimmungen des EULA und der Erg�nzungsvereinbarung zu halten.';
    zeige_text_absatz($p, $Text);

    pdf_continue_text($p,'');
    PDF_setfont($p, $bfont, 14.0);
    $Text = 'INSTALLATIONSRICHTLINIEN';
    zeige_text_absatz($p, $Text);
    PDF_setfont($p, $font, 10.0);
    $Text="Sie m�ssen $Was am OSZ IMT sein, um die Berechtigung zum Installieren " .
    		"der Programmsoftware auf ihrem privaten Computer zu erlangen.";

    zeige_text_absatz($p, $Text);
    $Text='Das OSZ IMT gibt Ihnen die M�glichkeit, eine Kopie ' .
    		'der Software zu erstellen, die Sie auf Ihrem privaten Computer installieren k�nnen. ';

    zeige_text_absatz($p, $Text);
    $Text = 'F�r bestimmte Produkte erhalten Sie einen Produktschl�ssel, der ' .
    		'zur Installation erforderlich ist. Sie d�rfen den Produktschl�ssel ' .
    		'nicht an andere Personen weitergeben. Der Produktschl�ssel wird ' .
    		'ihnen pers�nlich zugeordnet.';
    zeige_text_absatz($p, $Text);

    $Text = 'Sie d�rfen Kopien der geliehenen Software nicht an andere Personen ' .
    		'weitergeben. Andere berechtigte Personen m�ssen die Software ' .
    		'�ber die regul�re Ausleihprozedur von der Schule beziehen. In ' .
    		'jedem Falle m�ssen alle Nutzer die Nutzungsvereinbarung unterschreiben.';

    pdf_continue_text($p,'');
    PDF_setfont($p, $bfont, 14.0);
    $Text = 'NUTZUNGSRICHTLINIEN';
    zeige_text_absatz($p, $Text);
    PDF_setfont($p, $font, 10.0);

    $Text = "Sie d�rfen die Software nicht zu kommerziellen Zwecken irgendwelcher " .
    		"Art verwenden, insbesondere nicht f�r die Entwicklung kommerzieller " .
    		"Software. Die Nutzung ist lediglich f�r Lehre und Forschung sowie " .
    		"zum Entwerfen, Entwickeln und Testen von Projekten im Rahmen der " .
    		"Aufgabenstellungen der Schule, bei Examen oder f�r pers�nliche " .
    		"Projekte erlaubt.";
    zeige_text_absatz($p, $Text);
    $Text = 'Wenn Sie nicht mehr '.$Was.' des OSZ IMT sind, sind Sie nicht mehr ' .
    		'zum Bezug von MSDNAA-Software berechtigt. Sie d�rfen jedoch auf ' .
    		'Ihrem Computer installierte Produkte weiterverwenden, vorausgesetzt, ' .
    		'Sie halten sich weiterhin an die MSDNAA-Programmrichtlinien.';
    zeige_text_absatz($p, $Text);
    $Text = 'Wenn Sie gegen die Bestimmungen des EULA und der ' .
    		'Erg�nzungsvereinbarung versto�en, sind Sie nicht mehr berechtigt, ' .
    		'die Software zu nutzen. Die MSDNAA-Verwaltung des OSZ IMT wird ' .
    		'eine Best�tigung verlangen, dass die Programmsoftware von Ihrem ' .
    		'pers�nlichen Computer entfernt wurde.';
    zeige_text_absatz($p, $Text);
    pdf_continue_text($p,'');
    PDF_setfont($p, $bfont, 14.0);
    $Text = 'EINVERST�NDNISERKL�RUNG';
    zeige_text_absatz($p, $Text);
    PDF_setfont($p, $font, 10.0);

    $Text = 'Durch Ihre Unterschrift erkl�ren Sie sich mit den Bestimmungen ' .
    		'des MSDN EULA, der Erg�nzungsvereinbarung zum MSDNAA-Endbenutzer-' .
    		'Lizenzvertrag, der MSDNAA-Nutzungsvereinbarung f�r Sch�ler und ' .
    		'Lehrer und den Nutzungsrichtlinien des OSZ IMT einverstanden. ' .
    		'Sie erkl�ren sich weiterhin damit einverstanden, dass Ihr Name ' .
    		'und Ihre Klasse gespeichert werden.';
    zeige_text_absatz($p, $Text);

    pdf_continue_text($p, '');
    pdf_continue_text($p, '');
    pdf_continue_text($p, '');
    pdf_continue_text($p, '');
    pdf_continue_text($p, '');
    pdf_continue_text($p, '');
    $y = pdf_get_value($p,'texty',0);
    pdf_moveto($p, 50, $y);
    pdf_lineto($p, 200, $y);
    pdf_stroke($p);
    pdf_moveto($p, 350, $y);
    pdf_lineto($p, 500,$y);
    pdf_stroke($p);
    pdf_continue_text($p,'Ort, Datum');

    PDF_show_xy($p,$Name, 350, pdf_get_value($p, 'texty',0));
    PDF_setfont($p, $font, 6.0);
    pdf_continue_text($p, '(Bei Minderj�hrigen Unterschrift der Erziehungsberechtigten)');
    pdf_continue_text($p, '');
    PDF_setfont($p, $font, 8.0);
    pdf_continue_text($p,'');
    pdf_continue_text($p,'');
    pdf_continue_text($p,'');
    PDF_setfont($p, $font, 6.0);
    pdf_show_xy($p, 'Wird von der MSDNAA-Verwaltung des OSZ IMT ausgef�llt', 350,
      pdf_get_value($p, 'texty',0));
    PDF_setfont($p, $font, 8.0);
    pdf_continue_text($p,'');
    $y = pdf_get_value($p, 'texty',0);
    pdf_set_text_pos($p, 50, $y);
    PDF_setfont($p, $bfont, 8.0);
    pdf_show_xy($p, 'Antragsteller:', 50, $y);
    
    if ( isset($_REQUEST['Nochmal']))
      pdf_show_xy($p, 'Erneuter Ausdruck vom '.$Eingang, 350, $y);
    else
      pdf_show_xy($p, 'Original', 350, $y);
    
    $w = pdf_stringwidth($p, 'Antragsteller', pdf_get_value($p,'font',0)
               ,pdf_get_value($p,'fontsize',0));
    PDF_setfont($p, $font, 8.0);
    pdf_show_xy($p, $Name, 70+$w, pdf_get_value($p, 'texty',0));
    PDF_setfont($p, $bfont, 8.0);
    pdf_continue_text($p,'');
    $y = pdf_get_value($p, 'texty',0);
    if ( strtoupper($Art) != 'LEHRER' )
      pdf_show_xy($p,'Klasse:', 50, $y);
    pdf_show_xy($p, '', 350, $y);
    PDF_setfont($p, $font, 8.0);
    pdf_show_xy($p, $Art, 70+$w, pdf_get_value($p, 'texty',0));
    PDF_setfont($p, $bfont, 8.0);
    pdf_continue_text($p,'');
    $y = pdf_get_value($p, 'texty',0);
    if ( strtoupper($Art) != 'LEHRER' )
      pdf_show_xy($p,'Lehrer:', 50, $y);
    pdf_show_xy($p, 'Lizenz/CD zugewiesen:', 350, pdf_get_value($p, 'texty',0));
    PDF_setfont($p, $font, 8.0);
    pdf_show_xy($p, $Wer.' ('.date('d.m.Y H:i').')', 70+$w, 
      pdf_get_value($p, 'texty',0));

    PDF_end_page($p);
  } // Microsoft produkte vorhanden
  else
  {
    /*
    // Freeware!
    PDF_begin_page($p, 595, 842);
    $bfont = PDF_findfont($p, 'Helvetica-Bold', "host", 0);
    PDF_setfont($p, $bfont, 18.0);
    PDF_show_xy($p, "VN: ".$AntragNr,480,730);
    PDF_set_text_pos($p, 50, 730);
    PDF_show($p, "Anfrage nach lizenzgeb�hrenfreier Software");
    PDF_continue_text($p," f�r Sch�ler und Lehrer");
    pdf_continue_text($p,"");
    $bb = "oszimtlogo300.jpg";
    if ( file_exists($bb) ) {
      $pim = pdf_open_image_file($p, "jpeg", $bb);
      pdf_place_image($p, $pim, 435, 750, 0.4);
      pdf_close_image($p, $pim);
    }
    else
      PDF_continue_text($p, "(OSZ IMT)");
    PDF_continue_text($p, "");
    $font = PDF_findfont($p, "Helvetica", "host", 0);
    PDF_setfont($p, $font, 10.0);
    zeige_text_absatz($p, "Die lizenzgeb�hrenfreie Software wird vom OSZ IMT ohne ".
      "Gew�hrleistungsanspr�che oder sonstige rechtliche Verpflichtungen zur Verf�gung ".
      "gestellt.");
    zeige_text_absatz($p, "F�r den Arbeitsaufwand und die CD wird ein Unkostenbeitrag von ".
      "2 Euro erhoben.");
    zeige_text_absatz($p, "Bitte beachten Sie, dass die Software StarOffice nur f�r den ".
     " pers�nlichen Gebrauch bestimmt ist und nicht weitergegeben ".
     " werden darf.");
    PDF_continue_text($p, "");
    PDF_continue_text($p, "");
    PDF_setfont($p, $bfont, 14.0);
    $Text = "EINVERST�NDNISERKL�RUNG";
    zeige_text_absatz($p, $Text);
    PDF_setfont($p, $font, 10.0);

    $Text = "Durch Ihre Unterschrift erkl�ren Sie sich mit den genannten Einschr�nkungen einverstanden.";
    zeige_text_absatz($p, $Text);

    pdf_continue_text($p, "");
    pdf_continue_text($p, "");
    pdf_continue_text($p, "");
    pdf_continue_text($p, "");
    pdf_continue_text($p, "");
    pdf_continue_text($p, "");

    pdf_moveto($p, 50, pdf_get_value($p,"texty"));
    pdf_lineto($p, 200,pdf_get_value($p,"texty"));
    pdf_stroke($p);
    pdf_moveto($p, 350, pdf_get_value($p,"texty"));
    pdf_lineto($p, 500,pdf_get_value($p,"texty"));
    pdf_stroke($p);
    pdf_continue_text($p,"Ort, Datum");

    PDF_show_xy($p,$Name, 350, pdf_get_value($p, "texty"));
    PDF_setfont($p, $font, 6.0);
    pdf_continue_text($p, "(Bei Minderj�hrigen Unterschrift der Erziehungsberechtigten)");
    pdf_continue_text($p, "");
    PDF_setfont($p, $font, 8.0);
    pdf_continue_text($p,"");
    pdf_continue_text($p,"");
    pdf_continue_text($p,"");
    PDF_setfont($p, $font, 6.0);
    pdf_show_xy($p, "Wird von der MSDNAA-Verwaltung des OSZ IMT ausgef�llt", 350,pdf_get_value($p, "texty"));
    PDF_setfont($p, $font, 8.0);
    pdf_continue_text($p,"");
    $y = pdf_get_value($p, "texty");
    pdf_set_text_pos($p, 50, $y);
    PDF_setfont($p, $bfont, 8.0);
    pdf_show_xy($p, "Antragsteller:", 50, $y);
    pdf_show_xy($p, "Unterschrieben zur�ck am:", 350, $y);
    $w = pdf_stringwidth($p, "Antragsteller");
    PDF_setfont($p, $font, 8.0);
    pdf_show_xy($p, $Name, 70+$w, pdf_get_value($p, "texty"));
    PDF_setfont($p, $bfont, 8.0);
    pdf_continue_text($p,"");
    $y = pdf_get_value($p, "texty");
    if ( strtoupper($Art) != "LEHRER" )
      pdf_show_xy($p,"Klasse:", 50, $y);
    pdf_show_xy($p, "", 350, $y);
    PDF_setfont($p, $font, 8.0);
    pdf_show_xy($p, $Art, 70+$w, pdf_get_value($p, "texty"));
    PDF_setfont($p, $bfont, 8.0);
    pdf_continue_text($p,"");
    $y = pdf_get_value($p, "texty");
    if ( strtoupper($Art) != "LEHRER" )
      pdf_show_xy($p,"Lehrer:", 50, $y);
    pdf_show_xy($p, "Lizenz/CD zugewiesen:", 350, pdf_get_value($p, "texty"));
    PDF_setfont($p, $font, 8.0);
    pdf_show_xy($p, $Wer, 70+$w, pdf_get_value($p, "texty"));
    PDF_end_page($p);
    */
  }
} // While
mysql_close($db);
PDF_close($p);
$buf = PDF_get_buffer($p);
$len = strlen($buf);
header("Content-type: application/pdf");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=Antrag".$AntragNr.".pdf");
print $buf;
PDF_delete($p);


?>