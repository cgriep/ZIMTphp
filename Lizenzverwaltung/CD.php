<?php
$Ueberschrift = "CD zurückgeben";
include("include/header.inc.php");
  include("include/msdnaaconfig.php");
?>
<tr><td>
 <table border="1" width="100%">
 <tr><th>ID</th><th>Bezeichnung</th><th>Datum</th><th>Name</th><th>Vorname</th><th>Klasse</th>
 <th>Produkt</th><th>Verantwortlich</th></tr>
<?php
  if ( isset($_REQUEST["CD"]) && is_numeric($_REQUEST["CD"]) ) {
    $sql = "UPDATE CD SET Datum = NULL, VertragID = NULL, Ansprechpartner = NULL WHERE id = ";
    $sql .= $_REQUEST["CD"];
    // echo $sql;
    if ( ! mysql_query($sql)) echo "Fehler: ".mysql_error();
  }
  $sql = "SELECT DISTINCT CD.id, CD.Datum, CD.VertragID, Produkt, Bezeichnung, Name, ".
    " Vorname, Art, CD.Ansprechpartner FROM (CD INNER JOIN Produkte ON CD.ProduktID = " .
    "Produkte.id) LEFT JOIN Lizenznehmer ON CD.VertragID = Lizenznehmer.Vertragsnummer" .
    " ORDER BY CD.id";
  // echo $sql;
  $query = mysql_query($sql);
  while ( $row = mysql_fetch_object($query))
  {
    echo '<tr><td>';
    echo $row->id . ' ';
    if ( $row->Name != "" ) {
      echo '<small>(<a href="'.$_SERVER["PHP_SELF"].'?CD='.$row->id;
      echo '">Zurück</a></small>)';
    }
    echo "</td><td>".$row->Bezeichnung."</td><td";
    if ( ! is_null($row->Datum) )
    if ( strtotime($row->Datum) < strtotime("-14 days",time()) ) echo ' bgcolor="red"';
    else
      if ( strtotime($row->Datum) < strtotime("-7 days",time()) ) echo ' bgcolor="yellow"';
    echo ">".$row->Datum."</td><td>";
    echo $row->Name."</td>";
    echo "<td>".$row->Vorname."</td><td>".$row->Art."</td><td>".$row->Produkt;
    echo "</td><td>$row->Ansprechpartner</td></form></tr>";
  }
  mysql_free_result($query);
  echo "</table><br /><br />";
  echo '</td></tr>';
  include("include/footer.inc.php");
?>