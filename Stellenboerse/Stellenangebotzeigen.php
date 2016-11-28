<?php
/*
 * Created on 22.08.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $dbUser = 'stellenboerse';
 $dbPassword = 'ePGBR9LEh,,Vf3Wz';
 $db = mysql_connect("localhost", $dbUser, $dbPassword);

  if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) )
  {
     // Senden des Response-Headers für den Inhaltstyp der gelieferten
     // Daten 
     mysql_select_db('Stellenboerse');
     $query = mysql_query('SELECT * FROM T_Stellenangebote WHERE Stellen_id='.
       $_REQUEST['id']);
     if ( ! $stelle = mysql_fetch_array($query)) unset($stelle);
     mysql_free_result($query);
     if ( isset($stelle))
     {
       header ( 'Content-Type: '.$stelle['Mime']);
       header('Content-Len: '.strlen($stelle['Datei']));
       header('Content-Disposition: inline; filename=Stellenangebot.pdf');
       echo $stelle['Datei'];
     }
     else
       die('Ungültige ID');
  }
  else
    die ( 'Ungültige ID!');
  mysql_close(); 

?>
