<?php

DEFINE("RANDBREITE", 50);
DEFINE("PAGEWIDTH", 595);

/*
 * Schreibt den Text an die Stelle x/y bzw. die aktuelle Position.
 * Dabei werden Zeichen, die durch {Fontname:Zeichennummer} dargestellt werden,
 * in dem Zeichensatz namens Fontname dargestellt. Zeichennummer ist der
 * ASCII-Code des Zeichens.
 * @param $Seite das PDF-Handle der Seite
 * @param $Text der zu schreibende Text
 * @param $x die x-Position. Wenn -1 dann wird die aktuelle Position verwendet. 
 * @param $y die y-Position.
 */
function zeige_text($Seite, $Text, $x = -1, $y = -1)
{
  $Font=LadeFonts($Seite);
  $Zeichen = "";
  if ( strpos($Text, '{') === false )
    $t = $Text;
  else
  {
    $font = pdf_get_value($Seite, "font",0);
    $fontsize = pdf_get_value($Seite, "fontsize",0);
    $t = substr($Text,0,strpos($Text, '{'));
    $Zeichen = substr($Text,strpos($Text, '{'));
    if ( strpos($Zeichen, '}') === false )
    {
      $t .= $Zeichen;
      $Zeichen = "";
    }
    else
    {
      $postfix = substr($Zeichen, strpos($Zeichen, '}')+1);
      $Zeichen = substr($Zeichen, 0, strpos($Zeichen, '}')+1);
    }
  }
  if ( $t != "" )
    if ( $x != -1 )
      pdf_show_xy($Seite, $t, $x, $y);
    else
      pdf_show($Seite, $t);
  if ( $Zeichen != "" )
  {
    $Zeichen=str_replace('{','',$Zeichen);
    $Zeichen=str_replace('}','',$Zeichen);
    list($f, $c) = explode(':',$Zeichen);
    pdf_setfont($Seite, $Font[$f]['N'], $fontsize );
    pdf_show($Seite,chr($c));
    pdf_setfont($Seite, $font, $fontsize);
    zeige_text($Seite, $postfix);
  }
}

/*
 * Trennt eine Zeile auf eine bestimmte Länge. Dabei wird vorzugsweise an 
 * Leerzeichen umgebrochen, sollte ein Wort größer als die angegebene Breite
 * sein, so wird das Wort an ansprechender Stelle geteilt.
 * Das Teilen erfolgt durch Einfügen eines Newline (\n).
 * @param $Seite das PDF-Handle der Seite
 * @param $Text der Text 
 * @param $seitenbreite die Breite, auf die Umgebrochen werden soll
 * @returns den Text mit eingefügten Newlines
 * 
 */
function trenne_zeile($Seite, $Text, $seitenbreite)
{
  $Saetze = explode("\n", $Text);
  $gzeile = '';
  foreach ( $Saetze as $satz )
  {
    $Woerter = explode(' ',$satz);
    $zeile = '';
    $lzeile = '';
    foreach ( $Woerter as $key => $wort )
    {
      $wort2 = ereg_replace('\{[A-Za-z:0-9]*\}','M', $wort);
      if ( pdf_stringwidth($Seite, $lzeile.' '.$wort2,pdf_get_value($Seite,'font',0)
               ,pdf_get_value($Seite,'fontsize',0)) > $seitenbreite )
      {
          while ( pdf_stringwidth($Seite, $wort2,pdf_get_value($Seite,'font',0)
               ,pdf_get_value($Seite,'fontsize',0)) > $seitenbreite)
          {
            $len = 0;
            $z = '';
            $symbol = false;
            while ( pdf_stringwidth($Seite, $z,pdf_get_value($Seite,'font',0)
               ,pdf_get_value($Seite,'fontsize',0)) < $seitenbreite &&
                  $len < strlen($wort2))
            {
              if ( substr($wort, 0, strlen($z)+1) == "{" && 
                 strpos($wort,'}') !== false )
              {
              	$len = strpos($wort,'}')-1;
              	$z = substr($wort, 0, $len+1);
              } 	
              else
                $z = substr($wort, 0, strlen($z)+1);
              $len++;
            }
            $gzeile .= $zeile."\n".$z;
            $zeile = '';
            $lzeile = '';
            $wort2 = substr($wort2, strlen($z));
          }
          // Zeile ausgeben
          $gzeile .= $zeile."\n";
          $lzeile = '';
          $zeile = '';
      }
      else
      {
          $zeile .= ' ';
          $lzeile .= ' ';
      }
      $lzeile .= trim($wort2);                     
      $zeile .= trim($wort);
    }
    $gzeile .= $zeile."\n";
  } // while
  return trim($gzeile);
}


