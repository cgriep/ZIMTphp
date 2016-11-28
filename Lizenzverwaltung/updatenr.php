<?php

$dbName = "msdnaa";
$dbUser = "msdnaa";
$dbPassword = "msaadn";

// Datenbank öffnen
$db = mysql_connect("localhost", $dbUser, $dbPassword);
mysql_select_db($dbName, $db);
$dbName = "oszimt";
  $dbUser = "oszintern";
  $dbPassword = "qBEj8h";

  // Datenbank öffnen
  $db2 = mysql_connect("localhost", $dbUser, $dbPassword);
  mysql_select_db($dbName, $db2);
$query = mysql_query("SELECT DISTINCT Name, Vorname FROM Antrag WHERE Art <> 'LEHRER'",$db);
while ( $s =mysql_fetch_array($query) )
{
  $q = mysql_query("SELECT Nr FROM T_Schueler WHERE Name='".$s["Name"].
    "' AND Vorname='".$s["Vorname"]."'",$db2);
  if ( $nr = mysql_fetch_row($q) ) {
    echo $s["Name"]." ".$s["Vorname"]." ".$nr[0]."<br />";
    if ( ! mysql_query("UPDATE Antrag SET Schueler_Nr=".$nr[0]." WHERE Name='".$s["Name"].
     "' AND Vorname='".$s["Vorname"]."'",$db)) echo mysql_error($db);
     echo "s";
    }
  echo ".<br />";
}
mysql_free_result($query);
mysql_close($db2);
mysql_close($db);

?>
