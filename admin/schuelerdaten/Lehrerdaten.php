<?php
/*
 * Created on 13.08.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $Ueberschrift = 'Lehrerdaten bearbeiten';
 $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
 include('include/header.inc.php');
 include('include/Lehrer.class.php');
 echo '<tr><td>';
 
 if ( isset($_REQUEST['Abgleich']))
 {
 	// Stundenplanabgleich
 	$query = mysql_query('SELECT DISTINCT Lehrer, Name, Vorname FROM T_StuPla');
 	while ( $lehrer = mysql_fetch_array($query))
 	{
 		$derLehrer = new Lehrer($lehrer['Lehrer'], LEHRERID_KUERZEL);
 		if ( trim($derLehrer->Name) != trim($lehrer['Name']) || 
 		     trim($derLehrer->Vorname) != trim($lehrer['Vorname']))
 		{
 			echo '<div class="Fehler">' .
 					'Unterschied StuPla-TLehrer: '.$lehrer['Kuerzel'].': '.
 					$lehrer['Name'].'/'.$derLehrer->Name.' : '.
 					$lehrer['Vorname'].'/'.$derLehrer->Vorname.'</div>';
 		} 
 	}
 	mysql_free_result($query);
 }
 
 if ( isset($_REQUEST['Sichern']) && isset($_REQUEST['Taetigkeit']) && 
      is_array($_REQUEST['Taetigkeit']))
 { 	
 	if ( isset($_REQUEST['Geschlecht']) && is_array($_REQUEST['Geschlecht']))
 	  $Geschlecht = $_REQUEST['Geschlecht'];
 	else
 	  $Geschlecht = array();
 	if ( isset($_REQUEST['Name']) && is_array($_REQUEST['Name']))
 	  $Name=$_REQUEST['Name'];
 	else
 	  $Name = array();
 	if ( isset($_REQUEST['EMail']) && is_array($_REQUEST['EMail']))
 	  $EMail=$_REQUEST['EMail'];
 	else
 	  $EMail = array();
 	foreach ( $_REQUEST['Taetigkeit'] as $Kuerzel => $Taetigkeit)
 	{
 		if( $Kuerzel != 'XXX')
 		{
 		  $sql = 'UPDATE T_Lehrer SET Taetigkeit="'.
 		        mysql_real_escape_string($Taetigkeit).'"';
 		  if ( isset($EMail[$Kuerzel]))
   		    $sql .= ', EMail="'.mysql_real_escape_string($EMail[$Kuerzel]).'"';
 		  if ( isset($Name[$Kuerzel]))
   	        $sql .= ', Name="'.mysql_real_escape_string($Name[$Kuerzel]).'"';
   	      if ( isset($Geschlecht[$Kuerzel]))
   	        $sql .= ', Geschlecht="'.mysql_real_escape_string($Geschlecht[$Kuerzel]).'"';
   	      $sql .= ' WHERE EMail="'.mysql_real_escape_string($Kuerzel).'"';
 		  mysql_query($sql);
 		}
 		else
 		{
 		    // Neuer Eintrag
 			if ( $Taetigkeit != '')
 			  mysql_query('INSERT INTO T_Lehrer (Kuerzel,Name,Taetigkeit,EMail) VALUES ("","'.
               mysql_real_escape_string($Name['XXX']).'","'.
               mysql_real_escape_string($Taetigkeit).'","'.
               mysql_real_escape_string($EMail['XXX']).'")');
 		}
 	}
 }
 if ( isset($_FILES['Bild']) && isset($_REQUEST['Kuerzel']))
 {
 	if ( $_FILES['Bild']['type'] != 'image/jpeg')
 	{
 		echo '<div class="Fehler">Bilddateien können nur jpeg-Dateien sein! Sie ' .
 				'haben eine Datei des Typs '.$_FILES['Bild']['type'].' hochgeladen.</div>';
 	}
 	else
 	{
 	  if ( is_uploaded_file($_FILES['Bild']['tmp_name']))
 	  {
 	    $Bild = file_get_contents($_FILES['Bild']['tmp_name']);
 	    $Bild = mysql_real_escape_string($Bild);
 	    $Kuerzel = $_REQUEST['Kuerzel']; 
 	    mysql_query('UPDATE T_Lehrer SET Bild="'.$Bild.'" WHERE Kuerzel="'.
   	    mysql_real_escape_string($Kuerzel).'"');
   	    echo '<div class="Hinweis">Bild für '.$Kuerzel.' wurde gespeichert.</div>';
 	  }
 	}
 } 
 if ( isset($_REQUEST['Pic']))
 {
 	echo '<form method="post" action="'.$_SERVER['PHP_SELF'].
       '" class="Formular" enctype="multipart/form-data">';
 	echo 'Bilddatei (.jpg) für ';
 	$Lehrer = new Lehrer($_REQUEST['Pic'],LEHRERID_KUERZEL);
 	echo $Lehrer->Name.', '.$Lehrer->Vorname;
    echo ' <input type="file" name="Bild" />';
    echo ' <input type="hidden" name="Kuerzel" value="'.$_REQUEST['Pic'].'"/>';
 	echo '<br />';
 	echo '<input type="submit" value="Speichern" />';
 	echo '</form>';
 }
 if ( isset($_REQUEST['Bearbeiten']))
 {
 	echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'" class="Formular">';
 }
 echo '<table class="Liste">';
 echo '<tr><th>Kürzel</th><th>Name</th><th>Vorname</th><th>';
 if ( isset($_REQUEST['Bilder']))
   echo 'Bild';
 else
   echo 'E-Mail'; 
 echo '</th><th>Bemerkungstext für Externe</th></tr>';
 $query = mysql_query('SELECT * FROM T_Lehrer ORDER BY Name');
 while ( $lehrer = mysql_fetch_array($query))
 {
   echo '<tr>';
   echo '<td>';
   if ( $lehrer['Bild'] != '') echo '<a href="/Service/Lehrerbild.php?Kuerzel='.
       $lehrer['Kuerzel'].'" target="_blank">';   
   echo $lehrer['Kuerzel'];
   if ( $lehrer['Bild'] != '') echo '</a>';
   if ( isset($_REQUEST['Bearbeiten']) && $lehrer['Kuerzel']!='')
     echo ' <a href="'.$_SERVER['PHP_SELF'].'?Pic='.$lehrer['Kuerzel'].'">&rarr;Pic</a>';
   else
     
   echo '</td>';
   echo '<td>';
   if ( isset($_REQUEST['Bearbeiten']) && $lehrer['Kuerzel']=='')
     echo '<input type="text" name="Name['.$lehrer['EMail'].']" value="';   
   echo $lehrer['Name'];
   if ( isset($_REQUEST['Bearbeiten']) && $lehrer['Kuerzel']=='')
     echo '" />';
   echo '</td>';
   echo '<td>'.$lehrer['Vorname'].'</td>';
   echo '<td>';
   if ( ! isset($_REQUEST['Bilder']))
   {
     if ( isset($_REQUEST['Bearbeiten']) )
       echo '<input type="text" name="EMail['.$lehrer['EMail'].']" value="';
     echo $lehrer['EMail'];
     if ( isset($_REQUEST['Bearbeiten']))
       echo '" />';
   }
   elseif ( $lehrer['Bild'] != '' && $lehrer['Kuerzel'] != '' )
   {
   	  echo '<img src="/Service/Lehrerbild.php?Kuerzel='.$lehrer['Kuerzel'].'&Groesse=50" />';
   }
   else
     echo 'n/a';       
   echo '</td>';
   echo '<td>';
   if ( isset($_REQUEST['Bearbeiten']))
     echo '<input type="text" name="Taetigkeit['.$lehrer['EMail'].']" value="';
   echo htmlentities($lehrer['Taetigkeit']);
   if ( isset($_REQUEST['Bearbeiten']))
     echo '" />';
   if ( isset($_REQUEST['Bearbeiten']))
   {
   	echo '<br />Geschlecht: <select name="Geschlecht['.$lehrer['EMail'].']">';
   	echo '<option value="U" ';
   	if ( $lehrer['Geschlecht'] != 'M' && $lehrer['Geschlecht'] != 'W') 
   	  echo 'selected="selected"';
    echo '>Unbekannt/ohne</option>';
   	echo '<option value="M" ';
    if ( $lehrer['Geschlecht'] == 'M') echo 'selected="selected"';
    echo '>Männlich</option>';
   	echo '<option value="W" ';
   	if ( $lehrer['Geschlecht'] == 'W') echo 'selected="selected"';
    echo '>Weiblich</option>';
   	echo '</select>'."\n";   	
   }
   echo '</td>';
   echo '</tr>'."\n";
 }
 mysql_free_result($query);
 if ( isset($_REQUEST['Bearbeiten']))
 {
 	echo '<tr><td>Neuer Eintrag:</td><td>';
 	echo '<input type="text" name="Name[XXX]" />';
 	echo '</td><td></td><td>';
 	echo '<input type="text" name="EMail[XXX]" />';
 	echo '</td><td>';
    echo '<input type="text" name="Taetigkeit[XXX]" />';
    echo '<br />Geschlecht: <select name="Geschlecht[XXX]">';
   	echo '<option value="U" selected="selected"';
    echo '>Unbekannt/ohne</option>';
   	echo '<option value="M" ';
    echo '>Männlich</option>';
   	echo '<option value="W" ';
   	echo '>Weiblich</option>';
   	echo '</select>'."\n";
    echo '</td></tr>';
 }
 echo '</table>';
 if ( isset($_REQUEST['Bearbeiten']))
 {
 	echo '<input type="submit" value="Speichern" name="Sichern"/>';
 	echo '</form>';
 }
 else
 {
   echo '<a href="'.$_SERVER['PHP_SELF'].'?Bearbeiten=1">Daten bearbeiten</a> / ';
   echo '<a href="'.$_SERVER['PHP_SELF'].'?Abgleich=1">Datenabgleich mit Stundenplan</a> / ';
   echo '<a href="'.$_SERVER['PHP_SELF'].'?Bilder=1">Bilder anzeigen</a> / ';
 }
 echo '</td></tr>';
 include('include/footer.inc.php');
?>