function pdf_Tabelle_xy($Seite, $x, $y, $Tabelle, $Ausrichtung = 'center',
  $Zellenbreite = -1, $Zellenhoehe = -1,
  $InnenrandHoriz = 2, $InnenrandVert = 2, $Linienbreite = 1)
{
  if ( ! is_array($Tabelle) ) return ;
  pdf_setlinewidth($Seite, $Linienbreite);
  $Breiten = array();
  foreach ($Tabelle as $rownr => $col)
  {
    foreach ( $col as $colnr => $value )
    {
      if ( isset($value['Breite']) && is_numeric($value['Breite']) )
      {
        if ( ! isset($Breiten[$colnr]) || $value['Breite'] > $Breiten[$colnr] )
          $Breiten[$colnr] = $value['Breite'];   
      }
      elseif ( $Zellenbreite > 0 )
      {
        $Breiten[$colnr] = $Zellenbreite;
      }
      else
      {
        if ( ! isset($value['value'])) $value['value'] = '';
        $inhalt = explode("\n",$value['value']);
        $max = 0;
        foreach ( $inhalt as $nr => $string )
        {
          $string = ereg_replace('\{[A-Za-z:0-9]*\}','M',$string);        	  
          if ( pdf_stringwidth($Seite, $string,pdf_get_value($Seite,'font',0)
               ,pdf_get_value($Seite,'fontsize',0)) >= $max )
            $max = pdf_stringwidth($Seite, $string,pdf_get_value($Seite,'font',0)
               ,pdf_get_value($Seite,'fontsize',0))+3;
        }
        if ( !isset($Breiten[$colnr]) || $max > $Breiten[$colnr] )
          $Breiten[$colnr] = $max;
      }

    }
  }
  $xPos = array();
  foreach ( $Breiten as $key => $value) 
  {
    $xPos[$key] = 0;
    for ( $i=0; $i < $key; $i++)
      $xPos[$key] += $Breiten[$i];
  }
  reset($Tabelle);
  $orgZellenhoehe = $Zellenhoehe;
  $uy = $y; // Baseline des Textes
  foreach ( $Tabelle as $rownr => $col )
  {
     // Maximale Höhe feststellen
     if ( $orgZellenhoehe == -1 )
     {
       $maxzeilen = 1;
       $Zellenhoehe = 0;
       foreach ( $col as $colnr => $value)
       {
         if ( ! isset($value['value'])) $value['value'] = '';
         $value['value'] = 
           trenne_zeile($Seite, $value['value'], $Breiten[$colnr]);
         $Tabelle[$rownr][$colnr] = $value;
         $col[$colnr] = $value;
         $anz = substr_count($value['value'],"\n");
         if ( $anz+1 > $maxzeilen ) $maxzeilen = $anz+1;
         if ( isset($value['Hoehe']) && is_numeric($value['Hoehe']) )
           if ( $Zellenhoehe < $value['Hoehe'] )
             $Zellenhoehe = $value['Hoehe'];
       }
       if ( $Zellenhoehe == 0)
         $Zellenhoehe = pdf_get_value($Seite, 'fontsize',0)*$maxzeilen;
     }
     $uy = $uy-($Zellenhoehe+$InnenrandVert*2+$Linienbreite); // Baseline des Textes
     foreach ( $col as $colnr => $value)
     {
       $ux = $x+$xPos[$colnr]+$colnr*($InnenrandHoriz*2+$Linienbreite);
       //$uy = $y-($rownr+1)*($Zellenhoehe+$InnenrandVert*2+$Linienbreite); // Baseline des Textes
       if ( isset($value['Fuellung']) )
       {
         pdf_setcolor($Seite,'fill','gray',$value['Fuellung'],0,0,0);
         //pdf_setgray_fill($Seite,$value['Fuellung']);
         pdf_rect($Seite, $ux+$Linienbreite, $uy,
             $Breiten[$colnr]+$InnenrandHoriz*2+$Linienbreite,
             $Zellenhoehe+$InnenrandVert*2+$Linienbreite);
         pdf_fill($Seite);
         pdf_setcolor($Seite,'fill','gray',0,0,0,0);
         //pdf_setgray_fill($Seite,0);
       }
       if ( ! isset($value['value'])) $value['value'] = '';
       $inhalt = explode("\n",$value['value']);
       $oz = $uy+pdf_get_value($Seite, 'fontsize',0)*(Count($inhalt)-1);
       foreach ( $inhalt as $nr => $string )
       {
         $aus = 0;
         $string2 = ereg_replace('\{[A-Za-z:0-9]*\}','M',$string);
         if ( $Ausrichtung == 'center' )
         {
           $aus = ($Breiten[$colnr]-pdf_stringwidth($Seite,$string2, 
               pdf_get_value($Seite,'font',0)
               ,pdf_get_value($Seite,'fontsize',0)))/2;
         }
         elseif ( $Ausrichtung == 'right' )
         {
           $aus = $Breiten[$colnr]-pdf_stringwidth($Seite,$string2, 
              pdf_get_value($Seite,'font',0)
               ,pdf_get_value($Seite,'fontsize',0));
         }
         zeige_text($Seite, $string,  // pdf_show_xy
           $ux+$Linienbreite+$InnenrandVert+$aus,
           $oz+$Linienbreite+$InnenrandHoriz);
         $oz -= pdf_get_value($Seite, 'fontsize',0);
       }
       if ( !isset($value['Rahmen']) || $value['Rahmen'] == 'OULR' )
       {
         pdf_rect($Seite, $ux, $uy,
           $Breiten[$colnr]+$InnenrandHoriz*2+$Linienbreite*2,
           $Zellenhoehe+$InnenrandVert*2+$Linienbreite*2);
         pdf_stroke($Seite);
       }
       else
       {
       	 if ( strpos('O',$value['Rahmen'] !== false ))
       	 {
       	 	pdf_moveto($Seite, $ux, $uy+$Zellenhoehe+$InnenrandVert*2+$Linienbreite*2);       	 	
       	 	pdf_lineto($Seite, $ux+$Breiten[$colnr]+$InnenrandHoriz*2+$Linienbreite*2,
       	 	   $uy+$Zellenhoehe+$InnenrandVert*2+$Linienbreite*2);
       	 	pdf_stroke($Seite); 
       	 }
       	 if ( strpos('U',$value['Rahmen'] !== false ))
       	 {
       	 	pdf_moveto($Seite, $ux, $uy);
       	 	pdf_lineto($Seite, $ux+$Breiten[$colnr]+$InnenrandHoriz*2+$Linienbreite*2,$uy);
       	 	pdf_stroke($Seite);
       	 }
       	 if ( strpos('R',$value['Rahmen'] !== false ))
       	 {
       	 	pdf_moveto($Seite, $ux+$Breiten[$colnr]+$InnenrandHoriz*2+$Linienbreite*2, 
       	 	  $uy+$Zellenhoehe+$InnenrandVert*2+$Linienbreite*2);       	 	
       	 	pdf_lineto($Seite, $ux+$Breiten[$colnr]+$InnenrandHoriz*2+$Linienbreite*2,
       	 	   $uy);
       	 	pdf_stroke($Seite); 
       	 }
       	 if ( strpos('L',$value['Rahmen'] !== false ))
       	 {
       	 	pdf_moveto($Seite, $ux, $uy+$Zellenhoehe+$InnenrandVert*2+$Linienbreite*2);       	 	
       	 	pdf_lineto($Seite, $ux, $uy);
       	 	pdf_stroke($Seite); 
       	 }
       }
     }
  }
}

