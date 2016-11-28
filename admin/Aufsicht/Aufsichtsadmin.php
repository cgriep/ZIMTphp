<?
// Aufsichtsorte verwalten

 $Ueberschrift = "Aufsichtsorte verwalten";
 include("include/header.inc.php");
 echo '<tr><td>';
 if ( isset($_REQUEST["Ort"]) )
 {
   $Orte = $_REQUEST["Ort"];
   $Bereiche = $_REQUEST["Bereich"];
   if ( is_array($Orte) && is_array($Bereiche) )
   {
     foreach ( $Orte as $key => $ort )
     {
       if ( is_numeric($key) && $key > 0 )
         mysql_query("UPDATE T_Aufsichtsorte SET Ort='".
           mysql_real_escape_string($ort)."', Bereich='".
           mysql_real_escape_string($Bereiche[$key])."' WHERE Ort_id=".$key);
       else
       {
         if ( trim($ort) != "" )
         if (!   mysql_query("INSERT INTO T_Aufsichtsorte (Ort, Bereich) VALUES ('".
             mysql_real_escape_string($ort)."','".mysql_real_escape_string($Bereiche[$key])."')"))
              echo mysql_error();
       }
     }
   }
 }
 if ( isset($_REQUEST["Del"]) && is_numeric($_REQUEST["Del"]) )
 {
   echo '<hr />';
   echo 'Aufsichtsort wirklich löschen? Das entfernt auch alle Aufsichten an diesem Ort!<br />';
   echo '<a href="'.$_SERVER["PHP_SELF"].'?DelOk='.$_REQUEST["Del"].'">--- Ja, unwiderruflich löschen ---</a>';
   echo '<hr />';
 }
 if ( isset($_REQUEST["DelOk"]) && is_numeric($_REQUEST["DelOk"]) )
 {
   mysql_query("DELETE FROM T_Aufsichten WHERE F_Ort_id=".$_REQUEST["DelOk"]);
   mysql_query("DELETE FROM T_Aufsichtsorte WHERE Ort_id=".$_REQUEST["DelOk"]);
 }
 echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" >';
 echo '<table>';
 echo '<tr><th>Bezeichnung</th><th>Bereich (Ebene Bereich ohne Punkt ;-separiert)</th><th></th></tr>';
 $query = mysql_query("SELECT * FROM T_Aufsichtsorte ORDER BY Ort");

 while ( $ort = mysql_fetch_array($query) )
 {
   echo '<tr><td>';
   echo '<input type="Text" name="Ort['.$ort["Ort_id"].']" value="';
   echo $ort["Ort"].'" size="20" maxlength="45"/>';
   echo '</td><td>';
   echo '<input type="Text" name="Bereich['.$ort["Ort_id"].']" value="';
   echo $ort["Bereich"];
   echo '" size="30" maxlength="45"/>';
   echo '</td><td>';
   echo '<a href="'.$_SERVER["PHP_SELF"].'?Del='.$ort["Ort_id"].'">Löschen</a>';
   echo "</td></tr>\n";

 }
 mysql_free_result($query);
 echo '<tr><td>';
 echo 'neu: <input type="Text" name="Ort[-1]" value="" size="20" maxlength="45"/>';
 echo '</td><td>';
 echo '<input type="Text" name="Bereich[-1]" value="" size="30" maxlength="45"/>';
 echo '</td></tr>';
 echo '</table>';
 echo '<input type="Submit" value="Speichern" />';
 echo '</form>';
 echo "</td></tr>\n";
 include("include/footer.inc.php");


?>