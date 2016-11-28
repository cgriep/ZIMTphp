<?php
 $Ueberschrift = "Klausuranzahlen bearbeiten";
 $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
 include("include/header.inc.php");
 include("include/Klausur.inc.php");
 if ( isset($_REQUEST["Del"]) && isset($_REQUEST["Art"]) )
   mysql_query("DELETE FROM T_Faecher WHERE Fach='".$_REQUEST["Del"]."' AND Art='".$_REQUEST["Art"]."'");
 if ( isset($_REQUEST["Fach"]) && isset($_REQUEST["Art"]) && trim($_REQUEST["Art"]) != "" &&
   isset($_REQUEST["HJ1"]) && is_numeric($_REQUEST["HJ1"]) &&
   isset($_REQUEST["HJ2"]) && is_numeric($_REQUEST["HJ2"]) )
 {
   for ( $i = 0; $i < 10; $i++ )
     $_REQUEST["Fach"] = str_replace($i,"",$_REQUEST["Fach"]);
   if ( isset($_REQUEST["Alt"] ) )
   {
     $sql = "UPDATE T_Faecher SET HJ1=".$_REQUEST["HJ1"].", HJ2=".$_REQUEST["HJ2"].
       ", Fach='".$_REQUEST["Fach"]."'".
       " WHERE Fach='".$_REQUEST["Alt"]."' AND Art='".$_REQUEST["Art"]."'";
     if ( ! mysql_query($sql))
       echo mysql_error();
       /*
       if ( ! mysql_query("INSERT INTO T_Faecher (Fach, Art, HJ1, HJ2) VALUES ('".$_REQUEST["Fach"].
       "', '".trim($_REQUEST["Art"])."', ".$_REQUEST["HJ1"].",".$_REQUEST["HJ2"].")"))
       echo mysql_error();
       */
   }
   else
   {
     if ( ! mysql_query("INSERT INTO T_Faecher (Fach, Art, HJ1, HJ2) VALUES ('".$_REQUEST["Fach"].
       "', '".trim($_REQUEST["Art"])."', ".$_REQUEST["HJ1"].",".$_REQUEST["HJ2"].")"))
         echo mysql_error();
   }
 }
 echo "<tr><td>\n";
 $query = mysql_query("SELECT * FROM T_Faecher ORDER BY Art, Fach",$db);
 $Art = "";
 echo '<table class="Liste">';
 while ( $fach = mysql_fetch_array($query) )
 {
   if ($Art != $fach["Art"] )
   {
     //if ( $Art != "" ) echo '<tr><td colspan="3"><hr /></td></tr>';
     echo '<tr><td colspan="3" align="center" class="Zwischenueberschrift">'.$fach["Art"].
       "</td></tr>";
     echo '<tr><th>Fach</th><th>1. Halbjahr</th><th>2. Halbjahr</th></tr>';
     $Art = $fach["Art"];
   }
   echo '<tr><td><a href="'.$_SERVER["PHP_SELF"].'?Fach='.$fach["Fach"]."&Art=".$fach["Art"];
   echo '#eing">'.$fach["Fach"].'</a></td><td align="center">'.$fach["HJ1"];
   echo '</td><td align="center">'.$fach["HJ2"]."</td></tr>\n";
 }
 mysql_free_result($query);
 echo '</table>';
 if ( isset($_REQUEST["Fach"]) && isset($_REQUEST["Art"]) )
 {
   if ( ! $query = mysql_query("SELECT * FROM T_Faecher WHERE BINARY Fach='".$_REQUEST["Fach"].
     "' AND Art='".$_REQUEST["Art"]."'",$db)) echo mysql_error($db);
   $Fach = mysql_fetch_array($query);
   mysql_free_result($query);
 }
 else
 {
   $Fach["HJ1"] = 1;
   $Fach["HJ2"] = 1;
   $Fach["Art"] = "";
 }
 echo '<hr /><form action="'.$_SERVER["PHP_SELF"].'" method="post" class="Verhinderung">';
 echo '<a id="eing" name="eing"></a>';
 if ( isset($_REQUEST["Fach"]) )
   echo '<input type="hidden" name="Alt" value="'.$Fach["Fach"].'" /> ';
 echo 'Art <input type="Text" name="Art" value="'.$Fach["Art"].'" size="10" maxlength="10" /> ';
 echo 'Fach <select name="Fach">';

 $query = mysql_query("SELECT DISTINCT Fach FROM T_StuPla ORDER BY Fach");
 $da = false;
 while ( $was = mysql_fetch_array($query) )
 {
   echo '<option ';
   if ( isset($Fach["Fach"]) && $was["Fach"] == $Fach["Fach"] )
   {
     echo ' selected="selected"';
     $da = true;
   }
   echo '>'.$was["Fach"]."</option>\n";
 }
 if ( ! $da && $Fach["Fach"] != "" )
   echo '<option selected="selected">'.$Fach["Fach"].'</option>';
 mysql_free_result($query);
 echo '</select> Klausuranzahl 1. Hj ';
 echo '<input type="Text" name="HJ1" value="'.$Fach["HJ1"];
 echo '" size="2" maxlength="2" /> Klausuranzahl 2. Hj ';
 echo '<input type="Text" name="HJ2" value="'.$Fach["HJ2"];
 echo '" size="2" maxlength="2" /><br />';
 if ( isset($_REQUEST["Fach"]) )
 {
   echo '<input type="Submit" value="ändern">&nbsp;&nbsp;&nbsp;';
   echo '<a href="'.$_SERVER["PHP_SELF"]."?Del=".$_REQUEST["Fach"]."&Art=".
     $_REQUEST["Art"].'">Löschen</a>';
   echo '&nbsp;&nbsp;*&nbsp;&nbsp;<a href="'.
     $_SERVER["PHP_SELF"].'?Neu=#eing">Klausuranzahl für neues Fach eingeben</a>';
 }
 else
   echo '<input type="Submit" value="hinzufügen">';
 echo '<br /><b>Hinweise:</b><br />';
 echo "_ ist ein Platzhalter für ein variables Zeichen (z.B. die Klassennummer)<br />\n";
 echo "Berücksichtigt werden fehlende KLausuren nur, wenn das entsprechende Fach auch
 im Stundenplan steht. Es können daher die Fächer aller Ausbildungsjahre angegeben werden,
 die Fächer die in einem Jahr nicht unterrichtet werden bleiben unberücksichtigt.
 Fächer, in denen keine Klausuren geschrieben werden, müssen nicht angegeben werden.<br />
 Platzhalter für die Ausbildungsjahre sind 1, 2 und 3 (1. bis 3. AJ). Diese Zahlen werden
 passend zum aktuellen Jahr ersetzt (Beispiel: im Jahr 2006 ergibt Klasse X2_ alle Klassen
 mit X5*).";
 echo "</form>\n";
 echo '</td></tr>';
 include("include/footer.inc.php");
?>