function pdf_Tabelle($Seite, $Tabelle, $Ausrichtung = 'center', $Zellenbreite = -1, $Zellenhoehe = -1,
    $InnenrandHoriz = 2, $InnenrandVert = 2, $Linienbreite = 1)
{
  $x = pdf_get_value($Seite, 'textx',0);
  $y = pdf_get_value($Seite, 'texty',0);
  pdf_Tabelle_xy($Seite, $x, $y, $Tabelle, $Ausrichtung, $Zellenbreite, $Zellenhoehe,
    $InnenrandHoriz, $InnenrandVert, $Linienbreite);
}

function pdf_underline($Seite, $ein)
{
  if ( $ein )
    pdf_set_parameter( $Seite, 'underline', 'true' );
  else
    pdf_set_parameter( $Seite, 'underline', 'false');
}

function zeige_text_absatz($Seite, $Text) {
  $i = 0;
  while ( $i < strlen($Text) ) {
    if ( pdf_get_value($Seite, 'textx',0) >= PAGEWIDTH-2*RANDBREITE &&
      (substr($Text, $i-1, 1) == ' ' || substr($Text, $i-1, 1) == '-' ||
       substr($Text, $i-1, 1) == '/' ) )
      pdf_continue_text($Seite, '');
    pdf_show($Seite, substr($Text, $i, 1));
    $i++;
  }
  pdf_continue_text($Seite, '');
  pdf_continue_text($Seite, '');
}

