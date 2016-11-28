<?php
function holeProdukte($einschraenkung = "", $dbMSDNAA)
{
  $query = mysql_query("SELECT * FROM T_Produkte $einschraenkung ORDER BY Produkt ",$dbMSDNAA);
  $Produkte = array();
  
  while ( $row = mysql_fetch_object($query))
    $Produkte[$row->id] = $row->Produkt;
  
  mysql_free_result($query);
  
  return $Produkte;
}
?>