<?php
/**
 * Wertungsformular für Klausuren
 * Letzte Änderungen:
 * 10.03.06 C. Griep - angepasst an neuen Server
 * 
 */
function zeige_text_absatz($Seite, $Text, $absatz = true) {
  $i = 0;
  while ( $i < strlen($Text) ) {
    if ( pdf_get_value($Seite, "textx",0) >= 500 &&
      (substr($Text, $i-1, 1) == " " || substr($Text, $i-1, 1) == "-" ||
       substr($Text, $i-1, 1) == "/" ) )
      pdf_continue_text($Seite, "");
    pdf_show($Seite, substr($Text, $i, 1));
    $i++;
  }
  if ( $absatz )
    pdf_continue_text($Seite, "");
  pdf_continue_text($Seite, "");
}
include("include/Klausur.inc.php");

if ( ! isset($_REQUEST["Klausur_id"]) || ! is_numeric($_REQUEST["Klausur_id"])) {
  die("Es muss eine Klausur_id angegeben werden!");
}
else
{
  include("include/config.php");
  $query = mysql_query("SELECT * FROM T_Klausurergebnisse WHERE Klausur_id =" .
    $_REQUEST["Klausur_id"]);
  if ( mysql_num_rows($query) == 0 ) die ("Keinen Eintrag für ".$_REQUEST["Klausur_id"]." gefunden");
  if ( ! $Klausur = mysql_fetch_array($query) ) die(mysql_error());
  mysql_free_result($query);

  $p = PDF_new();
  PDF_open_file($p,"");
  PDF_set_info($p, "Creator", "OSZIMT");
  PDF_set_info($p, "Author", "Christoph Griep");
  PDF_set_info($p, "Title", "Wertung Klausur ".$_REQUEST["Klausur_id"]);
  PDF_begin_page($p, 595, 842);
  $fontb = PDF_findfont($p, "Helvetica-Bold", "host", 0);
  $font = PDF_findfont($p, "Helvetica", "host", 0);
  $ifont = PDF_findfont($p, "Helvetica-Oblique", "host", 0);
  PDF_setfont($p, $fontb, 18.0);
  if ( file_exists("oszimtlogo300.jpg") ) {
    $pim = pdf_load_image($p, "jpeg", "oszimtlogo300.jpg","");
    pdf_place_image($p, $pim, 435, 750, 0.4);
    pdf_close_image($p, $pim);
  }
  else
    PDF_continue_text($p, "(OSZIMT)");
  PDF_set_text_pos($p, 50, 750);
  PDF_show($p, "Wertung der Klassenarbeit");
  PDF_continue_text($p,"");
  PDF_setfont($p, $fontb, 14.0);
  PDF_continue_text($p,"Klasse:");
  PDF_setfont($p, $font, 14.0);
  PDF_show_xy($p, $Klausur["Klasse"], 120, pdf_get_value($p, "texty",0));
  PDF_setfont($p, $fontb, 14.0);
  PDF_show_xy($p, "Schuljahr:", 350, pdf_get_value($p, "texty",0));
  PDF_setfont($p, $font, 14.0);
  pdf_show_xy($p, $Klausur["Schuljahr"], 440, pdf_get_value($p, "texty",0));
  PDF_continue_text($p,"");
  PDF_continue_text($p,"");
  PDF_setfont($p, $fontb, 14.0);
  PDF_show_xy($p, "Fach:", 50, pdf_get_value($p, "texty",0));
  PDF_setfont($p, $font, 14.0);
  PDF_show_xy($p, $Klausur["Fach"], 120, pdf_get_value($p, "texty",0));
  PDF_setfont($p, $fontb, 14.0);
  PDF_show_xy($p, "Lehrer/in:", 350, pdf_get_value($p, "texty",0));
  PDF_setfont($p, $font, 14.0);
  pdf_show_xy($p, $Klausur["Lehrer"], 440, pdf_get_value($p, "texty",0));
  PDF_continue_text($p,"");
  PDF_continue_text($p,"");
  PDF_setfont($p, $fontb, 14.0);
  PDF_show_xy($p,"Termin:",50, pdf_get_value($p,"texty",0));
  PDF_setfont($p, $font, 14.0);
  PDF_show_xy($p, date("d.m.Y",strtotime($Klausur["Datum"])), 120, pdf_get_value($p, "texty",0));
  PDF_setfont($p, $fontb, 14.0);
  PDF_show_xy($p, "Dauer/min:", 350, pdf_get_value($p, "texty",0));
  PDF_setfont($p, $font, 14.0);
  pdf_show_xy($p, $Klausur["Dauer"], 440, pdf_get_value($p, "texty",0));
  PDF_continue_text($p,"");
  PDF_continue_text($p,"");
  PDF_setfont($p, $fontb, 18.0);
  PDF_show_xy($p,"Notenspiegel", 120, pdf_get_value($p, "texty",0));
  PDF_continue_text($p,"");
  PDF_setfont($p, $ifont, 12.0);
  $x = 100;
  $y = PDF_get_value($p, "texty",0);
  $Zellenbreite = pdf_stringwidth($p, "9999", pdf_get_value($p,"font",0)
               ,pdf_get_value($p,"fontsize",0));
  for ( $i = 1; $i < 7; $i++ )
    pdf_show_xy($p, $i, $x+$i*$Zellenbreite, $y);
  pdf_show_xy($p, "D", $x+7*$Zellenbreite, $y);
  pdf_continue_text($p,"");
  PDF_setfont($p, $font, 12.0);
  pdf_show_xy($p, $Klausur["Einser"], $x+$Zellenbreite-
    pdf_stringwidth($p, $Klausur["Einser"], pdf_get_value($p,"font",0)
               ,pdf_get_value($p,"fontsize",0))/2, pdf_get_value($p, "texty",0)-2);
  pdf_show_xy($p, $Klausur["Zweier"], $x+2*$Zellenbreite-
    pdf_stringwidth($p, $Klausur["Zweier"], pdf_get_value($p,"font",0)
               ,pdf_get_value($p,"fontsize",0))/2, pdf_get_value($p, "texty",0));
  pdf_show_xy($p, $Klausur["Dreier"], $x+3*$Zellenbreite-
    pdf_stringwidth($p, $Klausur["Dreier"], pdf_get_value($p,"font",0)
               ,pdf_get_value($p,"fontsize",0))/2, pdf_get_value($p, "texty",0));
  pdf_show_xy($p, $Klausur["Vierer"], $x+4*$Zellenbreite-
    pdf_stringwidth($p, $Klausur["Vierer"], pdf_get_value($p,"font",0)
               ,pdf_get_value($p,"fontsize",0))/2, pdf_get_value($p, "texty",0));
  pdf_show_xy($p, $Klausur["Fuenfer"], $x+5*$Zellenbreite-
    pdf_stringwidth($p, $Klausur["Fuenfer"], pdf_get_value($p,"font",0)
               ,pdf_get_value($p,"fontsize",0))/2, pdf_get_value($p, "texty",0));
  pdf_show_xy($p, $Klausur["Sechser"], $x+6*$Zellenbreite-
    pdf_stringwidth($p, $Klausur["Sechser"], pdf_get_value($p,"font",0)
               ,pdf_get_value($p,"fontsize",0))/2, pdf_get_value($p, "texty",0));
  pdf_show_xy($p, Durchschnitt($Klausur), $x+7*$Zellenbreite-
    pdf_stringwidth($p, Durchschnitt($Klausur), pdf_get_value($p,"font",0)
               ,pdf_get_value($p,"fontsize",0))/2, pdf_get_value($p, "texty",0));
  pdf_show_xy($p, "Teilnehmer: ".Teilnehmeranzahl($Klausur), $x+9*$Zellenbreite,
    pdf_get_value($p, "texty",0));
  $xneu = $x+7*$Zellenbreite-$Zellenbreite/2;
  $yneu = PDF_get_value($p, "texty",0)-2;
  pdf_rect($p, $x+$Zellenbreite/2, $yneu+28, 7*$Zellenbreite, -28);
  pdf_stroke($p);
  // Teilungsstrich Noten/Anzahl
  pdf_moveto($p, $x+$Zellenbreite/2, $yneu+14);
  pdf_lineto($p, $xneu+$Zellenbreite, $yneu+14);
  pdf_stroke($p);
  // Trennstriche
  for ( $i = 1; $i < 9; $i++ )
  {
    pdf_moveto($p, $x+$i*$Zellenbreite-$Zellenbreite/2, $yneu);
    pdf_lineto($p, $x+$i*$Zellenbreite-$Zellenbreite/2, $yneu+28);
    pdf_stroke($p);
  }
  pdf_continue_text($p,"");
  PDF_setfont($p, $fontb, 14.0);
  PDF_set_text_pos($p, 50, $yneu);
  PDF_continue_text($p, "");
  PDF_continue_text($p, "");
  PDF_continue_text($p, "Begründung zur Wertung der Klassenarbeit:");
  PDF_setfont($p, $font, 12.0);
  PDF_continue_text($p, "");
  PDF_continue_text($p, "O  Der Termin der Klassenarbeit ist den Schülern rechtzeitig bekanntgegeben worden.");
//  PDF_continue_text($p, "");
  PDF_continue_text($p, "O  Hinweise auf den Lerninhalt, der Gegenstand der Klassenarbeit sein soll, wurden gegeben.");
//  PDF_continue_text($p, "");
  PDF_continue_text($p, "O  Inhalt und Schwierigkeitsgrad waren nach Maßgabe der ".
    "Rahmenpläne, der Leistungsfähigkeit");
  pdf_continue_text($p, "       und dem Arbeitstempo des Bildungsganges angepasst.");
//  PDF_continue_text($p, "");
  PDF_continue_text($p, "O  Die Dauer der Klassenarbeit betrug mindestens eine Unterrichtstunde.");
//  PDF_continue_text($p, "");
  pdf_continue_text($p, "O  Die Klassenarbeit wurde erst durchgeführt, nachdem die Schüler/-innen".
    " mit den zu ");
  pdf_continue_text($p, "       kontrollierenden Lerninhalt hinreichend vertraut gemacht worden sind.");
  pdf_continue_text($p, "");
// Lehrkraft - Begründung
  PDF_setfont($p, $fontb, 14.0);
  PDF_continue_text($p, "Stellungnahme der Lehrkraft");
  PDF_setfont($p, $font, 12.0);
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, pdf_get_value($p, "textx",0), $y);
  pdf_lineto($p, 500, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, pdf_get_value($p, "textx",0), $y);
  pdf_lineto($p, 500, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, pdf_get_value($p, "textx",0), $y);
  pdf_lineto($p, 500, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, 400, $y);
  pdf_lineto($p, 500, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_show_xy($p, "Datum : ".date("d.m.Y"), 50, pdf_get_value($p, "texty",0));
  pdf_show_xy($p, $Klausur["Lehrer"], 400, pdf_get_value($p, "texty",0));
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  // Fachleiter-Begründung
  PDF_setfont($p, $fontb, 14.0);
  pdf_set_text_pos($p, 50, pdf_get_value($p, "texty",0));
  zeige_text_absatz($p, "Stellungnahme der/des Fach(bereichs)leiterin/leiters bzw. ".
    "der/des Vorsitzenden des Fachausschusses");
  PDF_setfont($p, $font, 12.0);
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, pdf_get_value($p, "textx",0), $y);
  pdf_lineto($p, 500, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, pdf_get_value($p, "textx",0), $y);
  pdf_lineto($p, 500, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, pdf_get_value($p, "textx",0), $y);
  pdf_lineto($p, 500, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, 400, $y);
  pdf_lineto($p, 500, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_show_xy($p, "(FBL/FL/V.d.F.A)", 400, pdf_get_value($p, "texty",0));
  pdf_continue_text($p,"");
  // Entscheidung
  PDF_setfont($p, $fontb, 14.0);
  pdf_show_xy($p, "Entscheidung", 50, pdf_get_value($p, "texty",0));
  PDF_setfont($p, $font, 12.0);
  pdf_continue_text($p,"O Die Klassenarbeit wird gewertet.");
  pdf_continue_text($p,"O Die Klassenarbeit ist zu wiederholen.");
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, 50, $y);
  pdf_lineto($p, 350, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, 50, $y);
  pdf_lineto($p, 350, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_continue_text($p,"");
  $y = pdf_get_value($p, "texty",0);
  pdf_moveto($p, 50, $y);
  pdf_lineto($p, 350, $y);
  pdf_stroke($p);
  pdf_moveto($p, 400, $y);
  pdf_lineto($p, 500, $y);
  pdf_stroke($p);
  pdf_continue_text($p,"");
  pdf_show_xy($p, "(Abteilungsleitung)", 400, pdf_get_value($p, "texty",0));
  PDF_end_page($p);
  PDF_close($p);
  $buf = PDF_get_buffer($p);
  $len = strlen($buf);
  header("Content-type: application/pdf");
  header("Content-Length: $len");
  header("Content-Disposition: inline; filename=Klausur".$_REQUEST["Klausur_id"].".pdf");
  print $buf;
  PDF_delete($p);
  mysql_close($db);
}
?>