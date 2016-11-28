<?php
 DEFINE('ANZ_TAGE', 12);
 $Ueberschrift = 'Kategorien (Gruppen) für Termine';
 include('include/header.inc.php');
 if ( isset($_REQUEST['Save']) )
 {
   // Speichern
   foreach ( $_REQUEST['Name'] as $key=>$value )
   {
     if ( isset($_REQUEST['Del'][$key]) )
     {
       // Löschen
     }
     else
     {
       mysql_query('UPDATE T_Termin_Betroffene SET Betroffen="'.
         mysql_real_escape_string($value).
         '",Berechtigte="'.mysql_real_escape_string($_REQUEST['Berechtigte'][$key]).'" '.
         '",Veraenderung="'.mysql_real_escape_string($_REQUEST['Veraenderung'][$key]).'" '.
         'WHERE Betroffen_id='.$key);
     }
   }
   if ( $_REQUEST['NeuName'] != '' )
   {
     // Neuer Datensatz
     mysql_query("INSERT INTO T_Termin_Betroffene (Betroffen, Berechtigte,Veraenderung) VALUES ('".
       mysql_real_escape_string($_REQUEST['NeuName'])."','".
       mysql_real_escape_string($_REQUEST['NeuBerechtigte'])."','".
       mysql_real_escape_string($_REQUEST['NeuVeraenderung'])."')");
   }
 }

 include('include/Termine.class.php');
 $Termine = new Termine($db);
 $Termine->CheckScriptEinfuegen();
 echo '<tr><td><h2>Vorhandene Kategorien:</h2></td></tr>';
 echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
 $Nr = 0;
 $query = mysql_query("SELECT * FROM T_Termin_Betroffene ORDER BY Betroffen");
 echo '<tr><td><table>';
 echo '<tr><td>Löschen</td><td>Gruppenbezeichnung</td><td>Berechtigte zum Sehen der Termine</td></tr>';
 while ( $betroffen = mysql_fetch_array($query) )
 {
   echo '<tr><td><input type="Checkbox" name="Del['.$betroffen['Betroffen_id'].
     ']" value="1" ';
   echo '/></td><td><input type="Text" name="Name['.$betroffen['Betroffen_id'].
     ']" value="'.$betroffen["Betroffen"];
   echo '" size="50" maxlength="50" />';
   echo '</td><td><input type="Text" name="Berechtigte['.$betroffen['Betroffen_id'].
     ']" value="'.$betroffen['Berechtigte'];
   echo '" size="50" />';
   echo '</td><td><input type="Text" name="Veraenderung['.$betroffen['Betroffen_id'].
     ']" value="'.$betroffen['Veraenderung'];
   echo '" size="50" />';
   echo '</td></tr>';
 }
 echo '<tr><td>Neu:</td><td><input type="Text" name="NeuName" size="50" maxlength="50" />' .
 		'</td><td>';
 echo '<input type="Text" name="NeuBerechtigte" size="50" /></td><td>' .
      '<input type="Text" name="NeuVeraenderung" size="50" /></td>' .
 		'</tr>';
 echo '</table></td></tr>';
 echo '<tr><td><input type="Submit" name="Save" value="Speichern"></td></tr>';
 echo '</form>';

 include('include/footer.inc.php');
?>