function zeige_text_absatz_j($Seite, $Text, $justify = '')
{
  $i = 0;
  $x = pdf_get_value($Seite, 'textx',0);
  $Woerter = explode(' ',$Text);
  $seitenbreite = PAGEWIDTH-RANDBREITE - $x;
  $zeile = '';
  while ( list($key, $wort) = each($Woerter) )
  {
    $zeile = trim($zeile);
    if ( pdf_stringwidth($Seite, $zeile.' '.$wort, 
      pdf_get_value($Seite,'font',0)
               ,pdf_get_value($Seite,'fontsize',0)) > $seitenbreite )
    {
      // Zeile ausgeben
      if ( $justify == 'justify' )
      {
        $spacecount = substr_count($zeile, ' ') ;
        if ( $spacecount > 0 )
        {
          $wordspacing = ( PAGEWIDTH-RANDBREITE-$x -
            pdf_stringwidth( $Seite, $zeile, pdf_get_value($Seite,'font',0)
               ,pdf_get_value($Seite,'fontsize',0) ) ) / $spacecount;
          pdf_set_value( $Seite, 'wordspacing', $wordspacing);
        }
      }
      pdf_show($Seite, $zeile);
      pdf_continue_text($Seite,''); // neue Zeile
      $zeile = $wort;
      pdf_set_value( $Seite, 'wordspacing', 0);
    }
    else
      $zeile .= ' '.$wort;
  } // while
  if ( $zeile != '' ) pdf_show($Seite, $zeile);
  pdf_continue_text($Seite, '');
}

function LadeFonts($p)
{
  $Font['Arial']['B'] = PDF_findfont($p, 'Helvetica-Bold', 'winansi', 0);  // Arial Fett
  $Font['Arial']['N'] = PDF_findfont($p, 'Helvetica', 'winansi', 0); // Arial
  $Font['Arial']['O'] = PDF_findfont($p, 'Helvetica-Oblique', 'winansi', 0); // Arila kursiv
  $Font['Arial']['BO'] = PDF_findfont($p, 'Helvetica-BoldOblique', 'winansi', 0); // Arila kursiv fett
  $Font['Courier']['N'] = PDF_findfont($p, 'Courier', 'winansi', 0); // Courier
  $Font['Courier']['B'] = PDF_findfont($p, 'Courier-Bold', 'winansi', 0); // Courier
  $Font['Courier']['O'] = PDF_findfont($p, 'Courier-Oblique', 'winansi', 0); // Courier
  $Font['Courier']['BO'] = PDF_findfont($p, 'Courier-BoldOblique', 'winansi', 0); // Courier
  $Font['Symbol']['N'] = PDF_findfont($p, 'Symbol', 'builtin', 0); // Courier
  $Font['Times']['N'] = PDF_findfont($p, 'Times-Roman', 'winansi', 0); // Courier
  $Font['Times']['B'] = PDF_findfont($p, 'Times-Bold', 'winansi', 0); // Courier
  $Font['Times']['BO'] = PDF_findfont($p, 'Times-BoldItalic', 'winansi', 0); // Courier
  $Font['Times']['O'] = PDF_findfont($p, 'Times-Italic', 'winansi', 0); // Courier
  $Font['Times']['I'] = PDF_findfont($p, 'Times-Italic', 'winansi', 0); // Courier
  $Font['Times']['R'] = PDF_findfont($p, 'Times-Roman', 'winansi', 0); // Courier
  $Font['ZapfDingbats']['N'] = PDF_findfont($p, 'ZapfDingbats', 'builtin', 0); // 
  return $Font;
}

?>