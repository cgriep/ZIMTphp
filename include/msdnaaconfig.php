<?php

$dbName = "msdnaa";
$dbUser = "msdnaa";
$dbPassword = "nVbyhCRsLqVRLFAK"; // neu: nVbyhCRsLqVRLFAK, alt: msaadn

// Datenbank öffnen
$db = mysql_connect("localhost", $dbUser, $dbPassword);
mysql_select_db($dbName, $db);

function holeProdukte($einschraenkung = "")
{
  global $db;
  
  $query = mysql_query("SELECT * FROM T_Produkte $einschraenkung ORDER BY Produkt ",$db);
  $Produkte = array();
  
  while ( $row = mysql_fetch_object($query))
    $Produkte[$row->id] = $row->Produkt;
  
  mysql_free_result($query);
  
  return $Produkte;
}
?>