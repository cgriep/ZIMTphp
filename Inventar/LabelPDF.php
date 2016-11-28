<?php
/**
 * Erstellung einer PDF-Datei mit Inventarlabels
 * (c) 2006 Christoph Griep
 * 
 */
 include('include/config.php');
 include('include/stupla.inc.php');
 include('include/turnus.inc.php');
 include('include/pdf.inc.php');
 include('barcode/core.php');

DEFINE('HOEHE',102);
DEFINE('BREITE',200);
DEFINE('RAND',5);
DEFINE('RANDOBEN',20);

if ( isset($_REQUEST['Inventar']) && is_array($_REQUEST['Inventar']))
{

$p = PDF_new();
PDF_open_file($p,'');
PDF_set_info($p, 'Creator', 'OSZIMT');
PDF_set_info($p, 'Author', 'Christoph Griep');
PDF_set_info($p, 'Title', 'Inventarlabel');
PDF_begin_page($p, 595, 842);
$Fonts = LadeFonts($p);
$bb = 'oszimtlogo300.jpg';
if ( file_exists($bb) ) {
    $pim = pdf_load_image($p, 'jpeg', $bb,'');
}

$zeile = 0;
$spalte = 0;
if ( isset($_REQUEST['Spalte']) && is_numeric($_REQUEST['Spalte']))
{
  $spalte = $_REQUEST['Spalte']-1;
  if ( $spalte < 0 || $spalte > 2 )
    $spalte = 0;
}
if ( isset($_REQUEST['Zeile']) && is_numeric($_REQUEST['Zeile']))
{
  $zeile = $_REQUEST['Zeile']-1;
  if ( $zeile < 0 || $zeile > 8 )
    $zeile = 0;
}  
$sql = 'SELECT * FROM T_Inventar INNER JOIN T_Inventararten ' .
		'ON F_Art_id=Art_id WHERE Inventar_id IN ('.implode(',',$_REQUEST['Inventar']).
        ') ORDER BY Inventar_Nr';   
if (!$query = mysql_query($sql)) 
  die ($sql.' / '.mysql_error());
$anz =0;
while ( $row = mysql_fetch_array($query) )
{
    $anz++;
	pdf_place_image($p, $pim, $spalte*BREITE+RAND, (800-$zeile*HOEHE)-15-RANDOBEN, 0.2);
    pdf_setfont($p, $Fonts['Arial']['N'], 6.0);
    pdf_show_xy($p, date('d.m.Y'),$spalte*BREITE+RAND+120,(800-$zeile*HOEHE)-RANDOBEN+15);
    pdf_setfont($p, $Fonts['Arial']['N'], 8.0);
    pdf_show_xy($p, $row['Art'], $spalte*BREITE+60+RAND,(800-$zeile*HOEHE)+5-RANDOBEN);
    pdf_show_xy($p, $row['Seriennummer'], $spalte*BREITE+60+RAND,(800-$zeile*HOEHE)-15-RANDOBEN);
    pdf_setfont($p, $Fonts['Arial']['N'], 6.0);
    pdf_show_xy($p, $row['Bezeichnung'], $spalte*BREITE+60+RAND,(800-$zeile*HOEHE)-5-RANDOBEN); 
    // Barcode holen
    barCode(BC_TYPE_CODE39, $row['Inventar_Nr'], 
      1, 2, 1, FALSE, 10, BC_IMG_TYPE_PNG, FALSE /* Label */, BC_ROTATE_0, 
      TRUE, TRUE, 'barcode/Label');
    $code = pdf_load_image($p, 'png', 'barcode/Label.png','');    
    unlink('barcode/Label.png');
    pdf_place_image($p, $code, $spalte*BREITE+RAND-10, (800-$zeile*HOEHE)-50-RANDOBEN, 1);
    pdf_close_image($p, $code);    
    pdf_setfont($p, $Fonts['Arial']['B'], 24.0);
    pdf_show_xy($p, $row['Inventar_Nr'],$spalte*BREITE+RAND,(800-$zeile*HOEHE)-35-RANDOBEN);
	$spalte++;
	if ( $spalte % 3 == 0 )
	{
		$spalte = 0;
		$zeile++;
		if ( $zeile == 8 )
		{
			$zeile = 0;
			PDF_end_page($p);
			PDF_begin_page($p, 595, 842);			
		}
	}
}   
mysql_free_result($query);
pdf_close_image($p, $pim);

PDF_end_page($p);
PDF_close($p);
$buf = PDF_get_buffer($p);
$len = strlen($buf);
header('Content-type: application/pdf');
header("Content-Length: $len");
header('Content-Disposition: inline; filename=Inventarlabel.pdf');
print $buf;
PDF_delete($p);
}
else
{
  $Ueberschrift = 'Inventarlabels drucken';
  include('include/header.inc.php');
  echo '<tr><td>';
  if ( isset($_REQUEST['Raum']) && is_numeric($_REQUEST['Raum']) )
  {
	// Raumauswahl
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
    echo '<h2>Raum ';
    $query = mysql_query('SELECT * FROM T_Raeume WHERE Raum_id='.$_REQUEST['Raum'].
      ' ORDER BY Raumnummer');
    $raum = mysql_fetch_array($query);
    echo $raum['Raumnummer'].' ('.$raum['Raumbezeichnung'].')</h2>';
    echo '<a href="'.$_SERVER['PHP_SELF'].'">anderen Raum auswählen</a><br />';
    mysql_free_result($query);
    
    $query = mysql_query('SELECT * FROM T_Inventar WHERE F_Raum_id='.
       $_REQUEST['Raum'].' AND Inventar_Nr<>"" ORDER BY Bezeichnung');
    echo '<select name="Inventar[]" multiple="multiple" size="25">';    
    while ( $inventar = mysql_fetch_array($query))
    {
    	echo '<option value="'.$inventar['Inventar_id'].'">';
    	echo $inventar['Inventar_Nr'].' - ';
    	echo $inventar['Bezeichnung'];    	
    	echo '</option>'."\n";
    }
    mysql_free_result($query);
    echo '</select><br/>';  
    echo 'Beginn mit Etikettspalte <input type="text" name="Spalte" size="2" /> (1 bis 3)<br />';
    echo 'Beginn mit Etikettzeile <input type="text" name="Zeile" size="2" /> (1 bis 9)<br />';
    echo '<input type="submit" value="Drucken" />';    
    echo '</form>';	
  }
  else
  {
	// Raumauswahl
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
    echo 'Raum ';
    echo '<select name="Raum">';
    $query = mysql_query('SELECT * FROM T_Raeume ORDER BY Raumnummer');
    while ( $raum = mysql_fetch_array($query))
    {
    	echo '<option value="'.$raum['Raum_id'].'">'.$raum['Raumnummer'];
    	echo '</option>'."\n";
    }
    mysql_free_result($query);
    echo '</select><br/>';  
    echo '<input type="submit" value="Anzeigen" />';
    echo '</form>';
  }
  echo '</td></tr>';
  include('include/footer.inc.php');
}


?>
