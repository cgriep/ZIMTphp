<?php
/*
 * Created on 22.08.2006
 * Gibt ein Bild aus der Datenbank als entsprechende Bilddatei aus
 * (c) 2006 Christoph Griep
 */
  include('include/config.php');
  include('include/Lehrer.class.php');  
  if ( isset($_REQUEST['Kuerzel']) && $_REQUEST['Kuerzel'] != '' )
    $Lehrer = new Lehrer($_REQUEST['Kuerzel'],LEHRERID_KUERZEL); 
  else
    $Lehrer = new Lehrer($_REQUEST['Mail'],LEHRERID_EMAIL);
  if ( $Lehrer->Bild != '' )
  {      
     // Senden des Response-Headers für den Inhaltstyp der gelieferten
     // Daten 
     header ( 'Content-Type: image/jpeg');
     // Senden der eigentlichen Bilddaten als (einzigen) Inhalt der
     // Response
     if ( isset($_REQUEST['Groesse']) && is_numeric($_REQUEST['Groesse']))
     {
     	// Bildgröße verändern
        $neueBreite=$_REQUEST['Groesse']; 
         
        // JPG 
        $altesBild=imagecreatefromstring ($Lehrer->Bild);       
        $breite = imageSX($altesBild);
        $hoehe = imageSY($altesBild);
        $neueHoehe=intval($hoehe*$neueBreite/$breite); 
        $neuesBild=imagecreatetruecolor($neueBreite,$neueHoehe); 
        ImageCopyResized($neuesBild,$altesBild,0,0,0,0,$neueBreite,$neueHoehe,$breite,$hoehe);
         
        ImageJPEG($neuesBild);      	
     }
     else    
       echo ( $Lehrer->Bild );
  }
  else
    die ( 'Ungültiges Kürzel oder Bild nicht vorhanden!');
  mysql_close(); 

?>
